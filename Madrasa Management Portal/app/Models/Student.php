<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'admission_number', 'first_name', 'middle_name', 'last_name',
        'date_of_birth', 'gender', 'place_of_birth',
        'father_first_name', 'father_middle_name', 'father_last_name', 'father_phone',
        'mother_first_name', 'mother_middle_name', 'mother_last_name', 'mother_phone',
        'address_building', 'address_road', 'address_county',
        'emergency_contact_name', 'emergency_contact_phone',
        'guardian_signed_name', 'declaration_accepted_at',
        'status', 'monthly_fee', 'enrolled_at',
    ];

    public const DEFAULT_MONTHLY_FEE = 10000;

    protected $attributes = ['monthly_fee' => self::DEFAULT_MONTHLY_FEE];

    protected function casts(): array
    {
        return ['enrolled_at' => 'date', 'monthly_fee' => 'integer'];
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_subject')->withPivot('juz_completed');
    }

    public function feeCharges()
    {
        return $this->hasMany(FeeCharge::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
