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
        'status', 'enrolled_at',
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_subject');
    }
}