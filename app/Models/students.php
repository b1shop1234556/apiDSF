<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class students extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $fillable =[
        'LRN',
        'lname',
        'fname',
        'mname',
        'suffix',
        'bdate',
        'bplace',
        'gender',
        'religion',
        'address',
        'email',
        'password'
    ];
}
