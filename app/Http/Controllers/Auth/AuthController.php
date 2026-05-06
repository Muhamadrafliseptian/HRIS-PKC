<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return Inertia::render('Auth/Index');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'captcha' => 'required',
        ]);

        $verify = Http::asForm()->post(
            'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            [
                'secret' => env('SECRET_KEY_CAPTCHA'),
                'response' => $request->captcha,
                'remoteip' => $request->ip(),
            ]
        );

        if (!($verify->json('success'))) {
            return back()->withErrors([
                'global' => 'Captcha tidak valid',
            ]);
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'global' => 'Email atau password salah',
        ]);
    }
    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return successHandler();
        } catch (Exception $e) {
            return errorHandler($e);
        }
    }
}
