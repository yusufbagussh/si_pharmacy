<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function index()
    {
        return view('authentication.login', [
            'title' => 'Login',
            'active' => 'login'
        ]);
    }

    public function changePassword()
    {
        return view('authentication.change_password', [
            'title' => 'Change Password',
            'active' => 'change-password'
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required | min:6',
            'confirm_password' => 'required | same:new_password'
        ]);

        $user = $this->user->checkPasswordByUsername(Auth::user()->username);

        if (!Hash::check($request->old_password, $user->password)) {
            return back()->with('passwordError', 'Password is incorrect!');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        session()->flash('passwordSuccess', 'Password has been changed!');
        // return back()->with('passwordSuccess', 'Password has been changed!');
        return redirect()->route('pharmacies.dashboard.orders');
    }

    public function authenticate(Request $request)
    {
        $credential = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = $this->user->checkPasswordByUsername($credential['username']);
        if ($user->kode_bagian !== 'k21' && $user->kode_bagian !== 'k45' && $user->kode_bagian !== 'os28') {
            return back()->with('loginError', 'Login failed! Please check your credential.');
        }

        if ($user->password === null) {
            $user->password = Hash::make($credential['username']);
            $user->save();
        }

        if (Auth::attempt($credential)) {
            $request->session()->regenerate();
            return redirect()->route('pharmacies.dashboard.orders');
        }

        return back()->with('loginError', 'Login failed! Please check your credential.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
