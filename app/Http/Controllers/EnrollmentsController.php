<?php

namespace App\Http\Controllers;

use App\Models\enrollments;
use App\Http\Requests\StoreenrollmentsRequest;
use App\Http\Requests\UpdateenrollmentsRequest;

class EnrollmentsController extends Controller
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
            'regapproval_date' => 'required|date',
            'payment_approval' => 'required|boolean',
            'year_level' => 'required|integer',
            'contact_no' => 'required|string|max:15',
            'guardian_name' => 'required|string|max:100',
            'last_attended' => 'required|string|max:100',
            'public_private' => 'required|in:Public,Private', 
            'date_register' => 'required|date',
            'strand' => 'nullable|string|max:50', 
            'school_year' => 'required|string|max:10',
        ]);
    
     
        $enrollments = Enrollment::create($formFields);
        return response()->json($enrollments, 201);
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(enrollments $enrollments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, enrollments $enrollments)
    {
        $formFields = $request->validate([
            'LRN' => 'required|integer|exists:students,LRN', 
            'regapproval_date' => 'required|date',
            'payment_approval' => 'required|boolean',
            'year_level' => 'required|integer',
            'contact_no' => 'required|string|max:15',
            'guardian_name' => 'required|string|max:100',
            'last_attended' => 'required|string|max:100',
            'public_private' => 'required|in:Public,Private', 
            'date_register' => 'required|date',
            'strand' => 'nullable|string|max:50', 
            'school_year' => 'required|string|max:10',
        ]);
    
        $enrollments->update($formFields);
        return response()->json($enrollments, 200);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(enrollments $enrollments)
    {
        $enrollments->delete();
        return "Enrollment Deleted";
        //
    }
}
