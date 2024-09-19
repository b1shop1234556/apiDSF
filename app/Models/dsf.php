<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class dsf extends Model
{
    use HasFactory, HasApiTokens, Notifiable  ;

    protected $table = 'admins';

    protected $fillable = [
        'fname',
        'lname',
        'mname',
        'role',
        'email',
        'password'
    ];

}
