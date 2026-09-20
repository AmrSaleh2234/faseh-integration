<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncedInvoice extends Model
{
    protected $fillable = [
        'daftra_invoice_id',
        'daftra_invoice_number',
        'internal_invoice_number',
        'invoice_type',
        'fasah_invoice_number',
        'sadad_number',
        'status',
        'fasah_status',
        'grand_total',
        'total_vat',
        'logistics_meta',
        'request_payload',
        'fasah_response',
        'daftra_marked_paid',
        'synced_at',
        'paid_at',
        'settled_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'logistics_meta' => 'array',
            'request_payload' => 'array',
            'fasah_response' => 'array',
            'daftra_marked_paid' => 'boolean',
            'grand_total' => 'decimal:2',
            'total_vat' => 'decimal:2',
            'synced_at' => 'datetime',
            'paid_at' => 'datetime',
            'settled_at' => 'datetime',
        ];
    }

    public const STATUS_PENDING = 'pending';

    public const STATUS_SYNCED = 'synced';

    public const STATUS_FAILED = 'failed';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_SETTLED = 'settled';

    public const STATUS_WAITING = 'waiting';
}
