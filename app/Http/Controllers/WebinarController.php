<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Webinar;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class WebinarController extends Controller
{
    public function addWebinar(Request $request)
    {
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
            if ($request->file('poster')) {
                $poster = $request->file('poster');

                $file_name = time() . " - " . $poster->getClientOriginalName();
                $file_name = str_replace(' ', '', $file_name);
                $path_poster = asset("uploads/Webinar/" . $file_name);
                $poster->move(public_path('uploads/Webinar/'), $file_name);
            }

            $materi  = null;
            if ($request->file('materi')) {
                $materi = $request->file('materi');

                $file_name = time() . " - " . $materi->getClientOriginalName();
                $file_name = str_replace(' ', '', $file_name);
                $path_materi = asset("uploads/Webinar/" . $file_name);
                $materi->move(public_path('uploads/Webinar/'), $file_name);
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

    public function getTotalParticipant(Request $request)
    {
        $webinars = Webinar::select('id', 'title')
            ->withCount([
                'participants as verified_participants' => function (Builder $query) {
                    $query->where('status', 1);
                },
                'participants as total_participant'
            ])
            ->get();

        return response()->json([
            'webinars' => $webinars
        ]);
    }

    public function webinarList(Request $request)
    {
        $webinars = Webinar::all();

        try {
            $webinar_data = $webinars->map(function ($webinar) {
                return [
                    'webinar' => [
                        'webinar_id' => $webinar->id,
                        'title' => $webinar->title,
                        'date' => $webinar->date,
                        'speaker' => $webinar->speaker,
                        'place' => $webinar->place,
                        'poster' => $webinar->poster,
                        'price' => $webinar->price,
                        'description' => $webinar->description,
                    ],
                ];
            });
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to get webinar list',
                'errors' => $th->getMessage()
            ]);
        }

        return response()->json([
            'webinar' => $webinar_data
        ]);
    }

    public function webinarListDetail(Request $request, $webinar_id)
    {
        $webinar = Webinar::findOrFail($webinar_id);

        if (!$webinar) {
            return response()->json([
                "message" => "webinar not found"
            ], 404);
        }

        return response()->json([
            'title' => $webinar->title,
        ]);
    }

    public function materiWebinar($webinar_id)
    {
        $webinar = Webinar::findOrFail($webinar_id);

        if (!$webinar) {
            return response()->json([
                "message" => "webinar not found"
            ], 404);
        }

        return response()->json($webinar->materi);
    }

    public function linkWebinar($webinar_id)
    {
        $webinar = Webinar::findOrFail($webinar_id);

        if (!$webinar) {
            return response()->json([
                "message" => "webinar not found"
            ], 404);
        }

        return response()->json($webinar->link);
    }

    public function webinarParticipants(Request $request, $webinar_id)
    {
        // $participants = Participant::where('webinar_id', $webinar_id)
        //     ->with('user.biodata')
        //     ->get();

        $participants = Participant::where('webinar_id', $webinar_id)
            ->where('status', 1)
            ->get();

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

        return response()->json([
            'participant_data' => $participant_data
        ]);
    }

    public function participantList(Request $request)
    {
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

    public function participantListDetail(Request $request, $participant_id)
    {
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
            'key' => $participant->key,
        ];

        return response()->json([
            'created_at' => $participant->created_at->toDateString(),
            'name' => $participant->user->biodata->name,
            'title' => $participant->webinar->title,
            'date' => $participant->webinar->date,
            'invoice_data' => $invoice_data,
        ]);
    }

    public function validateParticipant(Request $request, $participant_id)
    {
        $participant = Participant::with('invoice')->findOrFail($participant_id);

        try {
            $invoice = $participant->invoice;
            $invoice->update(['is_checked' => 1]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to checklist the invoice',
                'errors' => $th->getMessage()
            ], 400);
        }

        $participant->update(['status' => 1]);

        return response()->json([
            'message' => 'participant verified'
        ]);
    }

    public function declineParticipant(Request $request, $participant_id)
    {
        $validator = Validator::make($request->all(), [
            'note' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $participant = Participant::with('invoice')->find($participant_id);

        try {
            $participant->update([
                'status' => 2,
                'note' => $request->note
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to deny participant',
                'errors' => $th->getMessage()
            ], 400);
        }

        return response()->json([
            'message' => 'deny participant success'
        ]);
    }
}
