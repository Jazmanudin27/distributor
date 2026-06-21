<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            $role = strtolower(Auth::user()->role ?? '');
            if (in_array($role, ['sales'])) {
                return redirect()->route('mobile.dashboard');
            }
            return redirect('/');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // We map 'username' input to the 'name' column in the users table
        if (Auth::attempt(['name' => $credentials['username'], 'password' => $credentials['password']])) {
            $user = Auth::user();

            // Cek status aktif
            if ($user->status !== '1') {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Akun Anda tidak aktif.',
                ])->onlyInput('username');
            }

            // Cek jika user adalah sales / spv sales
            $role = strtolower($user->role ?? '');
            if (in_array($role, ['sales'])) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Sales hanya diperbolehkan login melalui aplikasi mobile.',
                ])->onlyInput('username');
            }

            $request->session()->regenerate();

            return $this->redirectByRole($user);
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    /**
     * Redirect user ke halaman yang sesuai berdasarkan role.
     */
    protected function redirectByRole($user)
    {
        $role = strtolower($user->role ?? '');

        if (in_array($role, ['sales'])) {
            return redirect()->route('mobile.dashboard');
        }

        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
