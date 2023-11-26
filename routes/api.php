<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LicenceController;
use App\Http\Controllers\LicenceFormDetailController;
use App\Http\Controllers\RegistrationPaymentController;
use App\Http\Controllers\WebinarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/getUserProfile', [UserController::class, 'profile']);
    Route::put('/updateProfile', [AuthController::class, 'update']);
    // Route::put('/updateUsers/{user_id}', [UserController::class, 'update']);
    Route::get('/getRegistPayment', [RegistrationPaymentController::class, 'registPayment']);
    Route::put('/paymentReceipt', [RegistrationPaymentController::class, 'uploadReceipt']);

    Route::post('/requestLicence', [LicenceController::class, 'requestLicence']);
    Route::post('/webinarRegistration/{webinar_id}', [InvoiceController::class, 'webinarRegistration']);

    Route::get('/getUsers', [UserController::class, 'index'])->middleware('operator');;
    Route::get('/checkPaymentRegistration/{user_id}', [RegistrationPaymentController::class, 'checkPaymentRegistration']);
    Route::put('/users/{user_id}/activate', [UserController::class, 'activateUser'])->middleware('operator');
    Route::put('/users/{user_id}/deactivate', [UserController::class, 'deactivateUser'])->middleware('operator');

    Route::get('/licenceList', [LicenceController::class, 'licenceList'])->middleware('operator');
    Route::get('/licenceList/{licence_id}', [LicenceController::class, 'licenceListDetail'])->middleware('operator');
    Route::post('/validateLicence/{licence_id}', [LicenceController::class, 'validateLicence'])->middleware('operator');
    Route::post('/declineLicence/{licence_id}', [LicenceController::class, 'declineLicence'])->middleware('operator');

    Route::post('/addWebinar', [WebinarController::class, 'addWebinar'])->middleware('operator');
    //WebinarList?
    Route::get('/participantList', [WebinarController::class, 'participantList'])->middleware('operator');
    Route::get('/participantList/{participant_id}', [WebinarController::class, 'participantListDetail'])->middleware('operator');
    Route::post('/validateParticipant/{participant_id}', [WebinarController::class, 'validateParticipant'])->middleware('operator');
    Route::post('/declineParticipant/{participant_id}', [WebinarController::class, 'declineParticipant'])->middleware('operator');

    Route::post('/logout', [AuthController::class, 'logout']);
});
