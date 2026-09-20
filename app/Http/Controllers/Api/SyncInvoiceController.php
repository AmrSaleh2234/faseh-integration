<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SyncDaftraInvoiceToFasahJob;
use App\Models\SyncedInvoice;
use App\Services\InvoiceSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SyncInvoiceController extends Controller
{
    /**
     * Sync one Daftra invoice to Fasah Pay (sync or queue).
     *
     * POST /api/fasah/sync/{daftraInvoiceId}
     * Body (optional logistics overrides):
     * {
     *   "queue": false,
     *   "invoice_type": "billoflading",
     *   "bill_of_lading": "...",
     *   "doc_ref_no": "...",
     *   "port": "30",
     *   "shipment_type": 1
     * }
     */
    public function store(Request $request, int $daftraInvoiceId, InvoiceSyncService $sync): JsonResponse
    {
        $meta = $request->except(['queue', 'force']);
        $meta['force'] = $request->boolean('force');

        if ($request->boolean('queue')) {
            SyncDaftraInvoiceToFasahJob::dispatch($daftraInvoiceId, $meta);

            return response()->json([
                'result' => 'queued',
                'daftra_invoice_id' => $daftraInvoiceId,
            ], 202);
        }

        try {
            $record = $sync->syncDaftraInvoice($daftraInvoiceId, $meta);

            return response()->json([
                'result' => 'success',
                'data' => $record,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'result' => 'failed',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(int $daftraInvoiceId): JsonResponse
    {
        $record = SyncedInvoice::query()
            ->where('daftra_invoice_id', $daftraInvoiceId)
            ->firstOrFail();

        return response()->json(['result' => 'success', 'data' => $record]);
    }

    public function index(Request $request): JsonResponse
    {
        $items = SyncedInvoice::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate((int) $request->get('per_page', 20));

        return response()->json($items);
    }
}
