<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class enrollments extends Model
{
    use HasFactory;

    protected $table = 'enrollments';

    protected $fillable = [
        'enrol_id',
        'LRN',
        'regapproval_date',
        'payment_approval',
        'grade_level',
        'contact_no',
        'guardian_name',
        'last_attended',
        'public_private',
        'date_register',
        'strand',
        'school_year',
    ];
}
