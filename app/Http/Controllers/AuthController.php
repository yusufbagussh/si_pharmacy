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
            'tittle' => 'Login',
            'active' => 'login'
        ]);
    }

    public function changePassword()
    {
        return view('authentication.change_password', [
            'tittle' => 'Change Password',
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
            return back()->with('passwordError', 'Old password is incorrect!');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('pharmacies.dashboard.orders');
    }

    public function authenticate(Request $request)
    {
        $credential = $request->validate([
            'username' => 'required | numeric',
            'password' => 'required'
        ]);

        $user = $this->user->checkPasswordByUsername($credential['username']);

        if ($user->password === null) {
            $user->password = Hash::make($credential['username']);
            $user->save();
        }

        if (Auth::attempt($credential)) {
            $request->session()->regenerate();

            // return redirect()->intended('dashboard');
            return redirect()->route('pharmacies.dashboard.orders');
        }

        return back()->with('loginError', 'Login failed! Please check your credential.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        return redirect('/login');
    }
}
