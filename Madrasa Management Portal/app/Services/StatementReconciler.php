<?php

namespace App\Services;

use App\Models\Payment;

/**
 * Checks recorded M-Pesa payments against a bank / M-Pesa statement exported as CSV.
 *
 * Statement layouts differ between banks, so rather than relying on column names we
 * look for each transaction code anywhere in a row, then check that the recorded
 * amount also appears in that row.
 */
class StatementReconciler
{
    /** Same shape as a valid M-Pesa code: a letter followed by 9 letters/digits. */
    public const CODE_PATTERN = '/\b([A-Z][A-Z0-9]{9})\b/';

    /**
     * @return array{verified: list<Payment>, mismatched: list<array{payment: Payment, amounts: list<float>}>, unrecorded: list<array{code: string, row: string}>, rows: int}
     */
    public function reconcile(string $csvPath): array
    {
        $rows = $this->readRows($csvPath);

        // Index statement rows by every code-like token they contain.
        $rowsByCode = [];
        foreach ($rows as $index => $cells) {
            preg_match_all(self::CODE_PATTERN, strtoupper(implode(' ', $cells)), $matches);
            foreach (array_unique($matches[1]) as $code) {
                $rowsByCode[$code][] = $index;
            }
        }

        $payments = Payment::with('student')
            ->whereNotNull('mpesa_code')
            ->whereIn('mpesa_code', array_keys($rowsByCode))
            ->get()
            ->keyBy('mpesa_code');

        $verified = [];
        $mismatched = [];
        foreach ($payments as $code => $payment) {
            $amounts = [];
            foreach ($rowsByCode[$code] as $index) {
                array_push($amounts, ...$this->amountsIn($rows[$index]));
            }

            $amountMatches = collect($amounts)->contains(fn ($amount) => abs($amount - $payment->amount) < 0.01);

            if ($amountMatches) {
                if (! $payment->isVerified()) {
                    $payment->update(['verification_status' => 'verified', 'verified_at' => now()]);
                }
                $verified[] = $payment;
            } elseif (! $payment->isVerified()) {
                $payment->update(['verification_status' => 'mismatch']);
                $mismatched[] = ['payment' => $payment, 'amounts' => array_values(array_unique($amounts))];
            }
        }

        $unrecorded = [];
        foreach ($rowsByCode as $code => $indexes) {
            // Only flag tokens with a digit, so 10-letter words in narrations are ignored.
            if (! $payments->has($code) && preg_match('/\d/', $code)) {
                $unrecorded[] = ['code' => $code, 'row' => implode(' | ', array_filter($rows[$indexes[0]], fn ($c) => trim($c) !== ''))];
            }
        }

        return ['verified' => $verified, 'mismatched' => $mismatched, 'unrecorded' => $unrecorded, 'rows' => count($rows)];
    }

    /**
     * @return list<list<string>>
     */
    private function readRows(string $path): array
    {
        $handle = fopen($path, 'r');
        $first = fgets($handle) ?: '';
        rewind($handle);

        // Excel in some locales exports with semicolons.
        $delimiter = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';

        $rows = [];
        while (($cells = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            if ($cells !== [null]) {
                $rows[] = array_map(fn ($cell) => (string) $cell, $cells);
            }
        }
        fclose($handle);

        return $rows;
    }

    /**
     * @param  list<string>  $cells
     * @return list<float>
     */
    private function amountsIn(array $cells): array
    {
        $amounts = [];
        foreach ($cells as $cell) {
            $clean = preg_replace('/(KES|KSH|KSHS|\s|,)/i', '', $cell);
            if ($clean !== '' && preg_match('/^-?\d+(\.\d{1,2})?$/', $clean)) {
                $amounts[] = abs((float) $clean);
            }
        }

        return $amounts;
    }
}
