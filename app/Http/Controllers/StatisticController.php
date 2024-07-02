<?php

namespace App\Http\Controllers;

use App\Models\Licence;
use App\Models\User;
use App\Models\Office;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StatisticController extends Controller
{
    public function getMemberStatistic() {
        $users = User::where('role_id', '<', 50)->get();

        $member_total = 0;
        $active_member = 0;
        $non_active_member = 0;

        foreach ($users as $user) {
            if ($user->registration->status != 0 && $user->registration->status != 2) {
                $member_total++;

                if ($user->registration->status == 1) {
                    $active_member++;
                } else if ($user->registration->status == 3) {
                    $non_active_member++;
                }
            }
        }

        return response()->json([
            'member_total' => $member_total,
            'active_member' => $active_member,
            'non_active_member' => $non_active_member,
        ]);
    }

    public function getVerificationStatistic() {
        $registration_total = Registration::all()->count();
        $checked_registration = Registration::where('status', '!=', 0)->get()->count();

        $licence_total = Licence::all()->count();
        $checked_licences = Licence::where('status', '!=', 0)->get()->count();

        $participant_total = Participant::all()->count();
        $checked_participant = Participant::where('status', '!=', 0)->get()->count();

        return response()->json([
            'registration_total' => $registration_total,
            'checked_registration' => $checked_registration,
            'licence_total' => $licence_total,
            'checked_licences' => $checked_licences,
            'participant_total' => $participant_total,
            'checked_participant' => $checked_participant,
        ]);
    }

    public function getEmployeeStatistic() {
        $employee_total = Office::all()->count();
        $employees = Office::groupBy('office_regency_city')
            ->select('office_regency_city', DB::raw('count(*) as total_employee'))
            ->get();

        return response()->json([
            'employee_total' => $employee_total,
            'employees' => $employees,
        ]);
    }
}
