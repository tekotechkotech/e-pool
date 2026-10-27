<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showLogin()
    {
        return $this->showLoginForm();
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            $penguruses = $user->person?->penguruses()
                ->where('is_active', true)
                ->whereHas('pengurusJabatan', fn ($query) => $query->where('is_active', true))
                ->with('pengurusJabatan')
                ->get();

            // Jika tidak terdaftar sebagai pengurus sama sekali
            if (!$penguruses || $penguruses->isEmpty()) {
                // Kecuali email admin khusus jika ada
                if ($user->email === 'admin@baznas.go.id' || $user->email === 'admin@baznas.id') {
                    session([
                        'active_jabatan' => 'Admin System',
                        'active_pengurus_id' => null
                    ]);
                    return redirect()->intended('/dashboard');
                }

                Auth::logout();
                $request->session()->invalidate();
                return back()->withErrors(['email' => 'Akun Anda tidak terdaftar sebagai pengurus aktif.'])->withInput();
            }

            // Jika memiliki lebih dari 1 jabatan aktif, arahkan ke pemilihan jabatan
            if ($penguruses->count() > 1) {
                return redirect()->route('select-jabatan');
            }

            // Jika hanya 1 jabatan, set session otomatis
            $tunggal = $penguruses->first();
            session([
                'active_jabatan' => $tunggal->pengurusJabatan->nama ?? '',
                'active_pengurus_id' => $tunggal->id
            ]);

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
    }

    public function selectJabatan()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $penguruses = Auth::user()->person?->penguruses ?? collect();
        return view('auth.select-jabatan', compact('penguruses'));
    }

    public function showSelectJabatan()
    {
        return $this->selectJabatan();
    }

    public function updateJabatan(Request $request)
    {
        $request->validate([
            'pengurus_id' => 'required|exists:penguruses,id'
        ]);

            $pengurus = Auth::user()->person?->penguruses()
                ->where('id', $request->pengurus_id)
                ->where('is_active', true)
                ->whereHas('pengurusJabatan', fn ($query) => $query->where('is_active', true))
                ->with('pengurusJabatan')
                ->first();

        if ($pengurus) {
            session([
                'active_jabatan' => $pengurus->pengurusJabatan->nama ?? '',
                'active_pengurus_id' => $pengurus->id
            ]);

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['pengurus_id' => 'Jabatan tidak valid atau tidak diizinkan.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
