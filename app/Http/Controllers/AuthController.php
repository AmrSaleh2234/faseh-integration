<?php

namespace App\Http\Controllers;

use App\Actions\AuthenticateUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm() { return view('auth.login'); }
    public function login(Request $request, AuthenticateUser $action)
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $action->login($request, $data);
        return redirect()->intended(route('dashboard'));
    }
    public function logout(Request $request, AuthenticateUser $action)
    {
        $action->logout($request);
        return redirect()->route('login');
    }
    public function passwordForm() { return view('auth.change-password'); }
    public function password(Request $request, AuthenticateUser $action)
    {
        $data = $request->validate(['current_password' => ['required', 'current_password'], 'password' => ['required', 'string', 'min:12', 'confirmed']]);
        $action->changePassword($request, $data['password']);
        return redirect()->route('dashboard');
    }
}
