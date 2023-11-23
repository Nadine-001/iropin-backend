<?php

namespace App\Http\Controllers;

use App\Models\RegistrationPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationPaymentController extends Controller
{
    public function registPayment()
    {
        $user = Auth::user();
        $regist_payment = $user->registration_payment;
        if (!$regist_payment) {
            return response()->json([
                "message" => "Payment not found"
            ], 400);
        }
        return $regist_payment;
    }

    public function uploadReceipt(Request $request)
    {
        $user = Auth::user();
        $reg_payment = $user->registration_payment;
        if (!$reg_payment) {
            return response()->json([
                "message" => "Payment not found"
            ], 400);
        }

        $reg_payment->update([
            'payment_receipt' => $request->payment_receipt,
            'payment_date' => date('Y-m-d'),
            'status' => 1,
        ]);

        return response()->json([
            'reg_payment' => $reg_payment,
        ]);
    }

    public function checkPaymentRegistration(Request $request, $user_id)
    {
        $registration_payment = RegistrationPayment::where('user_id', $user_id)->first();
        if (!$registration_payment) {
            return response()->json([
                "message" => "Payment not found"
            ], 400);
        }

        return response()->json([
            'result' => $registration_payment->status
        ]);
    }
}
