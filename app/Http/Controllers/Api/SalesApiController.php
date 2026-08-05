<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\Estimate;
use App\Models\FollowUp;
use App\Models\LeadMaster;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class SalesApiController extends Controller
{
    public function index()
    {
        if (Auth::user()->roles[0]->name == 'Manager') {
            $company = CompanyProfile::where('user_id', Auth::id())->first();
            $companyProfile = CompanyProfile::select('id', 'user_id', 'parent_id', 'address', 'state_id', 'city_id')
                ->where('manager_id', $company->id)
                ->where('parent_id', $company->parent_id)
                ->with('user', 'state', 'city')
                ->orderBy('id','DESC')
                ->get();
            foreach ($companyProfile as $value) {
                $value['name'] = $value->user->name;
                $value['last_name'] = $value->user->last_name;
                $value['email'] = $value->user->email;
                $value['mobile'] = $value->user->mobile;
                $value['state_name'] = $value->state->state_name;
                $value['city_name'] = $value->city->city_name;
                $value['status'] = $value->user->status;
                if ($value->user->status == '0') {
                    $value['status_title'] = "Pending";
                } elseif ($value->user->status == '1') {
                    $value['status_title'] = "Approve";
                } elseif ($value->user->status == '2') {
                    $value['status_title'] = "Block";
                } else {
                    $value['status_title'] = "Reject";
                }
                unset($value->user, $value->state, $value->city, $value->state_id, $value->city_id, $value->parent_id);
            }
            $response = ['status' => true, 'message' => 'Sales List.', 'Sales' => $companyProfile];
            return response($response, 200);
        } else {
            $response = ['status' => false, 'message' => 'You are not authorized.'];
            return response($response, 401);
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        if (Auth::user()->roles[0]->name == 'Manager') {
            $company = CompanyProfile::where('user_id', Auth::id())->first();
            $companyFind = CompanyProfile::where('id', $company->parent_id)->first();
            $user = User::where('id', $companyFind->user_id)->first();
            $check = CompanyProfile::where('user_type', 'S')->where('parent_id', $company->parent_id)->count();

            $limitField = 'sales_limit';
            $limitLebel = 'sales';
            if ($check <= $user->$limitField || $request->sales_id) {
                if (!is_null($request->sales_id)) {
                    $temp = 'nullable';
                    $password = 'nullable|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/';
                    $confirm_password = 'nullable|same:password';
                } else {
                    $temp = 'required|unique:users,email';
                    $password = 'nullable|min:8';
                    // $password = 'required_with:confirm_password|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/';
                    $confirm_password = 'required|same:password';
                }
                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'last_name' => 'required',
                    'email' => $temp,
                    'state_id' => 'required',
                    'city_id' => 'required',
                    'password' => $password,
                    'confirm_password' => $confirm_password,
                ], [
                    'name.required' => 'Enter first name',
                    'last_name.required' => 'Enter last name',
                    'email.required' => 'Enter email',
                    'state_id.required' => 'Select state',
                    'city_id.required' => 'Select city',
                    'password.min' => 'The password must have minimum 8 characters',
                    'password.regex' => 'at least 1 uppercase letter, 1 lowercase letter, 1 special character and 1 number',
                ]);
                if ($validator->fails()) {
                    $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
                    return response()->json($response);
                }
                DB::beginTransaction();
                try {
                    if (!is_null($request->sales_id)) {
                        $companyProfile = CompanyProfile::where('id', $request->sales_id)->first();
                        $user = User::where('id', $companyProfile->user_id)->first();
                        $response = ['status' => true, 'message' => 'Sales updated successfully.'];
                    } else {
                        $user = new User();
                        $companyProfile = new CompanyProfile();
                        $response = ['status' => true, 'message' => 'Sales added successfully.'];
                    }
                    $user->name = $request->name;
                    $user->last_name = $request->last_name;
                    if (!$request->sales_id) {
                        $user->email = $request->email;
                    }
                    $user->mobile = $request->mobile;
                    if ($request->password) {
                        $user->password = Hash::make($request->password);
                    }
                    $user->status = '1';
                    $result = $user->save();
                    DB::commit();
                    if (!is_null($result)) {
                        if (!$request->sales_id) {
                            $role = Role::where('name', 'Sales')->first();
                            $user->assignRole($role);
                            $company = CompanyProfile::where('user_id', Auth::id())->first();
                            $companyProfile->user_id = $user->id;
                            $companyProfile->parent_id = $company->parent_id;
                            $companyProfile->user_type = 'S';
                        }
                        $companyProfile->manager_id = $company->id;
                        $companyProfile->state_id = $request->state_id;
                        $companyProfile->city_id = $request->city_id;
                        $companyProfile->address = $request->address;
                        $companyProfile->save();
                        return response($response, 200);
                    } else {
                        $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
                        return response($response, 200);
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
                    return response($response, 500);
                }
            } else {
                $response = ['status' => false, 'message' => 'Your ' . $limitLebel . ' limit`s has been ended.'];
                return response($response, 200);
            }
        } else {
            $response = ['status' => false, 'message' => 'You are not authorized'];
            return response($response, 401);
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

    public function destroy(Request $request)
    {
        if (Auth::user()->roles[0]->name == 'Manager') {
            $id = $request->id;
            try {
                $companyProfile = CompanyProfile::where('id', $id)->first();
                $leadMaster = LeadMaster::where('assign_id', $id)->count();
                $estimate = Estimate::where('assign_id', $id)->count();
                $task = Task::where('assign_id', $id)->count();
                $followUp = FollowUp::where('follow_up_by', $id)->count();
                if ($leadMaster <= 0 && $estimate <= 0 && $task <= 0 && $followUp <= 0) {
                    $user = User::where('id', $companyProfile->user_id)->delete();
                    $response = ['status' => true, 'message' => 'Sales deleted successfully.'];
                } else {
                    $response = ['status' => false, 'message' => 'Also used this user.'];
                }
                return response()->json($response);
            } catch (\Exception $e) {
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } else {
            $response = ['status' => false, 'message' => 'You are not authorized.'];
            return response($response, 401);
        }
    }
}
