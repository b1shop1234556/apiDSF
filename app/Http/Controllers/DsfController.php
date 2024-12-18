<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
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
use Illuminate\Support\Facades\Log;  // Import the Log facade
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
        Admin::create($formField);
        return $request;
    }
    
    public function login(Request $request){
        $request->validate([
            "email"=>"required|email|exists:admins",
            "password"=>"required"
        ]);
        $admin = Admin::where('email',$request->email)
            ->Where('role', '=', 'DSF')
            ->first();
        if(!$admin || !Hash::check($request->password,$admin->password)){
            return [
                "message"=>"The provider credentials are incorrect"
            ];
        }
        $token = $admin->createToken($admin->fname);
        // $token = $admin->createToken($admin->fname)->plainTextToken; 

        return [
            'admin' => $admin,
            'token' => $token->plainTextToken,
            // 'admin_id'=> $admin->admin_id
        ];

    }
    
    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return [
            'message'=>'You are logged out'
        ];
        // return 'logout';
    }

      // editing personal account
      public function updatePass(Request $request){
          // Validate incoming request
          $request->validate([
              'admin_id' => 'required|integer|exists:admins,admin_id',
              'oldPassword' => 'nullable|string', // Make oldPassword optional
              'newPassword' => 'nullable|string|min:8|confirmed', // Allow newPassword to be optional
              'fname' => 'required|string|max:255',
              'mname' => 'required|string|max:255',
              'lname' => 'required|string|max:255',
              'email' => 'required|email|max:255|unique:admins,email,' . $request->admin_id . ',admin_id', // Check uniqueness for email
              'address' => 'required|string|max:255',
          ]);
      
          // Retrieve user
          $user = Admin::find($request->admin_id);
      
          // If old password is provided, check it
          if ($request->oldPassword && !Hash::check($request->oldPassword, $user->password)) {
              return response()->json(['message' => 'Wrong password'], 401);
          }
      
          // Update user details
          if ($request->newPassword) {
              $user->password = Hash::make($request->newPassword); // Update password if provided
          }
          
          $user->fname = $request->fname;
          $user->mname = $request->mname;
          $user->lname = $request->lname;
          $user->email = $request->email;
          $user->address = $request->address;
      
          $user->save(); // Save all changes
      
          return response()->json(['message' => 'User details updated successfully']);
          }

  public function uploadImage(Request $request){
          $request->validate([
              'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
              'admin_id' => 'required|exists:admins,admin_id'
          ]);
      
          try {
              $admin = Admin::findOrFail($request->input('admin_id'));
              $image = $request->file('image');
              $imageName = time() . '.' . $image->getClientOriginalExtension();
              $destinationPath = public_path('assets/adminPic');
      
              // Ensure the directory exists
              if (!is_dir($destinationPath)) {
                  mkdir($destinationPath, 0755, true);
              }
      
              // Delete the old image if exists
              if ($admin->admin_pic && file_exists($path = $destinationPath . '/' . $admin->admin_pic)) {
                  unlink($path);
              }
      
              // Move the new image and update the admin profile
              $image->move($destinationPath, $imageName);
              $admin->update(['admin_pic' => $imageName]);
      
              return response()->json([
                  'message' => 'Image uploaded successfully.',
                  'image_url' => url('assets/adminPic/' . $imageName)
              ]);
          } catch (\Exception $e) {
              return response()->json(['error' => 'Image upload failed.'], 500);
          }
          }

    // editing personal account
    public function findacc($id){
        $acc = Admin::find($id);

        if(is_null($acc)){
            return response()->json(['message' => 'Account not found'], 404);
        }
        return response()->json($acc,200);
    }

    // public function updateacc(Request $request, $admin_id){
    //     // Retrieve the account (dsf) by ID
    //     $acc = Admin::find($admin_id);

    //     // If the account does not exist, return a 404 error
    //     if (is_null($acc)) {
    //         return response()->json(['message' => 'Account not found'], 404);
    //     }

    //     // Prepare the data for updating the account
    //     $input = $request->all();
        
    //     // Check if a new password is provided and hash it if necessary
    //     if ($request->filled('password')) {
    //         $input['password'] = bcrypt($request->password);
    //         $input['currentPassword'] = $acc->password;
    //     }else {
    //         $input['password'] = $acc->password;
    //     }

    //     // Update the account data
    //     $acc->update($input);

    //     // Return a success response with the updated account data
    //     return response()->json([
    //         'message' => 'Account updated successfully',
    //         'account' => $acc
    //     ], 200);
    // }

    
    // public function updateProfileImage(Request $request, $id){
    //     $request->validate([
    //         'admin_pic' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
    //     ]);
    
    //     $admin = Admin::findOrFail($id);
    
    //     if ($request->hasFile('admin_pic')) {
    //         if ($admin->admin_pic) {
    //             Storage::delete('public/profile_images/' . $admin->admin_pic);
                
    //             $htdocsImagePath = 'D:\Laravel\backup\apiDSF\public\profile_images' . $admin->admin_pic;
    //             if (file_exists($htdocsImagePath)) {
    //                 unlink($htdocsImagePath);
    //             }
    //         }
    
    //         $extension = $request->admin_pic->extension();
    //         $imageName = time() . '_' . $admin->id . '.' . $extension;
    //         // $request->Admin_image->storeAs('public/profile_images', $imageName);
    
    //         $htdocsPath = 'D:\Laravel\backup\apiDSF\public\profile_images'; 
    
    //         if (!file_exists($htdocsPath)) {
    //             mkdir($htdocsPath, 0777, true);
    //         }
    
    //         $request->admin_pic->move($htdocsPath, $imageName);
    
    //         $admin->admin_pic = $imageName;
    //         $admin->save();
    
    //         return response()->json([
    //             'message' => 'Profile image updated successfully',
    //             'image_url' => asset('profile_images/' . $imageName) 
    //         ], 200);
    //     }
    
    //     return response()->json(['message' => 'No image file uploaded'], 400);
    // }



    // public function updateProfileImage(Request $request, $id){
    //     $request->validate([
    //         'admin_pic' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
    //     ]);

    //     $admin = Admin::findOrFail($id);

    //     if ($request->hasFile('admin_pic')) {
    //         // Delete the old image if it exists
    //         if ($admin->admin_pic) {
    //             $oldImagePath = public_path('profile_images/' . $admin->admin_pic);
    //             if (file_exists($oldImagePath)) {
    //                 unlink($oldImagePath);
    //             }
    //         }

    //         // Get the file extension and generate a unique name for the image
    //         $extension = $request->file('admin_pic')->extension();
    //         $imageName = time() . '_' . $admin->Admin_ID . '.' . $extension;

    //         // Move the file to the 'public/profile_images' directory
    //         $destinationPath = public_path('profile_images');
    //         $request->file('admin_pic')->move($destinationPath, $imageName);

    //         // Update the database with the new image name
    //         $admin->admin_pic = $imageName;
    //         $admin->save();

    //         // Generate the public URL for the new image
    //         $imageUrl = asset('profile_images/' . $imageName);

    //         return response()->json([
    //             'message' => 'Profile image updated successfully',
    //             'image_url' => $imageUrl
    //         ], 200);
    //     }

    //     return response()->json(['message' => 'No image file uploaded'], 400);
    // }
    

    public function display() {
        try {
            Log::info('Fetching enrollment data started.');
    
            // Get the current year and next year
            $currentYear = date('Y');  // Current year (e.g., 2024)
            $nextYear = $currentYear + 1;  // Next year (e.g., 2025)
    
            // Enable query log to track the SQL being run
            DB::enableQueryLog();
    
            // Execute the query to fetch data
                    $data = DB::table('enrollments')
            ->leftJoin('students', 'enrollments.LRN', '=', 'students.LRN')
            ->leftJoin('payments', 'students.LRN', '=', 'payments.LRN')
            ->leftJoin('tuition_fees', 'enrollments.grade_level', '=', 'tuition_fees.grade_level')
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
                'enrollments.strand',
                'enrollments.date_register',
                'enrollments.guardian_name',
                'enrollments.guardian_no',
                'enrollments.public_private',
                'enrollments.school_year',
                'enrollments.old_account',
                'enrollments.regapproval_date',
                'enrollments.payment_approval',
                'payments.OR_number',
                'payments.amount_paid',
                'payments.proof_payment',
                'payments.date_of_payment',
                'payments.description',
                'tuition_fees.tuition',
                'tuition_fees.general',
                'tuition_fees.esc',
                'tuition_fees.subsidy',
                // Compute the remaining balance
                DB::raw('
                CASE
                    -- For Grades 7 to 10
                    WHEN enrollments.grade_level BETWEEN 7 AND 10 THEN
                        CASE 
                            WHEN tuition_fees.esc IS NOT NULL THEN
                                COALESCE(tuition_fees.tuition, 0) + 
                                COALESCE(tuition_fees.general, 0) - 
                                COALESCE(tuition_fees.esc, 0)  -- With esc for grades 7 to 10
                            ELSE
                                COALESCE(tuition_fees.tuition, 0) + 
                                COALESCE(tuition_fees.general, 0)  -- Without esc for grades 7 to 10
                        END
                    
                    -- For Grades 11 to 12 (Private School)
                    WHEN enrollments.grade_level BETWEEN 11 AND 12 AND enrollments.public_private = "private" THEN
                        CASE 
                            WHEN tuition_fees.esc IS NOT NULL THEN
                                COALESCE(tuition_fees.tuition, 0) + 
                                COALESCE(tuition_fees.general, 0) - 
                                COALESCE(tuition_fees.esc, 0) - 
                                COALESCE(tuition_fees.subsidy, 0) - 14000  -- With esc for private
                            ELSE
                                COALESCE(tuition_fees.tuition, 0) + 
                                COALESCE(tuition_fees.general, 0) - 
                                COALESCE(tuition_fees.subsidy, 0) - 2000  -- Without esc for private
                        END
                    
                    -- For Grades 11 to 12 (Public School)
                    WHEN enrollments.grade_level BETWEEN 11 AND 12 AND enrollments.public_private = "public" THEN
                        CASE 
                            WHEN tuition_fees.esc IS NOT NULL THEN
                                COALESCE(tuition_fees.tuition, 0) + 
                                COALESCE(tuition_fees.general, 0) - 
                                COALESCE(tuition_fees.esc, 0) - 
                                COALESCE(tuition_fees.subsidy, 0) - 17500  -- With esc for public
                            ELSE
                                COALESCE(tuition_fees.tuition, 0) + 
                                COALESCE(tuition_fees.general, 0) - 
                                COALESCE(tuition_fees.subsidy, 0) - 2000  -- Without esc for public
                        END
                    
                    ELSE 0  -- Default case, fallback to 0 if none of the conditions match
                END
                - COALESCE(payments.amount_paid, 0) AS remaining_balance
            ')
            

                // New computation for total tuition
                // DB::raw('
                //     COALESCE(tuition_fees.tuition, 0) + 
                //     COALESCE(tuition_fees.general, 0) +
                //      (
                //         CASE
                //             WHEN enrollments.public_private = "private" THEN 14000
                //             WHEN enrollments.public_private = "public" THEN 17500
                //             ELSE COALESCE(tuition_fees.esc, 0)
                //         END
                //     ) +  
                //     COALESCE(tuition_fees.esc, 0) + 
                //     COALESCE(tuition_fees.subsidy, 0) AS total_tuition
                // ')
            )
            // Filter for the current and next school year
            ->where('enrollments.school_year', '=', $currentYear . '-' . $nextYear)
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
                'enrollments.date_register',
                'enrollments.guardian_name',
                'enrollments.guardian_no',
                'enrollments.public_private',
                'enrollments.school_year',
                'enrollments.strand',
                'enrollments.old_account',
                'enrollments.regapproval_date',
                'enrollments.payment_approval',
                'payments.OR_number',
                'payments.amount_paid',
                'payments.proof_payment',
                'payments.date_of_payment',
                'payments.description',
                'tuition_fees.tuition',
                'tuition_fees.general',
                'tuition_fees.esc',
                'tuition_fees.subsidy'
            )
            ->get();

    
            // Log the executed SQL query
            Log::info('SQL Query Executed: ' . json_encode(DB::getQueryLog()));
    
            // Check if data is empty and log accordingly
            if ($data->isEmpty()) {
                Log::warning('No data found for the provided filter.');
            } else {
                Log::info('Fetching enrollment data completed successfully.');
            }
    
            return response()->json($data, 200);
    
        } catch (\Exception $e) {
            // Log the exception error
            Log::error('Error fetching enrollment data: ' . $e->getMessage());
    
            // Return a JSON response with the error message
            return response()->json(['error' => 'An error occurred while fetching data.'], 500);
        }
    }
    
     
    
    
    public function displaylist() {
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->leftJoin('payments', 'students.LRN', '=', 'payments.LRN') // Left join to include students without payments
            ->join('tuition_fees', 'enrollments.grade_level', '=', 'tuition_fees.grade_level')
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
                'enrollments.strand',
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
                'tuition_fees.tuition',
                DB::raw('tuition_fees.tuition - COALESCE(SUM(payments.amount_paid), 0) AS remaining_balance'), // Remaining balance
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
                'enrollments.strand',
                // 'enrollments.contact_no',
                'enrollments.date_register',
                'enrollments.guardian_name',
                'enrollments.public_private',
                'enrollments.school_year',
                'enrollments.regapproval_date',
                'enrollments.payment_approval',
                'tuition_fees.tuition'
            )
            ->get();
        
        return response()->json($data, 200);
    }

    
    public function displayIN() {
        $data = DB::table('enrollments')
        ->join('students', 'enrollments.LRN', '=', 'students.LRN')
        ->leftJoin('payments', 'students.LRN', '=', 'payments.LRN')
        ->join('tuition_fees', 'enrollments.grade_level', '=', 'tuition_fees.grade_level')
        ->select(
            'students.LRN',
            'students.lname',
            'students.fname',
            'students.mname',
            'students.suffix',
            'students.gender',
            'students.address',
            'enrollments.grade_level',
            'students.contact_no',
            'enrollments.date_register',
            'enrollments.guardian_name',
            'enrollments.public_private',
            'enrollments.school_year',
            'enrollments.strand',
            'enrollments.regapproval_date',
            'enrollments.payment_approval',
            'payments.OR_number',
            'payments.amount_paid',
            'payments.proof_payment',
            'payments.date_of_payment',
            'payments.description',
            'tuition_fees.tuition',
            DB::raw('tuition_fees.tuition - payments.amount_paid AS remaining_balance') 
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
            'students.contact_no',
            'enrollments.date_register',
            'enrollments.guardian_name',
            'enrollments.public_private',
            'enrollments.school_year',
            'enrollments.strand',
            'enrollments.regapproval_date',
            'enrollments.payment_approval',
            'payments.OR_number',
            'payments.amount_paid',
            'payments.proof_payment',
            'payments.date_of_payment',
            'payments.description',
            'tuition_fees.tuition',
        )
        ->get();
    
    return response()->json($data, 200);
    return response()->json(students::all(), 200);
}
    

public function receiptdisplay(Request $request, $id) {
    // Query the database using a join and select statement
    $data = DB::table('enrollments')
        ->join('students', 'enrollments.LRN', '=', 'students.LRN')
        ->join('payments', 'students.LRN', '=', 'payments.LRN')
        ->join('tuition_fees', 'enrollments.grade_level', '=', 'tuition_fees.grade_level')
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
            'enrollments.guardian_no',
            'enrollments.public_private',
            'enrollments.school_year',
            'enrollments.old_account',
            'enrollments.regapproval_date',
            'payments.OR_number',
            'payments.amount_paid',
            'payments.proof_payment',
            'payments.date_of_payment',
            'payments.description',
            'tuition_fees.tuition',
            'tuition_fees.general',
            'tuition_fees.esc',
            'tuition_fees.subsidy',
            // Ensure amount_paid is handled even if it's null
            DB::raw('
                COALESCE(tuition_fees.tuition, 0) + COALESCE(tuition_fees.general, 0) + 
                COALESCE(tuition_fees.esc, 0) + COALESCE(tuition_fees.subsidy, 0) - 
                COALESCE(payments.amount_paid, 0) AS remaining_balance
            ') // Calculate remaining balance
        )
        ->where('students.LRN', $id) // Filter by student ID
        ->first(); // Use first() to get a single record
    
    // Check if data exists and return the result
    if ($data) {
        // Log the proof of payment path
        \Log::info('Proof of payment for student LRN: '.$data->LRN.' - Path: '.$data->proof_payment);
        
        return response()->json($data, 200);
    } else {
        // If no record is found for the given student ID
        return response()->json(['message' => 'Student not found'], 404);
    }
}


    public function approveEnrollment(Request $request, $id){
        // First, retrieve the enrollment data.
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->join('payments', 'students.LRN', '=', 'payments.LRN')
            ->join('tuition_fees', 'enrollments.grade_level', '=', 'tuition_fees.grade_level')
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
                'tuition_fees.tuition',
                DB::raw('tuition_fees.tuition - payments.amount_paid AS remaining_balance') 
            )
            ->where('students.LRN', $id) 
            ->first(); 
    
        if (!$data) {
            return response()->json(['message' => 'Student not found'], 404);
        }
    
        DB::table('enrollments')
            ->where('LRN', $id)
            ->update(['payment_approval' => now()]); 
        
        $data->payment_approval = now();
    
        return response()->json($data, 200); 
    }
    

    public function displayStudent(){
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->join('payments', 'students.LRN', '=', 'payments.LRN')
            ->join('tuition_fees', 'enrollments.grade_level', '=', 'tuition_fees.grade_level')
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
                // 'tuition_fees.tuition',
                // DB::raw('tuition_fees.tuition - payments.amount_paid AS remaining_balance') 
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
                // 'tuition_fees.tuition',
            )
            ->get();
        
        return response()->json($data, 200);
        return response()->json(students::all(), 200);
    }

    public function displaygrade(){
        return response()->json(tuition_fees::orderBy('grade_level','asc')->get(),200);
    }

 
    public function displaySOA(Request $request, $id) {
        $payments = DB::table('payments')
                ->join('enrollments', 'payments.LRN', '=', 'enrollments.LRN')
                ->join('students', 'payments.LRN', '=', 'students.LRN')
                ->leftJoin('tuition_fees', 'enrollments.grade_level', '=', 'tuition_fees.grade_level')
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
                    'tuition_fees.tuition',
                    'tuition_fees.general',
                    'tuition_fees.esc',
                    'tuition_fees.subsidy',
                    DB::raw('COALESCE(SUM(payments.amount_paid), 0) AS total_paid'),
                    DB::raw('COALESCE(SUM(tuition_fees.tuition), 0) AS total_tuition'),
                    DB::raw('COALESCE(SUM(tuition_fees.general), 0) AS total_general'),
                    DB::raw('COALESCE(SUM(tuition_fees.esc), 0) AS total_esc'),
                    DB::raw('COALESCE(SUM(tuition_fees.subsidy), 0) AS total_subsidy')
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
                    'tuition_fees.tuition',
                    'tuition_fees.general',
                    'tuition_fees.esc',
                    'tuition_fees.subsidy'
                )
                ->get();
        
        // Calculate the total tuition, general, esc, and subsidy fees (add them all up)
        $tuition = $payments->isNotEmpty() ? $payments[0]->total_tuition : 0;
        $general = $payments->isNotEmpty() ? $payments[0]->total_general : 0;
        $esc = $payments->isNotEmpty() ? $payments[0]->total_esc : 0;
        $subsidy = $payments->isNotEmpty() ? $payments[0]->total_subsidy : 0;
        
        // Calculate the total balance before payments
        $totalBalance = $tuition + $general + $esc + $subsidy;
    
        // Initialize remaining balance
        $remainingBalance = $totalBalance;
    
        // Create an array to hold the payment details with running balance
        $paymentDetails = [];
        $totalPaid = 0; // This will hold the total paid amount
    
        foreach ($payments as $payment) {
            // Subtract the current payment from the remaining balance
            $remainingBalance -= $payment->amount_paid;
    
            // Add to payment details with the current balance
            $paymentDetails[] = [
                'LRN' => $payment->LRN,
                'name' => "{$payment->lname} {$payment->fname} {$payment->mname}",
                'tuition' => $payment->tuition,
                'general' => $payment->general,
                'esc' => $payment->esc,
                'subsidy' => $payment->subsidy,
                'OR_number' => $payment->OR_number,
                'description' => $payment->description,
                'amount_paid' => $payment->amount_paid,
                'date_of_payment' => $payment->date_of_payment,
                'remaining_balance' => $remainingBalance
            ];
    
            // Add the current payment to the total paid amount
            $totalPaid += $payment->amount_paid;
        }
    
        // Return the total paid, total balance, and payment details
        return response()->json([
            'total_balance' => $totalBalance,
            'payments' => $paymentDetails,
            'remaining_balance' => $remainingBalance,
            'total_paid' => $totalPaid  // Added total paid here
        ], 200);
    }
    
    
    
    public function updatepayment(Request $request, $id) {
        $request->validate([
            'OR_number' => "required|string",
            'description' => 'required|string',
            'amount_paid' => 'required|numeric',
            'LRN' => 'required|string',
            'old_account' => 'nullable|numeric', // Validate old_account
        ]);
    
        // Find the enrollment record by LRN
        $enrollment = DB::table('enrollments')->where('LRN', $id)->first();
    
        if (!$enrollment) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }
    
        // Update the old_account in the enrollments table
        DB::table('enrollments')->where('LRN', $id)->update([
            'old_account' => $request->old_account, // Update old_account
        ]);
    
        // Optionally, you can also update the payments table if needed
        $payment = DB::table('payments')->where('LRN', $id)->first();
        
        if ($payment) {
            // Update payment record if it exists
            DB::table('payments')->where('LRN', $id)->update([
                'OR_number' => $request->OR_number,
                'description' => $request->description,
                'amount_paid' => $request->amount_paid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    
        // Retrieve updated enrollments and payments
        $enrollments = DB::table('enrollments')->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'message' => 'Success',
            'data' => [
                'enrollments' => $enrollments,
                // Optionally include updated payment data if needed
                // 'payments' => DB::table('payments')->orderBy('created_at', 'desc')->get(),
            ],
        ], 200);
    }
    
    // public function updatepayment(Request $request, $id) {
    //     $request->validate([
    //         'OR_number' => "required|string",
    //         'description' => 'required|string',
    //         'amount_paid' => 'required|numeric',
    //         'LRN' => 'required|string',
    //     ]);
    
    //     // Find the payment record by LRN
    //     $payment = DB::table('payments')->where('LRN', $id)->first();
    
    //     if (!$payment) {
    //         return response()->json(['message' => 'Payment not found'], 404);
    //     }
    
    //     // Update the payment record
    //     DB::table('payments')->where('LRN', $id)->update([
    //         'OR_number' => $request->OR_number,
    //         'description' => $request->description,
    //         'amount_paid' => $request->amount_paid,
    //         'LRN' => $request->LRN,
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);
    
    //     // Retrieve the updated payments
    //     $payments = DB::table('payments')->orderBy('created_at', 'desc')->get();
    
    //     return response()->json([
    //         'message' => 'Success',
    //         'data' => $payments,
    //     ], 200);
    // }      

    //Upload.......
    public function uploadfiles(Request $request, $id){
        // Validate the incoming request
        $request->validate([
            'filename' => 'required|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx|max:2048',
        ]);

        // Prepare the new image name
        $extension = $request->filename->extension();
        // $imageName = time() . '_' . $id . '_' . uniqid() . '.' . $extension;
        $imageName = $id . '.' . $extension;

        $htdocsPath = 'D:/Laravel/backup/apiDSF/public/images'; 
        // $htdocsPath = 'C:/xampp/htdocs/SOA'; 
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

    // for tuition fee
    public function addtuitionfee(Request $request){
        $request->validate([
            'grade_level' => 'required|integer',   // Change to integer validation
            'tuition' => 'required|numeric',
            'general' => 'required|numeric',
            'esc' => 'required|numeric', // Enum validation
            'subsidy' => 'nullable|numeric',
            'req_Downpayment' => 'required|numeric',
        ]);
    
        DB::table('tuition_fees')->insert([
            'grade_level' => $request->grade_level,
            'tuition' => $request->tuition,
            'general' => $request->general,
            'esc' => $request->esc,
            'subsidy' => $request->subsidy,
            'req_Downpayment' => $request->req_Downpayment,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        $tuitionlist = DB::table('tuition_fees')->orderBy('created_at', 'desc')->get();
    
        return response()->json([
            'message' => 'Success',
            'data' => $tuitionlist,
        ], 201);
    }
    
    public function tuitiondisplay() {
        $data = DB::table('tuition_fees')
            ->select(
                'fee_id', 
                'created_at', 
                'updated_at', 
                'grade_level', 
                'tuition', 
                'general', 
                'esc', 
                'subsidy', 
                'req_Downpayment'
            )
            ->get();
        
        return response()->json($data, 200);
    }

    public function findfees(Request $request, $id) {
        // Fetch the tuition fees based on the grade level
        $data = DB::table('tuition_fees')
            ->select(
                'tuition_fees.grade_level',
                'tuition_fees.tuition',
                'tuition_fees.general',
                'tuition_fees.esc',
                'tuition_fees.subsidy',
                'tuition_fees.req_Downpayment',
                'tuition_fees.created_at',
                'tuition_fees.updated_at'
            )
            ->where('tuition_fees.fee_id', $id) // Filter by grade level
            ->first(); // Use first() to get a single record
    
        // Return the data or error if not found
        if ($data) {
            return response()->json($data, 200);
        } else {
            return response()->json(['message' => 'Tuition fees for this grade level not found'], 404);
        }
    }
    
    public function updateTuitionFee(Request $request, $id) {
        // Log the incoming ID
        Log::info('Received fee_id in backend:', ['fee_id' => $id]);
    
        // Log the incoming request data
        Log::info('Received data in backend:', ['request_data' => $request->all()]);
        
        // Validate the incoming request data
        $validatedData = $request->validate([
            'grade_level' => 'required|integer',
            'tuition' => 'required|numeric',
            'general' => 'required|numeric',
            'esc' => 'nullable|numeric', // esc is nullable
            'subsidy' => 'nullable|numeric', // subsidy is nullable
            'req_Downpayment' => 'required|numeric',
        ]);
        
        // Log validation success
        Log::info('Request data validated successfully', ['validated_data' => $validatedData]);
    
        // Find the tuition fee record by fee_id (primary key)
        $tuitionFee = DB::table('tuition_fees')->where('fee_id', $id)->first();
        if (!$tuitionFee) {
            Log::warning('Tuition fee not found for ID: ' . $id);  // Log if tuition fee is not found
            return response()->json(['message' => 'Tuition fee not found'], 404);
        }
    
        // Log the current tuition fee record before updating
        Log::info('Current tuition fee record before update:', ['current_record' => $tuitionFee]);
    
        // Log before updating the record
        Log::info('Updating tuition fee record with new values', [
            'fee_id' => $id,
            'new_data' => $request->all()
        ]);
        
        // Update the tuition fee record with the new values
        DB::table('tuition_fees')->where('fee_id', $id)->update([
            'grade_level' => $request->grade_level,
            'tuition' => $request->tuition,
            'general' => $request->general,
            'esc' => $request->esc, // It can be null
            'subsidy' => $request->subsidy, // It can be null
            'req_Downpayment' => $request->req_Downpayment,
            'updated_at' => now(),
        ]);
    
        // Log after updating
        Log::info('Tuition fee record updated successfully for ID: ' . $id);
    
        // Retrieve the updated tuition fee records
        $tuitionFees = DB::table('tuition_fees')->orderBy('created_at', 'desc')->get();
    
        // Log the retrieved records
        Log::info('Retrieved updated tuition fees', ['tuition_fees' => $tuitionFees]);
    
        // Return the response with the updated tuition fees
        return response()->json([
            'message' => 'Tuition fee updated successfully',
            'data' => $tuitionFees,
        ], 200);
    }
    
    public function deleteTuitionFee($id){
    // Check if the record exists
    $tuitionFee = DB::table('tuition_fees')->where('fee_id', $id)->first();

    if (!$tuitionFee) {
        return response()->json(['message' => 'Tuition fee not found'], 404);
    }

    // Delete the record
    DB::table('tuition_fees')->where('fee_id', $id)->delete();

    // Return a success response
    return response()->json(['message' => 'Tuition fee deleted successfully'], 200);
}




    // view_financials
    public function displayFinancials(Request $request, $id) {
        // Query the database using a join and select statement
        $data = DB::table('enrollments')
            ->join('students', 'enrollments.LRN', '=', 'students.LRN')
            ->join('payments', 'students.LRN', '=', 'payments.LRN')
            ->join('tuition_fees', 'enrollments.grade_level', '=', 'tuition_fees.grade_level')
            ->leftJoin('financial_statements', 'students.LRN', '=', 'financial_statements.LRN') // Added the left join with financial_statements
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
                'tuition_fees.tuition',
                'tuition_fees.general',
                'tuition_fees.esc',
                'tuition_fees.subsidy',
                // Ensure amount_paid is handled even if it's null
                DB::raw('
                    COALESCE(tuition_fees.tuition, 0) + COALESCE(tuition_fees.general, 0) + 
                    COALESCE(tuition_fees.esc, 0) + COALESCE(tuition_fees.subsidy, 0) - 
                    COALESCE(payments.amount_paid, 0) AS remaining_balance
                '), // Calculate remaining balance
                'financial_statements.soa_id', // Added financial_statements columns
                'financial_statements.filename', // Include the filename
                'financial_statements.date_uploaded' // Date of the file upload
            )
            ->where('students.LRN', $id) // Filter by student ID
            ->first(); // Use first() to get a single record
        
        // Check if data exists and return the result
        if ($data) {
            // Log the filename path if it exists
            if ($data->filename) {
                \Log::info('Financial statement filename path: ', ['filename' => $data->filename]);
            }
            
            return response()->json($data, 200);
        } else {
            // If no record is found for the given student ID
            return response()->json(['message' => 'Student not found'], 404);
        }
    }



    // public function displayFinancials(Request $request, $id) {
    //     // Check if a file is uploaded
    //     if ($request->hasFile('proof_payment')) {
    //         // Retrieve the uploaded file
    //         $file = $request->file('proof_payment');
            
    //         // Get the original filename
    //         $filename = $file->getClientOriginalName();
            
    //         // Store the file in a specific path
    //         $path = $file->storeAs('uploads', $filename); // Adjust path as necessary
    
    //         // Log the filename for debugging
    //         \Log::info('Uploaded proof of payment filename: ', ['filename' => $filename]);
    //     } else {
    //         \Log::warning('No proof of payment file uploaded.');
    //     }
    
    //     // Query the database using a join and select statement
    //     $data = DB::table('enrollments')
    //         ->join('students', 'enrollments.LRN', '=', 'students.LRN')
    //         ->join('payments', 'students.LRN', '=', 'payments.LRN')
    //         ->join('tuition_fees', 'enrollments.grade_level', '=', 'tuition_fees.grade_level')
    //         ->leftJoin('financial_statements', 'students.LRN', '=', 'financial_statements.LRN') // Added the left join with financial_statements
    //         ->select(
    //             'students.LRN',
    //             'students.lname',
    //             'students.fname',
    //             'students.mname',
    //             'students.suffix',
    //             'students.gender',
    //             'students.address',
    //             'students.contact_no',
    //             'enrollments.grade_level',
    //             'enrollments.date_register',
    //             'enrollments.guardian_name',
    //             'enrollments.public_private',
    //             'enrollments.school_year',
    //             'enrollments.regapproval_date',
    //             'payments.OR_number',
    //             'payments.amount_paid',
    //             'payments.proof_payment',
    //             'payments.date_of_payment',
    //             'payments.description',
    //             'tuition_fees.tuition',
    //             'tuition_fees.general',
    //             'tuition_fees.esc',
    //             DB::raw('COALESCE(tuition_fees.subsidy, 0) AS subsidy'),
    //             DB::raw('
    //                 COALESCE(tuition_fees.tuition, 0) + COALESCE(tuition_fees.general, 0) + 
    //                 COALESCE(tuition_fees.esc, 0) + COALESCE(tuition_fees.subsidy, 0) - 
    //                 COALESCE(payments.amount_paid, 0) AS remaining_balance
    //             ')
    //         )
    //         ->where('students.LRN', $id) // Filter by student ID
    //         ->first(); // Use first() to get a single record
        
    //     // Check if data exists and return the result
    //     if ($data) {
    //         // Construct image URL based on where images are stored
    //         if (property_exists($data, 'image')) {
    //             $data->image_url = url('uploads/' . $data->image); // Construct image URL
                
    //             // Log for debugging
    //             \Log::info('Image URL: ', ['image_url' => $data->image_url]);
    //         } else {
    //             // If no image property exists, set a default or null value
    //             $data->image_url = null; // or set a default image URL
    //         }
    
    //         return response()->json($data, 200);
    //     } else {
    //         // If no record is found for the given student ID
    //         return response()->json(['message' => 'Student not found'], 404);
    //     }
    // }
    

    

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


    public function upload(Request $request){
        // Validate the incoming request
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        Log::info('Image upload initiated.', ['request' => $request->all()]);
    
        // Initialize an array to hold filenames
        $filenames = [];
    
        // Process each uploaded image
        foreach ($request->file('images') as $image) {
            // Get the original filename and extension
            $originalFilename = $image->getClientOriginalName();
            $extension = $image->extension();
    
            // Move the image to the desired location (public/images) using the original filename
            $image->move(public_path('images'), $originalFilename);
    
            Log::info('Image uploaded successfully.', ['filename' => $originalFilename]);
    
            // Store LRN based on original filename (without extension)
            $filenames[] = pathinfo($originalFilename, PATHINFO_FILENAME);
        }
    
        // Log filenames before querying students
        Log::info('Filenames being queried for students.', ['filenames' => $filenames]);
    
        // Get LRNs from students table based on filenames
        $students = DB::table('students')->whereIn('LRN', $filenames)->get();
    
        Log::info('Retrieved students based on filenames.', ['students' => $students]);
    
        // Create a mapping of LRN to the corresponding filename
        $filenameMap = [];
        foreach ($filenames as $index => $filename) {
            if (isset($students[$index])) {
                $filenameMap[$students[$index]->LRN] = $filename . '.' . $extension; // Map LRN to the corresponding filename
            }
        }
    
        foreach ($students as $student) {
            // Use the mapped filename for financial statements
            $uniqueFilename = $filenameMap[$student->LRN] ?? null;
    
            if ($uniqueFilename) {
                financial_statements::create([
                    'LRN' => $student->LRN,
                    'filename' => $uniqueFilename,
                    'date_uploaded' => now()->format('Y-m-d')
                ]);
    
                Log::info('Financial statement entry created.', ['LRN' => $student->LRN, 'filename' => $uniqueFilename]);
            }
        }
    
        return response()->json(['message' => 'Images uploaded successfully']);
    }
   
}
