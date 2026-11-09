<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Login sederhana untuk masuk sesuai role (Staff, Manager, Driver).
class AuthController extends Controller
{
    public const ROLE_DIIZINKAN = ['Staff', 'Manager', 'Driver'];

    public function form()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $kredensial = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (! Auth::attempt($kredensial)) {
            return back()->withErrors(['email' => 'Email atau password salah'])->onlyInput('email');
        }

        if (! in_array(Auth::user()->role, self::ROLE_DIIZINKAN, true)) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun ini tidak memiliki akses ke halaman Staff, Manager, atau Driver.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(self::beranda(Auth::user()->role));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public static function beranda(string $role): string
    {
        return route(strtolower($role).'.dashboard');
    }
}
