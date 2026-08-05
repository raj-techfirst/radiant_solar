<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\MailOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ForgetPasswordApiController extends Controller
{
    public function apiForget(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ], ['email.required' => 'Please input email or mobile']);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response($response);
        }

        try {
            $email = $request->email;
            $user = User::where('email', $email)->orwhere('mobile', $email)->first();
            if (!is_null($user)) {
                // $user->otp = mt_rand(100000, 999999);
                $user->otp = 123456;
                $user->save();
                $email = $user->email;
                Mail::to($user->email)->send(new MailOtp($user, 'forget'));
                $response = [ 'status' => true, 'message' => 'OTP Send via given email/mobile.', 'email'=> $email ];
            } else {
                $response = ['status' => false, 'message' => 'Credential record do not match our records please correct email/mobile input.'];
            }
            return response($response);
        } catch (\Exception $e) {
            
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response($response);
        }
    }
    

    public function apiOtpVerify(Request $request)
    {
        if (strlen($request->otp) == 6) {
            $user = User::where('email', $request->email)->where('otp', $request->otp)->first();
            if (!is_null($user)) {
                $user->otp = 0;
                $user->save();
                $response = ['status' => true, 'message' => 'OTP verified successfully.'];
            } else {
                $response = ['status' => false, 'message' => 'Entered OTP does not match.'];
            }
            return response($response);
        } else {
            $response = ['status' => false, 'message' => 'Please enter correct OTP.', 'errors' => 'validation error.'];
            return response($response);
        }
    }

    public function apiConfirmPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required_with:confirm_password|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
            'confirm_password' => 'required|same:password'
        ], [
            'password.min' => 'The password must have minimum 8 characters',
            'password.regex' => 'at least 1 uppercase letter, 1 lowercase letter, 1 special character and 1 number',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response($response);
        }
        try {
            $user = User::where('email', $request->email)->first();
            if (!is_null($user)) {
                $user->password = Hash::make($request->password);
                $user->save();
                $response = ['status' => true, 'message' => 'Your Password has been updated successfully.'];
            } else {
                $response = ['status' => false, 'message' => 'Credential something wrong.'];
            }
            return response($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response($response);
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

    public function destroy($id)
    {
        //
    }
}
