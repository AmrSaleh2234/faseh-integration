<?php

namespace App\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticateUser
{
    public function login(Request $request, array $credentials): void
    {
        if (! Auth::attempt($credentials)) throw ValidationException::withMessages(['email' => 'بيانات الدخول غير صحيحة.']);
        $user = Auth::user()->load('staff.role');
        if (! $user->active || ! $user->staff?->login_allowed || $user->staff->status !== 'نشط') {
            Auth::logout();
            throw ValidationException::withMessages(['email' => 'هذا الحساب غير مفعل.']);
        }
        $request->session()->regenerate();
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function changePassword(Request $request, string $password): void
    {
        $request->user()->update(['password' => $password, 'must_change_password' => false]);
        $request->session()->regenerate();
    }
}
