<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        // 1. VALIDASI INPUT
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
            'captcha'  => 'required|string',
        ]);

        $loginInput = trim($request->login);

        // 2. VALIDASI CAPTCHA
        $captcha = Session::get('captcha');

        if (!$captcha || now()->timestamp > $captcha['expires_at']) {
            Session::forget('captcha');
            throw ValidationException::withMessages([
                'captcha' => 'Captcha telah kedaluwarsa. Silakan ulangi.',
            ]);
        }

        if (strtoupper($request->captcha) !== $captcha['code']) {
            throw ValidationException::withMessages([
                'captcha' => 'Captcha tidak sesuai.',
            ]);
        }

        // 3. LOGIN VENDOR (EMAIL KHUSUS)
        if (str_ends_with($loginInput, '@vendor.com')) {

            if (Auth::guard('vendor')->attempt([
                'user_vendor' => $loginInput,
                'password'    => $request->password,
            ], $request->boolean('remember'))) {

                $request->session()->regenerate();
                Session::forget('captcha');

                return redirect()->route('vendor.index');
            }

            throw ValidationException::withMessages([
                'login' => 'Email atau password vendor salah.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. LOGIN USER INTERNAL
        |    - EMAIL
        |    - NAME 
        |--------------------------------------------------------------------------
        */
        // Cek EMAIL
        $user = User::whereRaw('BINARY email = ?', [$loginInput])
            ->where('status', 'active')
            ->first();

        // NAME (HARUS SAMA PERSIS)
        if (!$user) {
            $user = User::whereRaw('BINARY name = ?', [$loginInput])
                ->where('status', 'active')
                ->first();
        }

        if (!$user) {
            throw ValidationException::withMessages([
                'login' => 'Username atau email tidak ditemukan.',
            ]);
        }

        // Auth pakai EMAIL (standar Laravel)
        if (Auth::attempt([
            'email'    => $user->email,
            'password' => $request->password,
        ], $request->boolean('remember'))) {

            $request->session()->regenerate();
            Session::forget('captcha');

            Auth::user()->update([
                'last_login_at' => now(),
            ]);

            $user = Auth::user()->loadAuthContext();

            if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
                return redirect()->route('ums.users.index');
            }

            if ($user->hasRole('sekdir')) {
                return redirect()->route('sekdir.dashboard');
            }

            return redirect()->route('dashboard');
        }

        throw ValidationException::withMessages([
            'login' => 'Password salah.',
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
