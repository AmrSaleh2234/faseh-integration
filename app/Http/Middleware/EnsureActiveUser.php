<?php

namespace App\Http\Middleware;

use App\Actions\AuthenticateUser;
use Closure;
use Illuminate\Http\Request;

class EnsureActiveUser
{
    public function __construct(private AuthenticateUser $auth) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user->active || !$user->staff?->login_allowed || $user->staff?->status !== 'نشط') {
            $this->auth->logout($request);
            return redirect()->route('login')->withErrors(['email' => 'هذا الحساب غير مفعل.']);
        }

        return $next($request);
    }
}
