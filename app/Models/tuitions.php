<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tuitions extends Model
{
    use HasFactory;

    protected $table = 'tuitions';

    protected $fillable = [
        'year_level',           
        'tuition',     
        'general',  
        'esc',  
        'subsidy', 
        'req_Downpayment'
    ];
}
