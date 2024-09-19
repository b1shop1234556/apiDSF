<?php

namespace App\Http\Controllers;

use App\Models\students;
use App\Http\Requests\StorestudentsRequest;
use App\Http\Requests\UpdatestudentsRequest;

class StudentsController extends Controller
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
            'LRN' => 'required|integer|unique:students,LRN',
            'lname' => 'required|string|max:255',
            'fname' => 'required|string|max:255',
            'mname' => 'required|string|max:255',
            'suffix' => 'required|string|max:255',
            'bdate' => 'required|date',
            'bplace' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'religion' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'password' => 'required|string|min:8'
            
        ]);

        $students = Student::create($formFields);
        return $students;
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(students $students)
    {
        return $students;
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, students $students)
    {
        $formFields = $request->validate([
            'LRN' => 'required|integer|unique:students,LRN',
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'mname' => 'required|string|max:255',
            'suffix' => 'required|string|max:255',
            'bdate' => 'required|date',
            'bplace' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'religion' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'password' => 'required|string|min:8'
            
        ]);

        $students->update($formFields);
        return $students;
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(students $students)
    {
        $students->delete();
        return "Student Deleted";
        //
    }
}
