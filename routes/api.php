<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LicenceController;
use App\Http\Controllers\LicenceFormDetailController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StatisticController;
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

Route::post('/register', [RegistrationController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgotPassword', [UserController::class, 'forgotPassword']);
Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');
// Route::get('/resetPassword/{token}', [UserController::class, 'resetPassword']);
Route::post('/resetPassword', [UserController::class, 'updatePassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/getUserProfile', [UserController::class, 'profile']);
    Route::put('/updateProfile', [AuthController::class, 'update']);
    Route::get('/member_status', [AuthController::class, 'member_status']);
    // Route::put('/updateUsers/{user_id}', [UserController::class, 'update']);

    Route::post('/requestLicence', [LicenceController::class, 'requestLicence']);
    Route::get('/licenceApproved', [LicenceController::class, 'licenceApproved']);
    Route::get('/showApproved/{licence_id}', [LicenceController::class, 'showApproved']);
    Route::get('/webinarList', [WebinarController::class, 'webinarList']);
    Route::get('/webinarList/{webinar_id}', [WebinarController::class, 'webinarListDetail']);
    Route::post('/webinarRegistration/{webinar_id}', [InvoiceController::class, 'webinarRegistration']);
    Route::get('/materiWebinar/{webinar_id}', [WebinarController::class, 'materiWebinar']);
    Route::get('/linkWebinar/{webinar_id}', [WebinarController::class, 'linkWebinar']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('manager')->group(function () {
        Route::get('/userList', [UserController::class, 'userList']);
        Route::post('/addUser', [UserController::class, 'addUser']);
        Route::put('/updateUser/{user_id}', [UserController::class, 'updateUser']);
        Route::delete('/deleteUser/{email}', [UserController::class, 'deleteUser']);
        Route::get('/approvalList', [LicenceController::class, 'approvalList']);
        Route::get('/showApproval/{licence_id}', [LicenceController::class, 'showApproval']);
        Route::post('/sendApproval/{licence_id}', [LicenceController::class, 'sendApproval']);
    });

    Route::middleware('operator')->group(function () {
        Route::get('/getUsers', [UserController::class, 'getUsers']);
        Route::get('/getUsers/{user_id}', [UserController::class, 'getUserDetail']);
        Route::get('/registrationList', [RegistrationController::class, 'registrationList']);
        Route::get('/registrationList/{user_id}', [RegistrationController::class, 'registrationListDetail']);
        Route::post('/validateRegistration/{user_id}', [UserController::class, 'validateRegistration']);
        Route::post('/declineRegistration/{licence_id}', [UserController::class, 'declineRegistration']);

        Route::get('/licenceList', [LicenceController::class, 'licenceList']);
        Route::get('/licenceList/{licence_id}', [LicenceController::class, 'licenceListDetail']);
        Route::post('/validateLicence/{licence_id}', [LicenceController::class, 'validateLicence']);
        Route::post('/declineLicence/{licence_id}', [LicenceController::class, 'declineLicence']);

        Route::post('/addWebinar', [WebinarController::class, 'addWebinar']);
        Route::get('/webinarList/{webinar_id}/participantList', [WebinarController::class, 'webinarParticipants']);
        Route::get('/participantList', [WebinarController::class, 'participantList']);
        Route::get('/participantList/{participant_id}', [WebinarController::class, 'participantListDetail']);
        Route::post('/validateParticipant/{participant_id}', [WebinarController::class, 'validateParticipant']);
        Route::post('/declineParticipant/{participant_id}', [WebinarController::class, 'declineParticipant']);

        Route::get('/getTotalParticipant', [WebinarController::class, 'getTotalParticipant']);
        Route::get('/getMemberStatistic', [StatisticController::class, 'getMemberStatistic']);
        Route::get('/getVerificationStatistic', [StatisticController::class, 'getVerificationStatistic']);
        Route::get('/getEmployeeStatistic', [StatisticController::class, 'getEmployeeStatistic']);
    });
});
