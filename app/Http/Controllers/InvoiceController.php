<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Participant;
use App\Models\Webinar;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    function webinarRegistration(Request $request, $webinar_id)
    {
        $user = Auth::user();
        $webinar = Webinar::findOrFail($webinar_id);
        $participant = Participant::where('user_id', $user->id)
            ->where('webinar_id', $webinar_id)
            ->first();

        if ($participant) {
            return response()->json([
                'message' => 'Invoice already uploaded',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'invoice' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        DB::beginTransaction();
        try {
            if ($request->file('invoice')) {
                $invoice = $request->file('invoice');

                $ext = $invoice->getClientOriginalExtension();
                $file_name = time() . " - " . $invoice->getClientOriginalName();
                $file_name = str_replace(' ', '', $file_name);
                $path = asset("uploads/invoiceWebinar" . $file_name);
                $invoice->move(public_path('uploads/invoiceWebinar'), $file_name);

                $invoice = Invoice::create([
                    'path' => $path,
                    'ext' => $ext,
                    'file_name' => $file_name,
                ]);

                $participant = Participant::create([
                    'invoice_id' => $invoice->id,
                    'user_id' => $user->id,
                    'webinar_id' => $webinar->id,
                    'key' => 'invoice',
                    'val' => $file_name,
                    'file_id' => $invoice->id,
                ]);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'register failed',
                'errors' => $th->getMessage()
            ]);
        }

        return response()->json([
            'message' => 'Invoice uploaded successfully',
            'invoice' => $invoice,
        ]);
    }
}
