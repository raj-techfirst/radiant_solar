<?php

namespace App\Actions\Fortify;

use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Fortify;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;
    private $guard;

    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    public function create(array $input)
    {
        Validator::make($input, [
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
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'last_name' => $input['last_name'],
            'company_name' => $input['company_name'],
            'mobile' => $input['mobile'],
            'email' => $input['email'],
            'status' => '1',
            'password' => Hash::make($input['password']),
            'otp' => 0,
            // 'otp' => '654321',
        ]);
        CompanyProfile::create(['user_id' => $user->id]);
        $role = Role::where('name', 'Owner')->first();
        $user->assignRole($role);
        return $user;

        // if ($user->otp != 0) {
        // } else {
            
        //     $this->guard->logout(); // logs out the session
            
        //     return redirect('verify');

        // }

        // $credentials = $request->only('email', 'password');
        // if (Auth::attempt($credentials)) {
        //     if (Auth::user()->status == "0") {
        //         Auth::logout();
        //         return redirect()->back()->withInput()->withErrors(['email' => 'Your Account Pending Please Contact To Administrator.']);
        //     } elseif (Auth::user()->status == "2") {
        //         Auth::logout();
        //         return redirect()->back()->withInput()->withErrors(['email' => 'Your Account Deactivate Please Contact To Administrator.']);
        //     } elseif (Auth::user()->status == "3") {
        //         Auth::logout();
        //         return redirect()->back()->withInput()->withErrors(['email' => 'Your Account Reject Please Contact To Administrator.']);
        //     } elseif (Auth::user()->status == "1") {
        //         if (Auth::user()->getRoleNames()->first() == 'Super Admin') {
        //             return redirect()->route('super_home');
        //         } elseif (Auth::user()->getRoleNames()->first() == 'Admin') {
        //             return redirect()->route('admin_home');
        //         } elseif (Auth::user()->getRoleNames()->first() == 'Agent') {
        //             return redirect()->route('agent_home');
        //         } elseif (Auth::user()->getRoleNames()->first() == 'Master Agent') {
        //             return redirect()->route('agent_master_home');
        //         } elseif (Auth::user()->getRoleNames()->first() == 'Student'){
        //             return redirect()->route('student_home');
        //         }else{
        //             return redirect()->route('web_home');
        //         }
        //     } else {
        //         Auth::logout();
        //         return redirect()->back()->withInput()->withErrors(['email' => 'These credentials do not match our records.']);
        //     }
        // } else {
        //     Auth::logout();
        //     return redirect()->back()->withInput()->withErrors(['email' => 'These credentials do not match our records.']);
        // }
    }
}
