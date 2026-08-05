<?php

namespace App\Http\Controllers;

use App\Mail\MailOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ForgetPasswordController extends Controller
{
    public function forget()
    {
        return view('auth.forget');
    }

    public function forgetNow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ], ['email.required' => 'Please input email or mobile']);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        try {
            $email = $request->email;
            $user = User::where('email', $email)->orwhere('mobile', $email)->first();
            if (!is_null($user)) {
                // $user->otp = mt_rand(100000, 999999);
                $user->otp = rand(10000, 99999);
                $user->save();
                $id = $user->id;
                Mail::to($user->email)->send(new MailOtp($user, 'forget'));
                $request->session()->put('forget_key', $id);
                $request->session()->put('forget_email', $request->email);
                $response = ['data' => route('verify'), 'status' => true, 'message' => 'OTP Send via given email/mobile.'];
            } else {
                $response = ['status' => false, 'message' => 'Credential record do not match our records please correct email/mobile input.'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function verifySearch()
    {
        if (!empty(session()->get('forget_key'))) {
            return view('auth.verify');
        } else {
            return redirect('login');
        }
    }

    public function otpVerify(Request $request)
    {
        // dd($request->otp);
        // $user = User::where('otp', $request->otp)->first();
        // dd($user);
        if (strlen($request->otp) == 5) {
            // $user = User::where('id', session()->get('forget_key'))->where('otp', $request->otp)->first();
            $user = User::where('otp', $request->otp)->first();
            // dd($user);
            if (!is_null($user)) {
                $user->otp = 0;
                $user->save();
                $request->session()->put('forgetPassId', session()->get('forget_key'));
                $request->session()->put('forgetPassEmail', session()->get('forget_email'));
                $response = ['data' => route('reset-password'), 'status' => true, 'message' => 'OTP verified successfully.'];
            } else {
                $response = ['status' => false, 'message' => 'Entered OTP does not match.'];
            }
            return response()->json($response);
        } else {
            $response = ['status' => false, 'message' => 'Please enter correct OTP.', 'errors' => 'validation error.'];
            return response()->json($response);
        }
    }

    public function resetPassword()
    {
        if (!is_null(session()->get('forgetPassId'))) {
            return view('auth.reset');
        } else {
            return redirect('login');
        }
    }

    public function confirmPassword(Request $request)
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
            return response()->json($response);
        }
        try {
            $user = User::where('id', session()->get('forgetPassId'))->first();
            if (!is_null($user)) {
                $user->password = Hash::make($request->password);
                $user->save();
                session()->forget('forget_key');
                session()->forget('forgetPassId');
                $response = ['data' => route('login'), 'status' => true, 'message' => 'Your Password has been updated successfully.'];
            } else {
                $response = ['status' => false, 'message' => 'Credential something wrong.'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
