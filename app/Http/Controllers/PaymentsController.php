<?php

namespace App\Http\Controllers;

use App\Models\payments;
use App\Http\Requests\StorepaymentsRequest;
use App\Http\Requests\UpdatepaymentsRequest;

class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $formFields = $request->validate([
            'LRN' => 'required|integer|exists:students,LRN', 
            'OR_number' => 'required|string|max:255',
            'amount_paid' => 'required|string|max:255',
            'proof_payment' => 'required|string|max:255',
            'date_of_payment' => 'required|string|max:255',
        ]);
    
        $payments = Payment::create($formFields); 
        return response()->json($payments, 201);
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(payments $payments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, payments $payments)
    {
        
        $formFields = $request->validate([
            'LRN' => 'required|integer|exists:students,LRN', 
            'OR_number' => 'required|string|max:255',
            'amount_paid' => 'required|string|max:255',
            'proof_payment' => 'required|string|max:255',
            'date_of_payment' => 'required|string|max:255',
        ]);
    
        $payment->update($formFields); 
        return response()->json($payments, 200);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(payments $payments)
    {
        $payments->delete();
        return "Payment Deleted";
        //
    }
}
