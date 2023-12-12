<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Licence;
use App\Models\LicenceFormDetail;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Builder;
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
                    'status' => $licence->status,
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
        $files = LicenceFormDetail::where('licence_id', $licence->id)
            ->with('file')
            ->get();

        try {
            $file_data = $files->map(function ($file) {
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
        $key = $request->key;
        try {
            LicenceFormDetail::where('licence_id', $licence_id)
                ->where('key', $key)
                ->update(['is_forward_manager' => 1]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to get the key file for manager',
                'errors' => $th->getMessage()
            ], 400);
        }

        $licence = Licence::findOrFail($licence_id);

        try {
            File::whereHas('licence_form_detail', function ($query) use ($licence) {
                $query->where('licence_id', $licence->id);
            })
                ->update(['is_checked' => 1]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to checklist the files',
                'errors' => $th->getMessage()
            ], 400);
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
            $licence->update([
                'status' => 2,
                'note' => $request->note
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to deny licence',
                'errors' => $th->getMessage()
            ], 400);
        }

        return response()->json([
            'message' => 'deny licence success'
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
        $file_id = LicenceFormDetail::where('licence_id', $licence_id)
            ->where('is_forward_manager', 1)
            ->value('file_id');

        try {
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

    public function sendApproval(Request $request, $licence_id)
    {
        $validator = Validator::make($request->all(), [
            'licence' => 'required|file',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $file_id = LicenceFormDetail::where('licence_id', $licence_id)
            ->where('is_forward_manager', 1)
            ->value('file_id');

        try {
            if ($request->file('licence')) {
                $licence = $request->file('licence');

                $file_name = time() . " - " . $licence->getClientOriginalName();
                $file_name = str_replace(' ', '', $file_name);
                $path_licence = asset("uploads/Approval/" . $file_name);
                $licence->move(public_path('uploads/Approval/'), $file_name);
            }

            File::findOrFail($file_id)->update([
                'path' => $path_licence,
                'is_assigned' => 1,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to get the file',
                'errors' => $th->getMessage()
            ], 404);
        }

        return response()->json([
            'message' => 'success uploaded file',
        ]);
    }

    public function licenceApproved() {
        $user = Auth::user();

        $licences = Licence::select('id', 'licence_type')
        ->where('user_id', $user->id)
        // ->with(['licence_form_detail.file' => function ($query) {
        //         $query->where('is_assigned', 1);
        // }])
        ->whereHas('licence_form_detail.file', function ($query) {
                $query->where('is_assigned', 1);
        })
        ->get();

        $licence_data = $licences->map(function ($licence) {
            return [
                'licence_id' => $licence->id,
                'licence_type' => $licence->licence_type,
            ];
        });

        return response()->json($licence_data);
    }
}
