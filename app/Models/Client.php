<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Client extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected function casts(): array { return ['address' => 'array', 'government' => 'boolean']; }
    public function contacts(): HasMany { return $this->hasMany(ClientContact::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function attachments(): MorphMany { return $this->morphMany(Attachment::class, 'attachable'); }
}
