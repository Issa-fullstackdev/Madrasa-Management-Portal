<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public const METHODS = ['mpesa' => 'M-Pesa', 'cash' => 'Cash'];

    protected $fillable = [
        'student_id', 'amount', 'method', 'mpesa_code', 'paid_at',
        'verification_status', 'verified_at', 'notes', 'recorded_by',
    ];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime', 'verified_at' => 'datetime'];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }
}
