<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\Estimate;
use App\Models\LeadMaster;
use App\Models\Product;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CompanyApiController extends Controller
{
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        if (Auth::user()->roles[0]->name == 'Owner') {
            $temp = 'required';
        } else {
            $temp = 'nullable';
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'last_name' => 'required',
            'company_name' => $temp,
            'state_id' => 'required',
            'city_id' => 'required',
        ], [
            'name.required' => 'Enter first name',
            'last_name.required' => 'Enter last name',
            'company_name.required' => 'Enter company name',
            'state_id.required' => 'Select state',
            'city_id.required' => 'Select city',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            $user = User::where('id', Auth::id())->first();
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->company_name = $request->company_name;
            $user->mobile = $request->mobile;
            $result = $user->save();
            DB::commit();
            if (!is_null($result)) {
                $companyProfile = CompanyProfile::where('user_id', $user->id)->first();
                $companyProfile->state_id = $request->state_id;
                $companyProfile->city_id = $request->city_id;
                $companyProfile->address = $request->address;
                $companyProfile->business_name = $request->business_name;
                $companyProfile->save();
                $response = ['status' => true, 'message' => 'Profile updated successfully.'];
            } else {
                $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            }
            return response($response, 200);
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy()
    {
        try {
            if (Auth::id() != 113 && Auth::id() != 1 && Auth::id() != 114 && Auth::id() != 115) {
                $user = User::where('id', Auth::id())->update(['status' => '2']);
                $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
                $id = $companyFind->id;
                if ($companyFind->user_type == 'O') {
                    $leadMaster = LeadMaster::where('company_profile_id', $id)->delete();
                    $task = Task::where('company_profile_id', $id)->delete();
                    $estimate = Estimate::where('company_profile_id', $id)->delete();
                    $companyProfile = CompanyProfile::where('parent_id', $id)->get();
                    foreach ($companyProfile as $item) {
                        $userFind = User::where('id', $item->user_id)->update(['status' => '2']);
                        $item->delete();
                    }
                    $category = Category::where('company_profile_id', $id)->delete();
                    $product = Product::where('company_profile_id', $id)->delete();
                } else if ($companyFind->user_type == 'M') {
                    $leadMaster = LeadMaster::where('manager_id', $id)->delete();
                    $task = Task::where('manager_id', $id)->delete();
                    $estimate = Estimate::where('manager_id', $id)->delete();
                    $companyProfile = CompanyProfile::where('manager_id', $id)->get();
                    foreach ($companyProfile as $item) {
                        $userFind = User::where('id', $item->user_id)->update(['status' => '2']);
                        $item->delete();
                    }
                } else {
                    $leadMaster = LeadMaster::where('assign_id', $id)->delete();
                    $task = Task::where('assign_id', $id)->delete();
                    $estimate = Estimate::where('assign_id', $id)->delete();
                }
                $companyFind->delete();
                $response = ['status' => true, 'message' => 'Account has been deleted'];
                return response($response, 200);
            } else {
                $response = ['status' => false, 'message' => 'You can`t delete administrator account.'];
                return response($response, 200);
            }
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function getdatetime()
    {
        try {
			$data = ['date' => date('d-m-Y'),'time' => date('h:i:s A')];
			$response = ['status' => true, 'message' => 'Date and time get successfully','result' => $data];
                return response($response, 200);
		} catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
