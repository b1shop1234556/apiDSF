<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DsfController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::get('/', function () {
    return "API";
});


Route::put('/update-password', [DsfController::class, 'updatePass']);
Route::post('/upload-image', [DsfController::class, 'uploadImage']);
Route::get('assets/adminPic/{filename}', function ($filename) {
    $path = public_path('assets/adminPic/' . $filename);
    
    if (file_exists($path)) {
        return response()->file($path);
    }

    abort(404);
});


Route::post('/student', [StudentsController::class, 'student']);
Route::post('/enrollment', [EnrollmentsController::class, 'enrollment']);
Route::post('/payment', [PaymentsController::class, 'stupaymentdent']);

Route::post('/tuition', [TuitionsController::class, 'tuition']);

Route::post('/register', [DsfController::class, 'register']);
Route::post('/login', [DsfController::class, 'login']);
Route::post('/logout', [DsfController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/findacc/{id}', [DsfController::class, 'findacc']);
Route::put('/updateacc/{id}', [DsfController::class, 'updateacc']);
Route::post('/profile-image/{id}', [DsfController::class, 'updateProfileImage']);

Route::get('displaylist', [DsfController::class, 'displaylist']);
Route::get('/display', [DsfController::class, 'display']);
Route::get('/receiptdisplay/{id}', [DsfController::class, 'receiptdisplay']);

Route::get('/approveEnrollment/{id}', [DsfController::class, 'approveEnrollment']);
Route::get('/displaygrade', [DsfController::class, 'displaygrade']);
Route::get('/displaySOA/{id}', [DsfController::class, 'displaySOA']);
Route::get('/displayStudent', [DsfController::class, 'displayStudent']);

Route::put('/updatepayment/{id}', [DsfController::class, 'updatepayment']);

Route::get('displayIN', [DsfController::class, 'displayIN']);

//uploadIMG.....
Route::post('/uploadfiles/{id}', [DsfController::class, 'uploadfiles']);

//view_financials
// Route::get('/displayFinancials', [DsfController::class, 'displayFinancials']);
Route::get('/displayFinancials/{id}', [DsfController::class, 'displayFinancials']);

//for msg
Route::get('/getMessages', [DsfController::class, 'getMessages']);
Route::get('displaymsg', [DsfController::class, 'displaymsg']);
Route::get('/displayTWO', [DsfController::class, 'displayTWO']);
Route::post('/messages', [DsfController::class, 'send']);
Route::get('/messages', [DsfController::class, 'index']);

//---insert---
Route::post('/addtuitionfee', [DsfController::class, 'addtuitionfee']);
Route::get('/tuitiondisplay', [DsfController::class, 'tuitiondisplay']);
Route::put('/updateTuitionFee/{id}', [DsfController::class, 'updateTuitionFee']);

Route::get('/findfees/{id}', [DsfController::class, 'findfees']);

Route::post('/upload-images', [DsfController::class, 'upload']);