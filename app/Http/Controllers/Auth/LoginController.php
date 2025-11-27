<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validasi input terlebih dahulu
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            // Kirim ke Tracking_App_PAL untuk verifikasi
            $response = Http::timeout(5)->post(config('services.tracking_app.auth_verify'), [
                'email' => $request->email,
                'password' => $request->password
            ]);
        } catch (\Throwable $e) {
            Log::error('Remote Auth Error: ' . $e->getMessage());
            return back()->withErrors([
                'email' => 'Tidak dapat menghubungi server otentikasi. Silakan coba lagi.',
            ])->withInput();
        }

        // Jika status bukan 200 → login gagal
        if ($response->status() !== 200) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->withInput();
        }

        $payload = $response->json();

        // Cek apakah API mengembalikan valid: true
        if (!isset($payload['valid']) || $payload['valid'] !== true) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->withInput();
        }

        // Ambil data user
        $userData = $payload['user'] ?? null;

        if (!$userData || !isset($userData['email'])) {
            Log::warning('Remote auth invalid payload', $payload);
            return back()->withErrors([
                'email' => 'Terjadi kesalahan dalam autentikasi.',
            ])->withInput();
        }

        // Sinkron user (shadow user)
        $user = User::updateOrCreate(
            ['email' => $userData['email']],
            [
                'name' => $userData['full_name'] ?? 'Unknown User',
                'division_id' => $userData['division_id'] ?? null,
                'password' => bcrypt(uniqid('dummy_', true)), // password tidak digunakan
            ]
        );

        // Login user lokal
        Auth::login($user, true);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
