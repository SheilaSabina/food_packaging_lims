<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect($this->redirectPath(Auth::user()));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user && $user->locked_until && $user->locked_until->isFuture()) {
            return back()
                ->withErrors([
                    'locked' => 'Akun dikunci sementara selama 15 menit setelah 3 percobaan gagal. Silakan coba kembali setelah ' . $user->locked_until->format('H:i d/m/Y'),
                ])
                ->withInput($request->only('email'));
        }

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            if ($user) {
                $user->failed_login_attempts = $user->failed_login_attempts + 1;

                if ($user->failed_login_attempts >= 3) {
                    $user->failed_login_attempts = 0;
                    $user->locked_until = now()->addMinutes(15);
                }

                $user->save();
            }

            return back()
                ->withErrors(['email' => 'Email atau password tidak valid.'])
                ->withInput($request->only('email'));
        }

        $user->failed_login_attempts = 0;
        $user->locked_until = null;
        $user->save();

        Auth::login($user);

        return redirect()->intended($this->redirectPath($user));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function redirectPath(User $user): string
    {
        return $user->role === 'supervisor'
            ? route('supervisor.dashboard')
            : route('technician.dashboard');
    }
}
