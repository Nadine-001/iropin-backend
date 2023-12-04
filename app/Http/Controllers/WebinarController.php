<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Webinar;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class WebinarController extends Controller
{
    public function addWebinar(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'date' => 'required',
            'speaker' => 'required',
            'price' => 'required',
            'place' => 'required',
            'poster' => 'required|file',
            'materi' => 'required|file',
            'link' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $poster  = null;
            if ($request->file('poster')){
                $poster = $request->file('poster');

                $file_name = time() . " - " . $poster->getClientOriginalName();
                $file_name = str_replace(' ', '', $file_name);
                $path_poster = asset("public/uploads/Webinar/" . $file_name);
                $poster->move(public_path('uploads/Webinar'), $file_name);
            }

            $materi  = null;
            if ($request->file('materi')){
                $materi = $request->file('materi');

                $file_name = time() . " - " . $materi->getClientOriginalName();
                $file_name = str_replace(' ', '', $file_name);
                $path_materi = asset("public/uploads/Webinar/" . $file_name);
                $materi->move(public_path('uploads/Webinar'), $file_name);
            }

            $webinar = Webinar::create([
                'title' => $request->title,
                'date' => $request->date,
                'speaker' => $request->speaker,
                'price' => $request->price,
                'place' => $request->place,
                'description' => $request->description,
                'poster' => $poster ? $path_poster : null,
                'materi' => $materi ? $path_materi : null,
                'link' => $request->link,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to add the webinar',
                'errors' => $th->getMessage()
            ]);
        }

        return response()->json([
            'webinar' => $webinar
        ]);
    }

    public function webinarList(Request $request) {
        $webinars = Webinar::all();

        return response()->json($webinars);
    }

    public function webinarParticipants(Request $request, $webinar_id) {
        // $participants = Participant::where('webinar_id', $webinar_id)
        //     ->with('user.biodata')
        //     ->get();

        $participants = Participant::where('webinar_id', $webinar_id)->get();

        try {
            $participant_data = $participants->map(function ($participant) {
                return [
                    'participant' => [
                        'name' => $participant->user->biodata->name,
                        'membership_number' => $participant->user->membership_number,
                        // 'profile_photo' => $participant->user->,
                    ],
                ];
            });
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to get participants',
                'errors' => $th->getMessage()
            ]);
        }

        return response()->json($participant_data);
    }

    public function participantList(Request $request) {
        $participants = Participant::all();

        try {
            $participant_data = $participants->map(function ($participant) {
                return [
                    'participant' => [
                        'participant_id' => $participant->id,
                        'created_at' => $participant->created_at->toDateString(),
                        'name' => $participant->user->biodata->name,
                        'title' => $participant->webinar->title,
                        'status' => $participant->status,
                    ],
                ];
            });
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to get participant list',
                'errors' => $th->getMessage()
            ]);
        }

        return response()->json([
            'participant' => $participant_data
        ]);
    }

    public function participantListDetail(Request $request, $participant_id) {
        $participant = Participant::with('invoice', 'webinar', 'user')->find($participant_id);

        if (!$participant) {
            return response()->json([
                "message" => "participant not found"
            ], 404);
        }

        $invoice = $participant->invoice;

        $invoice_data = [
                'file_name' => $invoice->file_name,
                'file_path' => $invoice->path,
        ];

        return response()->json([
            'created_at' => $participant->created_at->toDateString(),
            'name' => $participant->user->biodata->name,
            'title' => $participant->webinar->title,
            'date' => $participant->webinar->date,
            'invoice' => $invoice_data,
        ]);
    }

    public function validateParticipant(Request $request, $participant_id) {
        $participant = Participant::with('invoice')->find($participant_id);

        $invoice = $participant->invoice;

        try {
            $checked = $request->input('checked');
            $checked_decode = json_decode($checked, true);

            foreach ($checked_decode as $fileName => $is_checked) {
                $invoice->is_checked = $is_checked;
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to checklist the invoice',
                'errors' => $th->getMessage()
            ]);
        }

        $participant->status = 1;

        $invoice->save();
        $participant->save();

        return response()->json([
            'message' => 'participant verified'
        ]);
    }

    public function declineParticipant(Request $request, $participant_id) {
        $validator = Validator::make($request->all(), [
            'note' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $participant = Participant::with('invoice')->find($participant_id);

        $invoice = $participant->invoice;

        try {
            $checked = $request->input('checked');
            $checked_decode = json_decode($checked, true);

            foreach ($checked_decode as $fileName => $is_checked) {
                $invoice->is_checked = $is_checked;
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to checklist the invoice',
                'errors' => $th->getMessage()
            ]);
        }

        $participant->status = 2;
        $participant->note = $request->note;

        $invoice->save();
        $participant->save();

        return response()->json([
            'message' => 'participant denied'
        ]);
    }
}
