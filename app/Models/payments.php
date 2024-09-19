<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class payments extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'LRN',           
        'OR_number',     
        'amount_paid',  
        'proof_payment',  
        'date_of_payment', 
    ];
}
