<?php

namespace App\Http\Controllers\Api;

use App\Actions\HandleFasahCallback;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FasahCallbackController extends Controller
{
    public function invoiceNotification(Request $request, HandleFasahCallback $action): JsonResponse
    {
        if (! $this->authorizeCallback($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $processed = $action->invoice($request->all());
        return response()->json(
            ['statusCode' => $processed ? '00000' : '00001', 'statusDesc' => $processed ? 'Received' : 'Processing failed'],
            $processed ? 200 : 503,
        );
    }

    public function settlementNotification(Request $request, HandleFasahCallback $action): JsonResponse
    {
        if (! $this->authorizeCallback($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $processed = $action->settlements($request->all());
        return response()->json(
            ['statusCode' => $processed ? '00000' : '00001', 'statusDesc' => $processed ? 'Received' : 'Processing failed'],
            $processed ? 200 : 503,
        );
    }

    private function authorizeCallback(Request $request): bool
    {
        $secret = config('fasahpay.callback_secret');
        return ! $secret || hash_equals((string) $secret, (string) $request->header('X-Callback-Secret', $request->query('secret', '')));
    }
}
