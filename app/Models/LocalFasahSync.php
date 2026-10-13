<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalFasahSync extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'fasah_response' => 'array',
            'synced_at' => 'datetime',
            'paid_at' => 'datetime',
            'settled_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
