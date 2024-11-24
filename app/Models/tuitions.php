<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tuitions extends Model
{
    use HasFactory;

    protected $table = 'tuiton_fees';

    protected $fillable = [
        'grade_leve',           
        'tuition',     
        'general',  
        'esc',  
        'subsidy', 
        'req_Downpayment'
    ];
}
