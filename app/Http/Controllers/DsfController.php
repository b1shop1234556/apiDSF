<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\dsf;
use App\Models\students;
use App\Models\enrollments;
use App\Models\payments;
use App\Models\messages;
use App\Models\tuitions;
use App\Models\financial_statements;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
            'admin_pic' => 'required|string|max:255',
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

    public function findacc($id){
        $acc = dsf::find($id);

        if(is_null($acc)){
            return response()->json(['message' => 'Account not found'], 404);
        }
        return response()->json($acc,200);
    }

    public function updateacc(Request $request, $id){
        $acc = dsf::find($id);

        if(is_null($acc)){
            return response()->json(['message' => 'Account not found'], 404);
        }

        $input = $request->all();

        if($request->filled('password')){
            $input['password'] = bcrypt($request->password);
        }

        $acc->update($input);

        return response()->json($acc,200);
    }
    
    public function updateProfileImage(Request $request, $id){
        $request->validate([
            'admin_pic' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
    
        $admin = dsf::findOrFail($id);
    
        if ($request->hasFile('admin_pic')) {
            if ($admin->admin_pic) {
                Storage::delete('public/profile_images/' . $admin->admin_pic);
                
                $htdocsImagePath = 'C:/xampp/htdocs/profile_images/' . $admin->admin_pic;
                if (file_exists($htdocsImagePath)) {
                    unlink($htdocsImagePath);
                }
            }
    
            $extension = $request->admin_pic->extension();
            $imageName = time() . '_' . $admin->id . '.' . $extension;
            // $request->Admin_image->storeAs('public/profile_images', $imageName);
    
            $htdocsPath = 'C:/xampp/htdocs/profile_images'; 
    
            if (!file_exists($htdocsPath)) {
                mkdir($htdocsPath, 0777, true);
            }
    
            $request->admin_pic->move($htdocsPath, $imageName);
    
            $admin->admin_pic = $imageName;
            $admin->save();
    
            return response()->json([
                'message' => 'Profile image updated successfully',
                'image_url' => asset('profile_images/' . $imageName) 
            ], 200);
        }
    
        return response()->json(['message' => 'No image file uploaded'], 400);
    }

    public function display() {
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->join('payments', 'students.LRN', '=', 'payments.LRN')
            ->join('tuition_and_fees', 'enrollments.grade_level', '=', 'tuition_and_fees.grade_level')
            ->select(
                'students.LRN',
                'students.lname',
                'students.fname',
                'students.mname',
                'students.suffix',
                'students.gender',
                'students.address',
                'students.contact_no',
                'enrollments.grade_level',
                // 'enrollments.contact_no',
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
                'tuition_and_fees.tuition',
                DB::raw('tuition_and_fees.tuition - payments.amount_paid AS remaining_balance')
            )
            ->groupBy(
                'students.LRN',
                'students.lname',
                'students.fname',
                'students.mname',
                'students.suffix',
                'students.gender',
                'students.address',
                'students.contact_no',
                'enrollments.grade_level',
                // 'enrollments.contact_no',
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
                'tuition_and_fees.tuition'
            )
            ->get();
        
        return response()->json($data, 200);
    }
    
    public function displaylist() {
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->leftJoin('payments', 'students.LRN', '=', 'payments.LRN') // Left join to include students without payments
            ->join('tuition_and_fees', 'enrollments.grade_level', '=', 'tuition_and_fees.grade_level')
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
                'students.contact_no',
                'enrollments.grade_level',
                // 'enrollments.contact_no',
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
                'tuition_and_fees.tuition',
                DB::raw('tuition_and_fees.tuition - COALESCE(SUM(payments.amount_paid), 0) AS remaining_balance'), // Remaining balance
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
                'students.contact_no',
                'enrollments.grade_level',
                // 'enrollments.contact_no',
                'enrollments.date_register',
                'enrollments.guardian_name',
                'enrollments.public_private',
                'enrollments.school_year',
                'enrollments.regapproval_date',
                'enrollments.payment_approval',
                'tuition_and_fees.tuition'
            )
            ->get();
        
        return response()->json($data, 200);
    }

     public function displayIN() {
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->leftJoin('payments', 'students.LRN', '=', 'payments.LRN') // Left join to include students without payments
            ->join('tuition_and_fees', 'enrollments.grade_level', '=', 'tuition_and_fees.grade_level')
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
                'students.contact_no',
                'enrollments.grade_level',
                // 'enrollments.contact_no',
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
                'tuition_and_fees.tuition',
                DB::raw('tuition_and_fees.tuition - COALESCE(SUM(payments.amount_paid), 0) AS remaining_balance'), // Remaining balance
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
                'students.contact_no',
                'enrollments.grade_level',
                // 'enrollments.contact_no',
                'enrollments.date_register',
                'enrollments.guardian_name',
                'enrollments.public_private',
                'enrollments.school_year',
                'enrollments.regapproval_date',
                'enrollments.payment_approval',
                'tuition_and_fees.tuition'
            )
            ->get();
        
        return response()->json($data, 200);
    }
    

    public function receiptdisplay(Request $request, $id) {
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->join('payments', 'students.LRN', '=', 'payments.LRN')
            ->join('tuition_and_fees', 'enrollments.grade_level', '=', 'tuition_and_fees.grade_level')
            ->select(
                'students.LRN',
                'students.lname',
                'students.fname',
                'students.mname',
                'students.suffix',
                'students.gender',
                'students.address',
                'students.contact_no',
                'enrollments.grade_level',
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
                'tuition_and_fees.tuition',
                DB::raw('tuition_and_fees.tuition - payments.amount_paid AS remaining_balance') // Calculate remaining balance
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
            ->join('tuition_and_fees', 'enrollments.grade_level', '=', 'tuition_and_fees.grade_level')
            ->select(
                'students.LRN',
                'students.lname',
                'students.fname',
                'students.mname',
                'students.suffix',
                'students.gender',
                'students.address',
                'students.contact_no',
                'enrollments.grade_level',
                // 'enrollments.contact_no',
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
                'tuition_and_fees.tuition',
                DB::raw('tuition_and_fees.tuition - payments.amount_paid AS remaining_balance') // Calculate remaining balance
            )
            ->where('students.LRN', $id) // Filter by student ID
            ->update(['payment_approval' => "Approve"]);
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
            ->join('tuition_and_fees', 'enrollments.grade_level', '=', 'tuition_and_fees.grade_level')
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
                // 'tuition_and_fees.tuition',
                // DB::raw('tuition_and_fees.tuition - payments.amount_paid AS remaining_balance') 
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
                // 'tuition_and_fees.tuition',
            )
            ->get();
        
        return response()->json($data, 200);
        return response()->json(students::all(), 200);
    }

    public function displaygrade(){
        return response()->json(tuition_and_fees::orderBy('grade_level','asc')->get(),200);
    }

 
    public function displaySOA(Request $request, $id) {
        $payments = DB::table('payments')
                ->join('enrollments', 'payments.LRN', '=', 'enrollments.LRN')
                ->join('students', 'payments.LRN', '=', 'students.LRN')
                ->leftJoin('tuition_and_fees', 'enrollments.grade_level', '=', 'tuition_and_fees.grade_level')
                ->where('payments.LRN', $id)
                ->select(
                    'students.LRN',
                    'students.lname',
                    'students.fname',
                    'students.mname',
                    'payments.amount_paid',
                    'payments.description',
                    'payments.OR_number',
                    'payments.date_of_payment',
                    'tuition_and_fees.tuition',
                    DB::raw('COALESCE(SUM(payments.amount_paid), 0) AS total_paid'),
                    DB::raw('COALESCE(SUM(tuition_and_fees.tuition), 0) AS total_tuition')
                )
                ->groupBy(
                    'students.LRN',
                    'students.lname',
                    'students.fname',
                    'students.mname',
                    'payments.amount_paid',
                    'payments.description',
                    'payments.OR_number',
                    'payments.date_of_payment',
                    'tuition_and_fees.tuition',
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
                    'LRN' => $payment->LRN,
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

    //Upload.......
    public function uploadfiles(Request $request, $id){
        // Validate the incoming request
        $request->validate([
            'filename' => 'required|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx|max:2048',
        ]);

        // Prepare the new image name
        $extension = $request->filename->extension();
        $imageName = time() . '_' . $id . '_' . uniqid() . '.' . $extension;

        $htdocsPath = 'C:/xampp/htdocs/SOA'; 
        if (!file_exists($htdocsPath)) {
            mkdir($htdocsPath, 0777, true);
        }

        // Handle the image upload
        if ($request->hasFile('filename')) {
            // Check if there is an old record to update
            $admin = DB::table('financial_statements')->where('LRN', $id)->first();

            // Delete the old image if it exists
            if ($admin && $admin->filename) {
                Storage::delete('public/profile_images/' . $admin->filename);

                $htdocsImagePath = $htdocsPath . '/' . $admin->filename;
                if (file_exists($htdocsImagePath)) {
                    unlink($htdocsImagePath);
                }
            }

            // Move the uploaded image to the target location
            $request->filename->move($htdocsPath, $imageName);

            // Insert or update the record in the financial_statements table
            if ($admin) {
                // Update the existing record
                // DB::table('financial_statements')->where('LRN', $id)->update([
                //     'filename' => $imageName,
                //     'date_uploaded' => now(),
                // ]);
                DB::table('financial_statements')->insert([
                    'LRN' => $id,
                    'filename' => $imageName,
                    'date_uploaded' => now(),
                ]);
            } else {
                // Insert a new record if it doesn't exist
                DB::table('financial_statements')->insert([
                    'LRN' => $id,
                    'filename' => $imageName,
                    'date_uploaded' => now(),
                ]);
            }
        }

        return response()->json([
            'message' => 'Profile image updated and record created successfully',
            'image_url' => asset('profile_images/' . $imageName)
        ], 200);
    }






    //for msg section....

    public function displaymsg() {
        // Assuming 'admin_id' is the identifier for admins in your messages table
        $adminId = 'admin_id'; // Replace with the actual ID or condition for identifying admins
    
        // Get students who have sent messages to admins
        $studentsWithMessages = DB::table('students')
            ->join('messages', function($join) use ($adminId) {
                $join->on('messages.message_sender', '=', 'students.LRN')
                     ->where('messages.message_reciever', '=', $adminId);
            })
            ->select('students.LRN')
            ->groupBy('students.LRN')
            ->get()
            ->pluck('LRN')
            ->toArray();
        
        // Fetch data for those students
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->leftJoin('payments', 'students.LRN', '=', 'payments.LRN') // Left join to include students without payments
            ->join('tuition_and_fees', 'enrollments.grade_level', '=', 'tuition_and_fees.grade_level')
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
                'tuition_and_fees.tuition',
                DB::raw('tuition_and_fees.tuition - COALESCE(SUM(payments.amount_paid), 0) AS remaining_balance'), // Remaining balance
                DB::raw('GROUP_CONCAT(DISTINCT messages.message ORDER BY messages.message_date SEPARATOR " | ") AS messages') // Concatenate messages
            )
            ->whereIn('students.LRN', $studentsWithMessages)
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
                'tuition_and_fees.tuition'
            )
            ->get();
        
        return response()->json($data, 200);
    }
    

    public function getMessages() {
        // Retrieve messages sent from students to admins
        $messages = messages::leftJoin('students as sender', 'messages.message_sender', '=', 'sender.LRN')
            ->leftJoin('admins as receiver', 'messages.message_reciever', '=', 'receiver.id')
            ->select(
                'messages.*', 
                'sender.fname as sender_fname',
                'sender.lname as sender_lname',
                'receiver.fname as receiver_fname',
                'receiver.lname as receiver_lname'
            )
            ->orderBy('messages.created_at', 'asc') // Order messages by created_at in ascending order
            ->get();
        
        // Check if messages were found
        if ($messages->isEmpty()) {
            return response()->json(['message' => 'No messages found'], 404);
        }
        
        // Group messages by message ID
        $messagesGrouped = [];
        
        foreach ($messages as $message) {
            // Initialize message if not already set
            if (!isset($messagesGrouped[$message->message_id])) {
                $messagesGrouped[$message->message_id] = [
                    'message_id' => $message->message_id,
                    'message' => $message->message,
                    'message_date' => $message->message_date,
                    'created_at' => $message->created_at,
                    'senders' => [],
                    'receivers' => [],
                ];
            }
        
            // Add sender to the message
            if ($message->message_sender == 100124) {
                $messagesGrouped[$message->message_id]['senders'][] = [
                    'fname' => $message->sender_fname,
                    'lname' => $message->sender_lname,
                ];
                $messagesGrouped[$message->message_id]['receivers'][] = [
                    'fname' => 'Dionece Mark',
                    'lname' => 'Collano',
                ];
            } else {
                $messagesGrouped[$message->message_id]['senders'][] = [
                    'fname' => 'Dionece Mark',
                    'lname' => 'Collano',
                ];
                $messagesGrouped[$message->message_id]['receivers'][] = [
                    'fname' => $message->receiver_fname,
                    'lname' => $message->receiver_lname,
                ];
            }
        }
        
        // Return messages as a JSON response
        return response()->json(array_values($messagesGrouped), 200);
    }
    

    public function displayTWO() {
        // Fetch enrollment data along with messages from both students and admins
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->leftJoin('payments', 'students.LRN', '=', 'payments.LRN') // Left join to include students without payments
            ->join('tuition_and_fees', 'enrollments.grade_level', '=', 'tuition_and_fees.grade_level')
            ->leftJoin('messages', function($join) {
                $join->on('messages.message_reciever', '=', 'students.LRN')
                     ->orOn('messages.message_sender', '=', 'students.LRN');
            })
            ->leftJoin('admins', function($join) {
                $join->on('messages.message_sender', '=', 'admins.id')
                     ->orOn('messages.message_reciever', '=', 'admins.id');
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
                'tuition_and_fees.tuition',
                DB::raw('tuition_and_fees.tuition - COALESCE(SUM(payments.amount_paid), 0) AS remaining_balance'), // Remaining balance
                DB::raw('GROUP_CONCAT(DISTINCT messages.message ORDER BY messages.message_date SEPARATOR " | ") AS messages'), // Concatenate messages
                DB::raw('GROUP_CONCAT(DISTINCT CASE WHEN messages.message_sender IS NOT NULL THEN CONCAT(admins.fname, " ", admins.lname) END ORDER BY messages.message_date SEPARATOR " | ") AS admin_senders'), // Admin sender names
                DB::raw('GROUP_CONCAT(DISTINCT CASE WHEN messages.message_reciever IS NOT NULL THEN CONCAT(admins.fname, " ", admins.lname) END ORDER BY messages.message_date SEPARATOR " | ") AS admin_recievers') // Admin receiver names
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
                'tuition_and_fees.tuition'
            )
            ->get();
        
        // Check if data was found
        if ($data->isEmpty()) {
            return response()->json(['message' => 'No data found'], 404);
        }
    
        // Return the combined data as a JSON response
        return response()->json($data, 200);
    }
    

    public function send(Request $request){
        $validator = Validator::make($request->all(), [
            'message_sender' => 'nullable|string',
            'message_reciever' => 'nullable|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $message = Messages::create([
            'message_sender' => $request->input('message_sender'), // Ensure the key matches your database column
            'message_reciever' => $request->input('message_reciever'), // Ensure the key matches your database column
            'message' => $request->input('message'), // Ensure the key matches your database column
            'message_date' => now(),
        ]);

        return response()->json($message, 201);
    }

    public function index(){
        return response()->json(Messages::all(), 200);
    }
    

}
