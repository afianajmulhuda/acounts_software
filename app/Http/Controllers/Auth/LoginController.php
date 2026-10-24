<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        // Check if user is locked out
        if ($user && $user->locked_until && Carbon::now()->lt($user->locked_until)) {
            return back()->withErrors(['email' => 'Account locked due to multiple failed attempts. Try again later.']);
        }

        // Check active status
        if ($user && $user->status !== 'ACTIVE') {
            return back()->withErrors(['email' => 'Your account is ' . strtolower($user->status) . '.']);
        }

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            // Reset failed login count and record last login
            $user->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => Carbon::now(),
            ]);

            return redirect()->intended('/dashboard');
        }

        // Increment failed attempts
        if ($user) {
            $user->increment('failed_login_attempts');
            if ($user->failed_login_attempts >= 5) {
                $user->update(['locked_until' => Carbon::now()->addMinutes(15)]);
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}