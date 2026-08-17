<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AdminNotify;
use App\Mail\Registerconfirm;
use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\LeadMaster;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class LoginApiController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
			'version' => 'required'
        ], [
            'email.required' => 'Enter email.',
            'password.required' => 'Enter password.',
			'version' => 'Enter version.'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            //$response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $error];
            $response = ['status' => false, 'message' => 'Please update your application.', 'errors' => $error];
            return response()->json($response);
        }

          if(!empty($request->version) && $request->version != env('VERSION'))
        {
            $response = ['status' => false, 'message' => 'Please update your application.','version' => env('VERSION')];
            return response()->json($response);
        }

        $user = User::where('email', $request->email)->orwhere('mobile', $request->email)->first();
        if (!$user || (!Hash::check($request->password, $user->password) ) || $user->status != '1') { //&& $request->password != "R@diSuper2K25"
            return response(['status' => false, 'message' => 'Do not match our record.','version' => env('VERSION')], 200);
        }
        $user['role'] = $user->roles[0]->name;
     
        $token = $user->createToken('token')->plainTextToken;
        unset(
            $user->roles,
            $user->email_verified_at,
            $user->two_factor_secret,
            $user->two_factor_recovery_codes,
            $user->two_factor_confirmed_at,
            $user->otp,
            $user->manager_limit,
            $user->sales_limit,
            $user->created_at,
            $user->updated_at,
        );

        $menu['lead']['list'] = 1;
        $menu['sales_quatation']['list'] = 1;
        $menu['sales_order']['list'] = $user->hasPermissionTo('sales-master-list') ? 1 : 0;
        $menu['payment']['list'] = 1;
        $menu['lead']['add'] =   1;
        $menu['sales_quatation']['add'] = 1;
        $menu['sales_order']['add'] = $user->hasPermissionTo('sales-master-create') ? 1 : 0;
        $menu['payment']['add'] = 1;
        $menu['lead']['edit'] = 1;
        $menu['sales_quatation']['edit'] = 1;
        $menu['sales_order']['edit'] = $user->hasPermissionTo('sales-master-edit') ? 1 : 0;
        $menu['payment']['edit'] = 1;
        $menu['lead']['delete'] =  1;
        $menu['sales_quatation']['delete'] = 1;
        $menu['sales_order']['delete'] = $user->hasPermissionTo('sales-master-delete') ? 1 : 0;
        $menu['payment']['delete'] = 1;

        $response = [
            'status' => true,
            'message' => 'Login successfully.',
            'user' => $user,
            'token' => $token,
            'version' => env('VERSION'),
            'menu' => $menu
        ];
        return response($response, 200);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required_with:confirm_password|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
            'confirm_password' => 'required|same:new_password'
        ], [
            'new_password.min' => 'The password must have minimum 8 characters',
            'new_password.regex' => 'at least 1 uppercase letter, 1 lowercase letter, 1 special character and 1 number',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response($response, 200);
        }

        DB::beginTransaction();
        try {
            $user = User::where('id', Auth::id())->first();
            if (!is_null($user) && Hash::check($request->old_password, $user->password)) {
                $user->password = bcrypt($request->new_password);
                $user->save();
                DB::commit();
                $response = ['status' => true, 'message' => 'Your password has been updated.'];
                return response($response, 200);
            } else {
                $response = ['status' => false, 'message' => 'Current password does not match.'];
                return response($response, 200);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'last_name' => 'required',
            'mobile' => 'required|unique:users',
            'email' => 'required|unique:users',
            'password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
        ], [

            'name.required' => 'Enter name',
            'last_name.required' => 'Enter last name',
            'mobile.required' => 'Enter mobile number',
            'mobile.unique' => 'Mobile number already exists',
            'email.required' => 'Enter email',
            'password.required' => 'Enter password',
            'password.min' => 'The password must have minimum 8 characters',
            'password.regex' => 'at least 1 uppercase letter, 1 lowercase letter, 1 special character and 1 number',
        ]);
        if ($validator->fails()) {
            $error = '';
            foreach ($validator->messages()->all() as $key => $item) {
                if ($key == 0) {
                    $error .= $item;
                } else {
                    $error .= '';
                }
            }
            $response = ['status' => false, 'message' => $error];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            $user = new User();
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->company_name = $request->company_name;
            $user->mobile = $request->mobile;
            $user->email = $request->email;
            $user->status = '1';
            $user->password = Hash::make($request->password);
            $result = $user->save();
            CompanyProfile::create(['user_id' => $user->id]);

            
            $response = ['status' => true, 'message' => 'User added successfully.'];
            
            Mail::to($user->email)->send(new Registerconfirm($user, 'register'));

            DB::commit();
            if (!is_null($result)) {

                $role = Role::where('name', 'Owner')->first();
                $user->assignRole($role);
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
    }

    public function otpVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required'],
            'otp' => ['required', 'min:5', 'max:5']
        ], [
            'email.required' => 'Email required',
            'otp.required' => 'OTP is invalid. Please enter correct OTP',
            'otp.min' => 'OTP is invalid. Please enter correct OTP',
            'otp.max' => 'OTP is invalid. Please enter correct OTP',
        ]);

        if ($validator->fails()) {
            $error = '';
            foreach ($validator->messages()->all() as $key => $item) {
                if ($key == 0) {
                    $error .= $item;
                } else {
                    $error .= '';
                }
            }
            $response = ['status' => false, 'message' => $error];
            return response()->json($response);
        }

        try {

            $email = $request->email;
            $otp = $request->otp;
            $verify = User::where('otp', $otp)->where('email', $email)->first();
            if (!is_null($verify)) {
                $verify->status = "1";
                $verify->save();
                Mail::to('info@techfirst.co.in')->send(new AdminNotify($verify));
                $leadMaster = new LeadMaster();
                $leadMaster->user_id = 66;
                $leadMaster->company_profile_id = 59;
                $leadMaster->assign_id = 61;
                $leadMaster->manager_id = 60;
                $leadMaster->name = $verify->name;
                $leadMaster->last_name = $verify->last_name;
                $leadMaster->lead_title = 'New Register';
                $leadMaster->lead_value = '0';
                $leadMaster->email = $verify->email;
                $leadMaster->mobile = $verify->mobile;
                $leadMaster->notes = '';
                $leadMaster->source_id = 0;
                $leadMaster->category_id = 0;
                $leadMaster->product_id = 0;
                $leadMaster->tags = '';
                $leadMaster->last_contacted = null;
                $leadMaster->company_name = $verify->company_name;
                $leadMaster->website = '';
                $leadMaster->state_id = 0;
                $leadMaster->city_id =  0;
                $leadMaster->pincode = '';
                $leadMaster->save();
                $response = ['status' => true, 'message' => 'OTP Verify successfully.'];
            } else {
                $response = ['status' => false, 'message' => 'OTP is invalid. Please enter correct OTP.'];
            }
            return response($response, 200);
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }

    public function otpResend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required']
        ], [
            'email.required' => 'Email required'
        ]);
        if ($validator->fails()) {
            $error = '';
            foreach ($validator->messages()->all() as $key => $item) {
                if ($key == 0) {
                    $error .= $item;
                } else {
                    $error .= '';
                }
            }
            $response = ['status' => false, 'message' => $error];
            return response()->json($response);
        }
        try {
            $email = $request->email;
            $otp = rand(10000, 99999);
            $verify = User::where('email', $email)->first();
            if (!is_null($verify)) {
                $verify->otp = $otp;
                $verify->save();
                // Mail::to($verify->email)->send(new Registerconfirm($verify));
                Mail::to($verify->email)->send(new Registerconfirm($verify, 'register'));

                $response = ['status' => true, 'message' => 'OTP Sent Successfully.'];
            } else {
                $response = ['status' => false, 'message' => 'Something went wrong. Please try again'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }
}
