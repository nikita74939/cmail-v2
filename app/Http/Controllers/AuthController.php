<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\Crypto\ArgonService;
use App\Services\Crypto\ECCService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // === [ TAMPILAN FORM ] ===

    public function showLogin()
    {
        // Tambahkan logika untuk menampilkan pesan jika redirect dari route protected
        $message = null;
        if (session()->has('url.intended') || url()->previous() !== url()->current()) {
            $message = 'Silahkan login terlebih dahulu untuk mengakses halaman tersebut.';
        }

        return view('auth.login', compact('message'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    // === [ PROSES REGISTER ] ===

    public function processRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',  // Tambahkan max untuk keamanan
            'email' => 'required|email|unique:users,email',  // Spesifikkan kolom unique
            'password' => 'required|min:6|confirmed',
        ]);

        // Generate ECC key pair
        $keys = ECCService::generateKeyPair();

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = ArgonService::hashPassword($request->password);
        $user->ecc_public_key = $keys['public'];
        $user->ecc_private_key = $keys['private'];
        $user->save();

        Session::flash('success', 'Registrasi berhasil! Silakan login.');
        return redirect()->route('dashboard.inbox');
    }

    // === [ PROSES LOGIN ] ===

    public function processLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !ArgonService::verifyPassword($request->password, $user->password)) {
            return back()->withErrors(['login' => 'Email atau password salah!'])->withInput();  // Gunakan withErrors untuk konsistensi
        }

        // Simpan session login
        Auth::login($user);

        // Redirect ke halaman yang dimaksud (intended) jika ada, atau default ke inbox
        return redirect()->intended(route('dashboard.inbox'));
    }

    // === [ LOGOUT ] ===
    public function logout(Request $request)  // Tambahkan parameter Request untuk akses session
    {
        // Hapus semua session
        session()->flush();
        
        // Regenerate token CSRF untuk keamanan (mencegah serangan CSRF)
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Anda telah logout.');
    }
}