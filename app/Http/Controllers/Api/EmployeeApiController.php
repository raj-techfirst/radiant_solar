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

class EmployeeApiController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->roles[0]->name == 'Owner') {
            $company = CompanyProfile::where('user_id', Auth::id())->first();
            $companyProfile = CompanyProfile::select('id', 'user_id', 'parent_id', 'manager_id', 'user_type', 'address', 'state_id', 'city_id')
                ->where('parent_id', $company->id)
                ->with('owner', 'manager', 'user', 'state', 'city')
                ->orderBy('id', 'DESC')
                ->get();
            foreach ($companyProfile as $value) {
                $value['owner_name'] = $value->owner->user->name . ' ' . $value->owner->user->last_name;
                $value['manager_name'] = ($value->manager_id != 0) ? $value->manager->user->name  . ' ' . $value->manager->user->last_name : "";
                $value['name'] = $value->user->name;
                $value['last_name'] = $value->user->last_name;
                $value['mobile'] = $value->user->mobile;
                $value['email'] = $value->user->email;
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
                unset($value->parent_id, $value->manager_id, $value->owner, $value->manager, $value->state_id, $value->city_id, $value->state, $value->city, $value->user);
            }
            $response = ['status' => true, 'message' => 'Employee List', 'employee' => $companyProfile];
            return response($response, 200);
        } else {
            $response = ['status' => false, 'message' => 'You are not authorized'];
            return response($response, 401);
        }
    }

    public function store(Request $request)
    {
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        $check = CompanyProfile::where('user_type', $request->user_type)->where('parent_id', $company->id)->count();
        if ($request->user_type == 'M') {
            $limitField = 'manager_limit';
            $limitLebel = 'manager';
        } else {
            $limitField = 'sales_limit';
            $limitLebel = 'sales';
        }
        if ($check < Auth::user()->$limitField || $request->employee_id) {
            if (!is_null($request->employee_id)) {
                $temp = 'nullable';
                $password = 'nullable|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/';
                $confirm_password = 'nullable|same:password';
            } else {
                $temp = 'required|unique:users,email';
                $password = 'required_with:confirm_password|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/';
                $confirm_password = 'required|same:password';
            }
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'last_name' => 'required',
                'email' => $temp,
                'district_id' => 'required',
                'taluka_id' => 'required',
                'password' => $password,
                'confirm_password' => $confirm_password,
            ], [
                'name.required' => 'Enter first name',
                'last_name.required' => 'Enter last name',
                'email.required' => 'Enter email',
                'district_id.required' => 'Select state',
                'taluka_id.required' => 'Select taluka',
                'password.min' => 'The password must have minimum 8 characters',
                'password.regex' => 'at least 1 uppercase letter, 1 lowercase letter, 1 special character and 1 number',
            ]);
            if ($validator->fails()) {
                $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
                return response()->json($response);
            }
            DB::beginTransaction();
            try {
                if (!is_null($request->employee_id)) {
                    $companyProfile = CompanyProfile::where('id', $request->employee_id)->first();
                    $user = User::where('id', $companyProfile->user_id)->first();
                    $response = ['data' => route('employee.index'), 'status' => true, 'message' => 'User updated successfully.'];
                } else {
                    $user = new User();
                    $companyProfile = new CompanyProfile();
                    $response = ['data' => route('employee.index'), 'status' => true, 'message' => 'User added successfully.'];
                }
                $user->name = $request->name;
                $user->last_name = $request->last_name;
                if (!$request->employee_id) {
                    $user->email = $request->email;
                }
                $user->mobile = $request->mobile;
                if ($request->password) {
                    $user->password = Hash::make($request->password);
                }
                $user->status = '1';
                $result = $user->save();

                if (!is_null($result)) {

                    $user->removeRole($user->roles);

                    if ($request->user_type == 'M') {
                        $role = Role::where('name', 'Manager')->first();
                    } elseif ($request->user_type == 'S') {
                        $role = Role::where('name', 'Sales')->first();
                    } else {
                        $role = Role::where('name', $request->role)->first();
                    }
                    $user->assignRole($role);

                    if (!$request->employee_id) {

                        $company = CompanyProfile::where('user_id', Auth::id())->first();
                        $companyProfile->user_id = $user->id;
                        $companyProfile->parent_id = $company->id;
                        $companyProfile->user_type = $request->user_type;
                    }
                    $companyProfile->manager_id = (!empty($request->manager_id)) ? $request->manager_id : 0;
                    $companyProfile->state_id = $request->district_id;
                    $companyProfile->city_id = $request->taluka_id;
                    $companyProfile->address = $request->address;
                    $companyProfile->save();

                    DB::commit();
                    return response()->json($response);
                } else {
                    $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                    return response()->json($response);
                }
            } catch (\Exception $e) {
                DB::rollback();
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } else {
            $response = ['status' => false, 'label' => $limitLebel, 'message' => 'Your ' . $limitLebel . ' limit`s has been ended.'];
            return response()->json($response);
        }
    }

    public function show(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        if (!is_null($user)) {
            $user->status = $request->status;
            $user->save();
            $response = ['status' => true, 'message' => 'Status updated successfully.'];
            return response($response, 200);
        } else {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }

    public function destroy(Request $request)
    {
        if (Auth::user()->roles[0]->name == 'Owner') {
            $id = $request->id;
            $companyProfile = CompanyProfile::where('id', $id)->first();
            if (!is_null($companyProfile)) {
                $leadMaster = LeadMaster::where('assign_id', $id)->count();
                $estimate = Estimate::where('assign_id', $id)->count();
                $task = Task::where('assign_id', $id)->count();
                $followUp = FollowUp::where('follow_up_by', $id)->count();
                if ($leadMaster <= 0 && $estimate <= 0 && $task <= 0 && $followUp <= 0) {
                    $user = User::where('id', $companyProfile->user_id)->delete();
                    $response = ['status' => true, 'message' => 'Employee deleted successfully.'];
                } else {
                    $response = ['status' => false, 'message' => 'Also used this user.'];
                }
                return response($response, 200);
            } else {
                $response = ['status' => false, 'message' => 'Employee not found.'];
                return response($response, 200);
            }
        } else {
            $response = ['status' => false, 'message' => 'You are not authorized.'];
            return response($response, 401);
        }
    }

    public function employeeDropDown(Request $request)
    {
        $users = User::select('id', 'name', 'email')
            ->orderBy('id', 'DESC')
            ->get();

        $data = [];
        if ($users->count() > 0) {
            foreach ($users as $key => $value) :
                if ($value->roles[0]->name != 'Super Admin') {
                    $value->role = $value->roles[0]->name;
                    unset($value->roles);
                    $data[] = $value;
                }
            endforeach;
        }

        $response = ['status' => true, 'message' => 'Employee List', 'employee' => $data];
        return response($response, 200);
    }
}
