<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Participant;
use App\Models\Webinar;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class WebinarController extends Controller
{
    function addWebinar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'date' => 'required',
            'speaker' => 'required',
            'price' => 'required',
            'place' => 'required',
            'poster' => 'required',
            'theme' => 'required',
            'link' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        if ($request->file('poster')){
            $poster = $request->file('poster');

            $file_name = time() . " - " . $poster->getClientOriginalName();
            $file_name = str_replace(' ', '', $file_name);
            $path = asset("uploads/" . $file_name);
            $poster->move(public_path('uploads/posterWebinar'), $file_name);
        }

        $webinar = Webinar::create([
            'title' => $request->title,
            'date' => $request->date,
            'speaker' => $request->speaker,
            'price' => $request->price,
            'place' => $request->place,
            'description' => $request->description,
            'poster' => $path,
            'theme' => $request->theme,
            'link' => $request->link,
        ]);

        return response()->json([
            'webinar' => $webinar
        ]);
    }

    function validateWebinar(Request $request, $participant_id)
    {
        $participant = Participant::findOrFail($participant_id)
            ->with('invoice')
            ->first();

        $webinar_id = $participant->webinar_id;
        $invoice_id = $participant->invoice_id;
        dd($participant);
        $invoice = $participant->invoice;

        $checked = $request->input('checked');
        $checked_decode = json_decode($checked, true);

        foreach ($checked_decode as $fileName => $is_checked) {
            $invoice->is_checked = $is_checked;
            $invoice->save();
        }

        return response()->json(['message' => 'invoice checked succesfully']);
    }
}
