<?php

use App\Http\Controllers\Api\FasahCallbackController;
use App\Http\Controllers\Api\SyncInvoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Fasah Pay / Daftra bridge API
|--------------------------------------------------------------------------
*/

Route::prefix('fasah')->group(function () {
    // Manual / system sync
    Route::get('synced-invoices', [SyncInvoiceController::class, 'index']);
    Route::get('synced-invoices/{daftraInvoiceId}', [SyncInvoiceController::class, 'show']);
    Route::post('sync/{daftraInvoiceId}', [SyncInvoiceController::class, 'store']);

    // Public callbacks (Fasah Pay → your server). Whitelist Fasah IPs at firewall.
    Route::post('callbacks/invoice-notification', [FasahCallbackController::class, 'invoiceNotification']);
    Route::post('callbacks/settlement-notification', [FasahCallbackController::class, 'settlementNotification']);
});
