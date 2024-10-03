<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\dsf;
use App\Models\students;
use App\Models\enrollments;
use App\Models\payments;
use App\Models\tuitions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\DB;
// use App\Http\Requests\StoredsfRequest;
// use App\Http\Requests\UpdatedsfRequest;

class DsfController extends Controller
{
    public function register(Request $request){
        $formField = $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'mname' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed',
        ]);

        $formField['password'] = Hash::make($formField['password']);
        dsf::create($formField);
        return $request;
    }
    public function login(Request $request) {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);
    
        $user = dsf::where('email', $validated['email'])->first();
    
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect'
            ], 401);
        }

        $token = $user->createToken($user->fname);
    
        return response()->json([
            'user' => $user,
            'token' => $token->plainTextToken
        ], 200);
    }
    
    public function logout(Request $request) {
        $request->user()->tokens()->delete();
    
        return response()->json([
            'message' => 'You are logged out'
        ], 200);
    }

    public function display() {
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->join('payments', 'students.LRN', '=', 'payments.LRN')
            ->join('tuitions', 'enrollments.year_level', '=', 'tuitions.year_level')
            ->select(
                'students.LRN',
                'students.lname',
                'students.fname',
                'students.mname',
                'students.suffix',
                'students.gender',
                'students.address',
                'enrollments.year_level',
                'enrollments.contact_no',
                'enrollments.date_register',
                'enrollments.guardian_name',
                'enrollments.public_private',
                'enrollments.school_year',
                'enrollments.regapproval_date',
                'enrollments.payment_approval',
                'payments.OR_number',
                'payments.amount_paid',
                'payments.proof_payment',
                'payments.date_of_payment',
                'tuitions.tuition',
                DB::raw('tuitions.tuition - payments.amount_paid AS remaining_balance') // Calculate remaining balance
            )
            ->get();
        
        return response()->json($data, 200);
    }
    public function receiptdisplay(Request $request, $id) {
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->join('payments', 'students.LRN', '=', 'payments.LRN')
            ->join('tuitions', 'enrollments.year_level', '=', 'tuitions.year_level')
            ->select(
                'students.LRN',
                'students.lname',
                'students.fname',
                'students.mname',
                'students.suffix',
                'students.gender',
                'students.address',
                'enrollments.year_level',
                'enrollments.contact_no',
                'enrollments.date_register',
                'enrollments.guardian_name',
                'enrollments.public_private',
                'enrollments.school_year',
                'enrollments.regapproval_date',
                'payments.OR_number',
                'payments.amount_paid',
                'payments.proof_payment',
                'payments.date_of_payment',
                'tuitions.tuition',
                DB::raw('tuitions.tuition - payments.amount_paid AS remaining_balance') // Calculate remaining balance
            )
            ->where('students.LRN', $id) // Filter by student ID
            ->first(); // Use first() to get a single record
        
        if ($data) {
            return response()->json($data, 200);
        } else {
            return response()->json(['message' => 'Student not found'], 404);
        }
    }
    
    
    
}
