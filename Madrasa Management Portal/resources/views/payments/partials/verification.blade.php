@switch($payment->verification_status)
    @case('verified')
        <span class="badge bg-success" title="Verified {{ $payment->verified_at?->format('d M Y') }}">Verified</span>
        @break
    @case('mismatch')
        <span class="badge bg-danger" title="The code is on the statement but the amount differs">Amount mismatch</span>
        @break
    @case('unverified')
        <span class="badge bg-warning text-dark" title="Not yet matched to a bank statement">Unverified</span>
        @break
    @default
        <span class="badge bg-secondary">Cash</span>
@endswitch
@if ($payment->notes)
    <div class="small text-muted">{{ $payment->notes }}</div>
@endif
