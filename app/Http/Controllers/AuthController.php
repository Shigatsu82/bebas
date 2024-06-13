<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\VarDumper\Caster\RedisCaster;

class AuthController extends Controller
{
    public function register(){
        return view('auth.register');
    }
    public function login(){
        return view('auth.login');
    }
    public function registerPost(Request $request)
    {
        $user = new User();

        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        $user->save();

        return redirect()->route('login')->with('success', 'Register successfully');
    }

    public function loginPost(Request $request){
        $credential = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if(Auth::attempt($credential)){
            return redirect()->route('dashboard')->with('success','Welcome and Happy Mewing!');
        }
        return back()->with('error', 'Email or password invalid');
    }
    public function logout(){
        Auth::logout();
        return redirect()->route('login')->with('success', 'Logout Mewing!');
    }
}
