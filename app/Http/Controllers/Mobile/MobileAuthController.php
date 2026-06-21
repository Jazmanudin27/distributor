<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileAuthController extends Controller
{
    /**
     * Tampilan Login Terpadu (Sales & Owner dalam satu halaman)
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role ?? '');
        }
        return view('mobile.auth.login');
    }

    /**
     * Proses Login Terpadu — auto redirect berdasarkan role
     */
    public function unifiedLogin(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(['name' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();

            // Cek status aktif
            if ($user->status !== '1') {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Akun Anda tidak aktif. Hubungi administrator.',
                ])->onlyInput('username');
            }

            // Cek role diizinkan masuk ke mobile
            $role = strtolower($user->role ?? '');
            $allowedRoles = ['sales', 'owner', 'admin', 'super admin', 'superadmin'];
            if (!in_array($role, $allowedRoles)) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Akun ini tidak memiliki akses ke portal mobile.',
                ])->onlyInput('username');
            }

            $request->session()->regenerate();

            // Auto redirect berdasarkan role
            return $this->redirectByRole($role);
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    /**
     * Redirect helper berdasarkan role
     */
    private function redirectByRole(string $role): \Illuminate\Http\RedirectResponse
    {
        $role = strtolower($role);
        $ownerRoles = ['owner', 'admin', 'super admin', 'superadmin'];

        if (in_array($role, $ownerRoles)) {
            return redirect()->route('mobile.owner.dashboard');
        }

        // sales / spv sales
        return redirect()->route('mobile.dashboard');
    }

    /**
     * Proses Logout (berlaku untuk semua role)
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('mobile.login');
    }

    // ─── Legacy redirects (backward compatibility) ───────────────────────────

    /**
     * @deprecated Redirect ke login terpadu
     */
    public function showOwnerLoginForm()
    {
        return redirect()->route('mobile.login');
    }

    /**
     * @deprecated Redirect ke login terpadu
     */
    public function ownerLogin(Request $request)
    {
        return redirect()->route('mobile.login');
    }

    /**
     * @deprecated Alias logout
     */
    public function ownerLogout(Request $request)
    {
        return $this->logout($request);
    }
}
