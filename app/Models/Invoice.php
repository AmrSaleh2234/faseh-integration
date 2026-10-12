<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Invoice extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['invoice_date' => 'date', 'issue_date' => 'date', 'issued_at' => 'datetime', 'deposit_paid' => 'boolean', 'insurance_visible' => 'boolean']; }
    public function client(): BelongsTo { return $this->belongsTo(Client::class)->withTrashed(); }
    public function salesStaff(): BelongsTo { return $this->belongsTo(Staff::class, 'sales_staff_id')->withTrashed(); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class)->orderBy('position'); }
    public function customFields(): HasMany { return $this->hasMany(InvoiceCustomField::class)->orderBy('position'); }
    public function payments(): HasMany { return $this->hasMany(InvoicePayment::class); }
    public function attachments(): MorphMany { return $this->morphMany(Attachment::class, 'attachable'); }
    public function getBalanceAttribute(): float { return max(0, round((float) $this->total - (float) $this->amount_paid, 2)); }
    public function getDisplayStatusAttribute(): string { return $this->status === 'draft' ? 'مسودة' : ($this->balance <= 0 ? 'مدفوعة' : 'غير مدفوعة'); }
}
