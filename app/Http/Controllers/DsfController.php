<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\dsf;
use App\Models\students;
use App\Models\enrollments;
use App\Models\payments;
use App\Models\messages;
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
            ->join('tuitions_and_fees', 'enrollments.grade_level', '=', 'tuitions_and_fees.grade_level')
            ->leftJoin('messages', function($join) {
                $join->on('messages.message_reciever', '=', 'students.LRN')
                     ->orOn('messages.message_sender', '=', 'students.LRN');
            })
            ->select(
                'students.LRN',
                'students.lname',
                'students.fname',
                'students.mname',
                'students.suffix',
                'students.gender',
                'students.address',
                'enrollments.grade_level',
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
                'payments.description',
                'tuitions_and_fees.tuition',
                'messages.message_reciever',
                'messages.message_sender',
                'messages.message_id',
                DB::raw('tuitions_and_fees.tuition - payments.amount_paid AS remaining_balance'),
                DB::raw('GROUP_CONCAT(messages.message ORDER BY messages.message_date SEPARATOR " | ") AS messages') // Concatenate messages
            )
            ->groupBy(
                'students.LRN',
                'students.lname',
                'students.fname',
                'students.mname',
                'students.suffix',
                'students.gender',
                'students.address',
                'enrollments.grade_level',
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
                'payments.description',
                'tuitions_and_fees.tuition',
                'messages.message_reciever',
                'messages.message_sender',
                'messages.message_id',
            )
            ->get();
        
        return response()->json($data, 200);
    }
    
    public function displaylist() {
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->leftJoin('payments', 'students.LRN', '=', 'payments.LRN') // Left join to include students without payments
            ->join('tuitions_and_fees', 'enrollments.grade_level', '=', 'tuitions_and_fees.grade_level')
            ->leftJoin('messages', function($join) {
                $join->on('messages.message_reciever', '=', 'students.LRN')
                     ->orOn('messages.message_sender', '=', 'students.LRN');
            })
            ->select(
                'students.LRN',
                'students.lname',
                'students.fname',
                'students.mname',
                'students.suffix',
                'students.gender',
                'students.address',
                'enrollments.grade_level',
                'enrollments.contact_no',
                'enrollments.date_register',
                'enrollments.guardian_name',
                'enrollments.public_private',
                'enrollments.school_year',
                'enrollments.regapproval_date',
                'enrollments.payment_approval',
                DB::raw('GROUP_CONCAT(DISTINCT payments.OR_number) AS OR_numbers'), // Unique OR numbers
                DB::raw('SUM(payments.amount_paid) AS total_amount_paid'), // Total payment amount
                DB::raw('MAX(payments.date_of_payment) AS latest_payment_date'), // Latest payment date
                DB::raw('GROUP_CONCAT(payments.description ORDER BY payments.date_of_payment SEPARATOR " | ") AS payment_descriptions'), // Concatenate descriptions
                'tuitions_and_fees.tuition',
                DB::raw('tuitions_and_fees.tuition - COALESCE(SUM(payments.amount_paid), 0) AS remaining_balance'), // Remaining balance
                DB::raw('GROUP_CONCAT(DISTINCT messages.message ORDER BY messages.message_date SEPARATOR " | ") AS messages') // Concatenate messages
            )
            ->groupBy(
                'students.LRN',
                'students.lname',
                'students.fname',
                'students.mname',
                'students.suffix',
                'students.gender',
                'students.address',
                'enrollments.grade_level',
                'enrollments.contact_no',
                'enrollments.date_register',
                'enrollments.guardian_name',
                'enrollments.public_private',
                'enrollments.school_year',
                'enrollments.regapproval_date',
                'enrollments.payment_approval',
                'tuitions_and_fees.tuition'
            )
            ->get();
        
        return response()->json($data, 200);
    }
    

    public function receiptdisplay(Request $request, $id) {
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->join('payments', 'students.LRN', '=', 'payments.LRN')
            ->join('tuitions_and_fees', 'enrollments.grade_level', '=', 'tuitions_and_fees.grade_level')
            ->select(
                'students.LRN',
                'students.lname',
                'students.fname',
                'students.mname',
                'students.suffix',
                'students.gender',
                'students.address',
                'enrollments.grade_level',
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
                'payments.description',
                'tuitions_and_fees.tuition',
                DB::raw('tuitions_and_fees.tuition - payments.amount_paid AS remaining_balance') // Calculate remaining balance
            )
            ->where('students.LRN', $id) // Filter by student ID
            ->first(); // Use first() to get a single record
        
        if ($data) {
            return response()->json($data, 200);
        } else {
            return response()->json(['message' => 'Student not found'], 404);
        }
    }

    public function approveEnrollment(Request $request, $id){
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->join('payments', 'students.LRN', '=', 'payments.LRN')
            ->join('tuitions_and_fees', 'enrollments.grade_level', '=', 'tuitions_and_fees.grade_level')
            ->select(
                'students.LRN',
                'students.lname',
                'students.fname',
                'students.mname',
                'students.suffix',
                'students.gender',
                'students.address',
                'enrollments.grade_level',
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
                'payments.description',
                'tuitions_and_fees.tuition',
                DB::raw('tuitions_and_fees.tuition - payments.amount_paid AS remaining_balance') // Calculate remaining balance
            )
            ->where('students.LRN', $id) // Filter by student ID
            ->update(['payment_approval' => 'Approve']);
            // ->first(); // Use first() to get a single record
        
        if ($data) {
            return response()->json($data, 200);
        } else {
            return response()->json(['message' => 'Student not found'], 404);
        }

    }

    public function displayStudent(){
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->join('payments', 'students.LRN', '=', 'payments.LRN')
            ->join('tuitions_and_fees', 'enrollments.grade_level', '=', 'tuitions_and_fees.grade_level')
            ->select(
                'students.LRN',
                'students.lname',
                'students.fname',
                'students.mname',
                'students.suffix',
                // 'students.gender',
                // 'students.address',
                // 'enrollments.grade_level',
                // 'enrollments.contact_no',
                // 'enrollments.date_register',
                // 'enrollments.guardian_name',
                // 'enrollments.public_private',
                // 'enrollments.school_year',
                // 'enrollments.regapproval_date',
                'enrollments.payment_approval',
                // 'payments.OR_number',
                // 'payments.amount_paid',
                // 'payments.proof_payment',
                // 'payments.date_of_payment',
                // 'payments.description',
                // 'tuitions_and_fees.tuition',
                // DB::raw('tuitions_and_fees.tuition - payments.amount_paid AS remaining_balance') 
            )
            ->groupBy(
                'students.LRN',
                'students.lname',
                'students.fname',
                'students.mname',
                'students.suffix',
                // 'students.gender',
                // 'students.address',
                // 'enrollments.grade_level',
                // 'enrollments.contact_no',
                // 'enrollments.date_register',
                // 'enrollments.guardian_name',
                // 'enrollments.public_private',
                // 'enrollments.school_year',
                // 'enrollments.regapproval_date',
                'enrollments.payment_approval',
                // 'payments.OR_number',
                // 'payments.amount_paid',
                // 'payments.proof_payment',
                // 'payments.date_of_payment',
                // 'payments.description',
                // 'tuitions_and_fees.tuition',
            )
            ->get();
        
        return response()->json($data, 200);
        return response()->json(students::all(), 200);
    }

    public function displaygrade(){
        return response()->json(tuitions_and_fees::orderBy('grade_level','asc')->get(),200);
    }

 
    public function displaySOA(Request $request, $id) {
        $payments = DB::table('payments')
                ->join('enrollments', 'payments.LRN', '=', 'enrollments.LRN')
                ->join('students', 'payments.LRN', '=', 'students.LRN')
                ->leftJoin('tuitions_and_fees', 'enrollments.grade_level', '=', 'tuitions_and_fees.grade_level')
                ->where('payments.LRN', $id)
                ->select(
                    'students.lname',
                    'students.fname',
                    'students.mname',
                    'payments.amount_paid',
                    'payments.description',
                    'payments.OR_number',
                    'payments.date_of_payment',
                    'tuitions_and_fees.tuition',
                    DB::raw('COALESCE(SUM(payments.amount_paid), 0) AS total_paid'),
                    DB::raw('COALESCE(SUM(tuitions_and_fees.tuition), 0) AS total_tuition')
                )
                ->groupBy(
                    'students.lname',
                    'students.fname',
                    'students.mname',
                    'payments.amount_paid',
                    'payments.description',
                    'payments.OR_number',
                    'payments.date_of_payment',
                    'tuitions_and_fees.tuition',
                )
                ->get();

            // Calculate the tuition fee (assumed to be the same for the student)
            $tuition = $payments->isNotEmpty() ? $payments[0]->total_tuition : 0;

            // Initialize remaining balance
            $remainingBalance = $tuition;

            // Create an array to hold the payment details with running balance
            $paymentDetails = [];

            foreach ($payments as $payment) {
                // Subtract the current payment from the remaining balance
                $remainingBalance -= $payment->amount_paid;

                // Add to payment details with the current balance
                $paymentDetails[] = [
                    'name' => "{$payment->lname} {$payment->fname} {$payment->mname}",
                    'tuition' => $payment->tuition,
                    'OR_number' => $payment->OR_number,
                    'description' => $payment->description,
                    'amount_paid' => $payment->amount_paid,
                    'date_of_payment' => $payment->date_of_payment,
                    'remaining_balance' => $remainingBalance
                ];
            }

            // You can now return or use $paymentDetails as needed
            return response()->json([
                'tuition_fee' => $tuition,
                'payments' => $paymentDetails,
                'remaining_balance' => $remainingBalance,
            ], 200);

    }
    
    
    public function updatepayment(Request $request, $id) {
        $request->validate([
            'OR_number' => "required|string",
            'description' => 'required|string',
            'amount_paid' => 'required|numeric',
            'LRN' => 'required|string',
        ]);
    
        // Find the payment record by LRN
        $payment = DB::table('payments')->where('LRN', $id)->first();
    
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }
    
        // Update the payment record
        DB::table('payments')->where('LRN', $id)->update([
            'OR_number' => $request->OR_number,
            'description' => $request->description,
            'amount_paid' => $request->amount_paid,
            'LRN' => $request->LRN,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        // Retrieve the updated payments
        $payments = DB::table('payments')->orderBy('created_at', 'desc')->get();
    
        return response()->json([
            'message' => 'Success',
            'data' => $payments,
        ], 200);
    }      



    //for msg section....
    public function getMessages(){
    $messages = Message::with(['sender', 'receiverGuardian', 'receiverStudent'])
        ->join('students', 'messages.message_sender', '=', 'students.LRN')
        ->join('parent_guardians', 'messages.message_reciever', '=', 'parent_guardians.guardian_id')
        ->select(
            'messages.*', 
            'students.fname as sender_fname',
            'students.lname as sender_lname',
            'parent_guardians.fname as receiver_fname',
            'parent_guardians.lname as receiver_lname'
        )
        ->orderBy('messages.created_at', 'desc')
        ->get();
    
    return response()->json($messages);
}

}
