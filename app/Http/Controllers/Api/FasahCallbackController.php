<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FasahCallbackLog;
use App\Services\InvoiceSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class FasahCallbackController extends Controller
{
    /**
     * Fasah Pay invoice status webhook.
     * POST /api/fasah/callbacks/invoice-notification
     */
    public function invoiceNotification(Request $request, InvoiceSyncService $sync): JsonResponse
    {
        if (! $this->authorizeCallback($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payload = $request->all();

        $log = FasahCallbackLog::query()->create([
            'type' => 'invoice',
            'fasah_invoice_number' => $payload['fasahpayInvoiceNumber']
                ?? $payload['FasahPayInvoiceNumber']
                ?? null,
            'internal_invoice_number' => $payload['internalInvoiceNumber'] ?? null,
            'sadad_number' => $payload['sadadNumber'] ?? null,
            'invoice_status' => $payload['invoiceStatus'] ?? null,
            'payload' => $payload,
        ]);

        try {
            $sync->handleInvoiceNotification($payload);
            $log->update(['processed' => true]);
        } catch (Throwable $e) {
            Log::error('Invoice callback processing failed', ['error' => $e->getMessage(), 'payload' => $payload]);
            $log->update([
                'processed' => false,
                'processing_error' => $e->getMessage(),
            ]);
        }

        // Always 200 so Fasah does not keep retrying endlessly on business errors.
        return response()->json(['statusCode' => '00000', 'statusDesc' => 'Received']);
    }

    /**
     * Fasah Pay settlement webhook (may send a single object or an array).
     * POST /api/fasah/callbacks/settlement-notification
     */
    public function settlementNotification(Request $request, InvoiceSyncService $sync): JsonResponse
    {
        if (! $this->authorizeCallback($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $body = $request->all();
        $items = array_is_list($body) ? $body : [$body];

        foreach ($items as $payload) {
            if (! is_array($payload)) {
                continue;
            }

            $log = FasahCallbackLog::query()->create([
                'type' => 'settlement',
                'fasah_invoice_number' => $payload['invoiceNumber'] ?? null,
                'internal_invoice_number' => $payload['internalInvoiceNumber'] ?? null,
                'sadad_number' => $payload['sadadNumber'] ?? null,
                'invoice_status' => 'SETTLED',
                'payload' => $payload,
            ]);

            try {
                $sync->handleSettlementNotification($payload);
                $log->update(['processed' => true]);
            } catch (Throwable $e) {
                Log::error('Settlement callback processing failed', ['error' => $e->getMessage()]);
                $log->update([
                    'processed' => false,
                    'processing_error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json(['statusCode' => '00000', 'statusDesc' => 'Received']);
    }

    private function authorizeCallback(Request $request): bool
    {
        $secret = config('fasahpay.callback_secret');

        if (! $secret) {
            return true;
        }

        return hash_equals((string) $secret, (string) $request->header('X-Callback-Secret', $request->query('secret', '')));
    }
}
