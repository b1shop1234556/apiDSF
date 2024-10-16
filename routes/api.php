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



Route::post('/student', [StudentsController::class, 'student']);
Route::post('/enrollment', [EnrollmentsController::class, 'enrollment']);
Route::post('/payment', [PaymentsController::class, 'stupaymentdent']);

Route::post('/tuition', [TuitionsController::class, 'tuition']);

Route::post('/register', [DsfController::class, 'register']);
Route::post('/login', [DsfController::class, 'login']);
Route::post('/logout', [DsfController::class, 'logout'])->middleware('auth:sanctum');

Route::get('displaylist', [DsfController::class, 'displaylist']);
Route::get('/display', [DsfController::class, 'display']);
Route::get('/receiptdisplay/{id}', [DsfController::class, 'receiptdisplay']);

Route::get('/approveEnrollment/{id}', [DsfController::class, 'approveEnrollment']);
Route::get('/displaygrade', [DsfController::class, 'displaygrade']);
Route::get('/displaySOA/{id}', [DsfController::class, 'displaySOA']);
Route::get('/displayStudent', [DsfController::class, 'displayStudent']);

Route::put('/updatepayment/{id}', [DsfController::class, 'updatepayment']);

//uploadIMG.....
Route::post('/uploadfiles', [DsfController::class, 'uploadfiles']);

//for msg
Route::get('/getMessages', [DsfController::class, 'getMessages']);
Route::get('displaymsg', [DsfController::class, 'displaymsg']);
Route::get('/displayTWO', [DsfController::class, 'displayTWO']);
Route::post('/messages', [DsfController::class, 'send']);
Route::get('/messages', [DsfController::class, 'index']);

