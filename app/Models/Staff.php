<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Staff extends Model
{
    use SoftDeletes;
    protected $table = 'staff';
    protected $guarded = [];
    protected function casts(): array { return ['login_allowed' => 'boolean', 'extra' => 'array']; }
    public function role(): BelongsTo { return $this->belongsTo(Role::class); }
    public function user(): HasOne { return $this->hasOne(User::class); }
    public function getFullNameAttribute(): string { return trim(implode(' ', array_filter([$this->first_name, $this->middle_name, $this->last_name]))); }
}
