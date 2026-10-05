<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NonBillableMonth extends Model
{
    protected $fillable = ['month', 'reason'];
}
