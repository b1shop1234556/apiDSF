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
Route::get('/display', [DsfController::class, 'display']);