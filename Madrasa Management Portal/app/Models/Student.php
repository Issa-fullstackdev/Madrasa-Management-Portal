<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'admission_number', 'first_name', 'last_name', 'date_of_birth',
        'guardian_name', 'guardian_phone', 'status', 'enrolled_at',
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_subject');
    }
}