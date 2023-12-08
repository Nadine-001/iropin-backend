<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Licence;
use App\Models\LicenceFormDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LicenceController extends Controller
{
    function requestLicence(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'licence_type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        DB::beginTransaction();
        try {
            $licence = Licence::create([
                'user_id' => $user->id,
                'membership_number' => $user->membership_number,
                'name' => $user->biodata->name,
                'email' => $user->email,
                'licence_type' => $request->licence_type,
                'description' => $request->description,
            ]);

            $count = 1;
            $files = [];
            if ($request->file('files')) {
                foreach ($request->file('files') as $key => $value) {
                    $key = "doc_" . $request->licence_type . "_" . $key;
                    $ext = $value->getClientOriginalExtension();
                    $file_name = time() . " - " . $value->getClientOriginalName();
                    $file_name = str_replace(' ', '', $file_name);
                    $path = asset("uploads/" . $file_name);
                    $value->move(public_path('uploads'), $file_name);

                    $file = File::create([
                        'path' => $path,
                        'ext' => $ext,
                        'file_name' => $file_name,
                    ]);

                    $files[$key] = $file;

                    $detail = LicenceFormDetail::create([
                        'licence_id' => $licence->id,
                        'key' => $key,
                        'val' => $file_name,
                        'file_id' => $file->id,
                    ]);

                    $count++;
                }
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
            'licence' => $licence,
            'files' => $files,
        ]);
    }

    function licenceList(Request $request)
    {
        $licences = Licence::all();

        $licence_data = $licences->map(function ($licence) {
            return [
                'licence' => [
                    'licence_id' => $licence->id,
                    'created_at' => $licence->created_at->toDateString(),
                    'licence_type' => $licence->licence_type,
                    'name' => $licence->name,
                ],
            ];
        });

        return response()->json([
            'licence_data' => $licence_data
        ]);
    }

    function licenceListDetail(Request $request, $licence_id)
    {
        $licence = Licence::findOrFail($licence_id);
        $file = LicenceFormDetail::where('licence_id', $licence->id)
            ->with('file')
            ->get();

        try {
            $file_data = $file->map(function ($file) {
                return [
                    'file_name' => $file->val,
                    'file_path' => $file->file->path,
                    'key' => $file->key,
                ];
            });
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to get file list',
                'errors' => $th->getMessage()
            ]);
        }

        return response()->json([
            'membership_number' => $licence->membership_number,
            'name' => $licence->name,
            'email' => $licence->email,
            'licence_type' => $licence->licence_type,
            'file_data' => $file_data,
        ]);
    }

    function validateLicence(Request $request, $licence_id)
    {
        $licence = Licence::findOrFail($licence_id);

        try {
            $checked = $request->input('checked');
            $checked_decode = json_decode($checked, true);

            foreach ($checked_decode as $fileName => $is_checked) {
                $licence_detail = LicenceFormDetail::where('licence_id', $licence->id)
                    ->whereHas('file', function ($query) use ($fileName) {
                        $query->where('file_name', $fileName);
                    })
                    ->firstOrFail();

                $file = $licence_detail->file;
                $file->update(['is_checked' => $is_checked]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to checklist the files',
                'errors' => $th->getMessage()
            ]);
        }

        $licence->update(['status' => 1]);

        return response()->json([
            'message' => 'licence verified'
        ]);
    }

    function declineLicence(Request $request, $licence_id)
    {
        $validator = Validator::make($request->all(), [
            'note' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $licence = Licence::findOrFail($licence_id);

        try {
            $checked = $request->input('checked');
            $checked_decode = json_decode($checked, true);

            foreach ($checked_decode as $fileName => $is_checked) {
                $licence_detail = LicenceFormDetail::where('licence_id', $licence->id)
                    ->whereHas('file', function ($query) use ($fileName) {
                        $query->where('file_name', $fileName);
                    })
                    ->firstOrFail();

                $file = $licence_detail->file;
                $file->update(['is_checked' => $is_checked]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to checklist the files',
                'errors' => $th->getMessage()
            ]);
        }

        $licence->update([
            'status' => 2,
            'note' => $request->note
        ]);

        return response()->json([
            'message' => 'participant denied'
        ]);
    }

    public function approvalList(Request $request)
    {
        $approvals = Licence::where('status', 1)->get();

        try {
            $approval_data = $approvals->map(function ($approval) {
                return [
                    'approvals' => [
                        'licence_id' => $approval->id,
                        'licence_type' => $approval->licence_type,
                        'name' => $approval->name,
                        'membership_number' => $approval->membership_number,
                    ],
                ];
            });

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to list the approval',
                'errors' => $th->getMessage()
            ], 404);
        }

        return response()->json([
            'approval_data' => $approval_data
        ]);
    }

    public function showApproval(Request $request, $licence_id)
    {
        $licence = Licence::findOrFail($licence_id);
        $licence_type = $licence->licence_type;

        $key = null;
        try {
            if ($licence_type == 'Pencabutan SIP-RO') {
                $key = 'sip-ro';
            } else if ($licence_type == 'Perpanjangan STR') {
                $key = 'str';
            } else if ($licence_type == 'Rekomendasi BPJS') {
                $key = 'bpjs';
            } else if ($licence_type == 'Pengajuan SIPO') {
                $key = 'sipo';
            } else if ($licence_type == 'Permohonan Pindah Cabang') {
                $key = 'pindah';
            } else if ($licence_type == 'Pengajuan SIP') {
                $key = 'sip';
            }

            $file_id = LicenceFormDetail::where('licence_id', $licence->id)
                ->where('key', $key)
                ->value('file_id');

            $file = File::findOrFail($file_id);
            $file_path = $file->path;
            
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to get the file',
                'errors' => $th->getMessage()
            ], 404);
        }

        return response()->json([
            'file_path' => $file_path,
        ]);
    }
}
