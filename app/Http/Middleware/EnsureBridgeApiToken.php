<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBridgeApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('bridge.sync_api_token');
        $provided = (string) $request->header('X-Bridge-Token', '');

        if ($expected === '' || ! hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
