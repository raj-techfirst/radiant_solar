<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AdminNotify;
use App\Mail\Registerconfirm;
use App\Models\CompanyProfile;
use App\Models\LeadMaster;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{


    public function index()
    {
        return view('auth.register');
    }
    public function create(Request $input)
    {
        $validator = Validator::make($input->all(), [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'integer'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => 'required|required_with:confirm_password|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
            'confirm_password' => 'required|same:password',
        ], [
            'password.min' => 'The password must have minimum 8 characters',
            'password.regex' => 'at least 1 uppercase letter, 1 lowercase letter, 1 special character and 1 number',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        DB::beginTransaction();
        try {
            $otp = rand(10000, 99999);
            $user = User::create([
                'name' => $input->name,
                'last_name' => $input->last_name,
                'company_name' => $input->company_name,
                'mobile' => $input->mobile,
                'email' => $input->email,
                'status' => '0',
                'password' => Hash::make($input->password),
                'otp' => $otp,
            ]);
            CompanyProfile::create(['user_id' => $user->id]);
            $role = Role::where('name', 'Owner')->first();
            $user->assignRole($role);

            Mail::to($user->email)->send(new Registerconfirm($user, 'register'));

            DB::commit();
            Session::put('register_email', $input->email);
            return redirect('/otp');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect('/register');
        }
    }

    public function otp()
    {
        $email = str_pad(substr(Session::get('register_email'), 6), strlen(Session::get('register_email')), "*", STR_PAD_LEFT);
        return view('auth.otp', compact('email'));
    }

    public function otpVerify(Request $input)
    {
        $validator = Validator::make($input->all(), [
            'otp' => ['required', 'min:5', 'max:5']
        ], [
            'otp.required' => 'OTP is invalid. Please enter correct OTP',
            'otp.min' => 'OTP is invalid. Please enter correct OTP',
            'otp.max' => 'OTP is invalid. Please enter correct OTP',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            $email = Session::get('register_email');
            $otp = $input->otp;
            $verify = User::where('otp', $otp)->where('email', $email)->first();
            if (!is_null($verify)) {
                $verify->status = "1";
                $verify->save();
                Mail::to('info@techfirst.co.in')->send(new AdminNotify($verify));

                $leadMaster = new LeadMaster();
                $leadMaster->user_id = 113;
                $leadMaster->company_profile_id = 104;
                $leadMaster->assign_id = 106;
                $leadMaster->manager_id = 105;
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

                return redirect('/techfirst/login');
            } else {
                $validator->errors()->add('otp', 'OTP is invalid. Please enter correct OTP');
                return redirect()->back()->withErrors($validator)->withInput();
            }
        } catch (\Exception $e) {
            $validator->errors()->add('otp', 'Something went wrong. Please try again');
            return redirect()->back()->withErrors($validator)->withInput();
        }
    }
    public function resendotp(Request $input)
    {
        $validator = Validator::make($input->all(), []);

        try {
            $email = Session::get('register_email');
            $otp = rand(10000, 99999);

            $verify = User::where('email', $email)->first();
            if (!is_null($verify)) {
                $verify->otp = $otp;
                $verify->save();

                Mail::to($verify->email)->send(new Registerconfirm($verify, 'register'));

                $validator->errors()->add('resendotp', 'OTP Sent Successfully.');
            } else {
                $validator->errors()->add('otp', 'Something went wrong. Please try again');
            }
            return redirect()->back()->withErrors($validator)->withInput();
        } catch (\Exception $e) {
            $validator->errors()->add('otp', 'Something went wrong. Please try again');
            return redirect()->back()->withErrors($validator)->withInput();
        }
    }
}
