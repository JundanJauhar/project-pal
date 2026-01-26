<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show login page
     */
    public function show()
    {
        return view('auth.login');
    }

    /**
     * Handle login process (USER + VENDOR)
     */
    public function authenticate(Request $request)
    {
        /*
        |----------------------------------------------------------------------
        | 1. VALIDASI INPUT DASAR
        |----------------------------------------------------------------------
        */
        $credentials = $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
            'captcha'  => 'required|string',
        ]);

        /*
        |----------------------------------------------------------------------
        | 2. VALIDASI CAPTCHA (ON-PREMISE + EXPIRED)
        |----------------------------------------------------------------------
        */
        $captcha = Session::get('captcha');

        // Captcha tidak ada di session
        if (!$captcha) {
            throw ValidationException::withMessages([
                'captcha' => 'Captcha telah kedaluwarsa. Silakan ulangi.',
            ]);
        }

        // Captcha expired (lebih dari 20 detik)
        if (now()->timestamp > $captcha['expires_at']) {
            Session::forget('captcha');

            throw ValidationException::withMessages([
                'captcha' => 'Captcha telah kedaluwarsa. Silakan ulangi.',
            ]);
        }

        // Captcha tidak cocok
        if (strtoupper($request->captcha) !== $captcha['code']) {
            throw ValidationException::withMessages([
                'captcha' => 'Captcha tidak sesuai.',
            ]);
        }

        /*
        |----------------------------------------------------------------------
        | 3. LOGIN VENDOR
        |----------------------------------------------------------------------
        */
        if (str_ends_with(strtolower($credentials['email']), '@vendor.com')) {

            if (Auth::guard('vendor')->attempt([
                'user_vendor' => $credentials['email'],
                'password'    => $credentials['password'],
            ], $request->boolean('remember'))) {

                $request->session()->regenerate();
                Session::forget('captcha');

                return redirect()->route('vendor.index');
            }

            throw ValidationException::withMessages([
                'email' => 'Email login atau password vendor salah.',
            ]);
        }

        /*
        |----------------------------------------------------------------------
        | 4. LOGIN USER INTERNAL
        |----------------------------------------------------------------------
        */
        if (Auth::attempt([
            'email'    => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {

            $request->session()->regenerate();
            Session::forget('captcha');

            if (Auth::user()->hasRole('superadmin')) {
                return redirect()->route('ums.users.index');
            }

            if (Auth::user()->hasRole('sekretaris')) {
                return redirect()->route('sekdir.dashboard');
            }

            return redirect()->route('dashboard');
        }

        /*
        |----------------------------------------------------------------------
        | 5. LOGIN GAGAL
        |----------------------------------------------------------------------
        */
        throw ValidationException::withMessages([
            'email' => 'Email atau password salah.',
        ]);
    }

    /**
     * Logout (USER & VENDOR)
     */
    public function logout(Request $request)
    {
        if (Auth::guard('vendor')->check()) {
            Auth::guard('vendor')->logout();
        }

        if (Auth::check()) {
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
