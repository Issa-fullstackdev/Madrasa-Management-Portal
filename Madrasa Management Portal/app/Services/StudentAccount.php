<?php

namespace App\Services;

use App\Models\Student;

class StudentAccount
{
    /**
     * @param  list<array{month: string, charged: int, applied: int, outstanding: int, status: string}>  $months
     */
    public function __construct(
        public readonly Student $student,
        public readonly int $totalCharged,
        public readonly int $totalPaid,
        public readonly array $months,
        public readonly int $arrearsMonths,
        public readonly ?string $paidUntil,
    ) {}

    /** Positive = amount owed (arrears), negative = credit (prepaid). */
    public function balance(): int
    {
        return $this->totalCharged - $this->totalPaid;
    }

    public function arrears(): int
    {
        return max($this->balance(), 0);
    }

    public function credit(): int
    {
        return max(-$this->balance(), 0);
    }

    public function standing(): string
    {
        return match (true) {
            $this->balance() > 0 => 'arrears',
            $this->balance() < 0 => 'credit',
            default => 'clear',
        };
    }
}
