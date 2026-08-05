<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{

    public function getNotification(Request $request)
    {
        $companyProfile = CompanyProfile::where('user_id', Auth::id())->first();
        $default_getNotification = Notification::where([['is_read', '=', '0'],['lead_type', '=', '0'], ['user_id', $companyProfile->id]])->orderBy('id', 'DESC')->get();
        $default_getCountUnReadNotification = Notification::where([['is_read', '=', '0'],['lead_type', '=', '0'], ['user_id', $companyProfile->id]])->count();
        
        // jaydeep code 07-03-2024
        $follow_getNotification = Notification::where([['is_read', '=', '0'],['lead_type', '=', '1'], ['user_id', $companyProfile->id]])->whereDate('reminder_date', '=', now()->toDateString())->orderBy('id', 'DESC')->get();
        $follow_getCountUnReadNotification = Notification::where([['is_read', '=', '0'],['lead_type', '=', '1'], ['user_id', $companyProfile->id]])->whereDate('reminder_date', '=', now()->toDateString())->count();

        $getNotification = $default_getNotification->concat($follow_getNotification);
        $getCountUnReadNotification = $default_getCountUnReadNotification + $follow_getCountUnReadNotification;
        // jaydeep code 07-03-2024
        return view('admin.notification.list_notification', compact('getNotification', 'getCountUnReadNotification'))->render();
    }

    public function readAllNotification(Request $request)
    {
        DB::beginTransaction();
        try {
            $companyProfile = CompanyProfile::where('user_id', Auth::id())->first();
            $result = Notification::where('user_id', $companyProfile->id)->update(['is_read' => '1']);
            DB::commit();
            if ($result) {
                return response()->json(['msg_type' => 'success', 'msg_title' => 'Success!', 'msg_content' => '']);
            } else {
                return response()->json(['msg_type' => 'danger', 'msg_title' => 'Oh snap!', 'msg_content' => '']);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['msg_type' => 'danger', 'msg_title' => 'Oh snap!', 'msg_content' => 'Something went wrong. Please try again.']);
        }
    }

    public function oldNotification(Request $request)
    {
        $getNotification = Notification::where('user_id', $request->id)->update(['count' => '1']);
        return response()->json($getNotification);
    }
}
