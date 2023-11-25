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

            $files = [];
            if ($request->file('files')){
                foreach($request->file('files') as $key => $value)
                {
                    $ext = $value->getClientOriginalExtension();
                    $file_name = time() . " - " . $value->getClientOriginalName();
                    $file_name = str_replace(' ', '', $file_name);
                    $path = asset("uploads/".$file_name);
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

        $file_data = $file->map(function ($file) {
            return [
                'file_name' => $file->val,
                'file_path' => $file->file->path,
            ];
        });

        return response()->json([
            'membership_number' => $licence->membership_number,
            'name' => $licence->name,
            'email' => $licence->email,
            'licence_type' => $licence->licence_type,
            'file_data' => $file_data,
        ]);
    }

    function verifyLicence(Request $request, $licence_id) {
        $licence = Licence::findOrFail($licence_id);

        
    }
}
