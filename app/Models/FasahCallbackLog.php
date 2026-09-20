<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FasahCallbackLog extends Model
{
    protected $fillable = [
        'type',
        'fasah_invoice_number',
        'internal_invoice_number',
        'sadad_number',
        'invoice_status',
        'payload',
        'processed',
        'processing_error',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed' => 'boolean',
        ];
    }
}
