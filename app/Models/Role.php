<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['built_in' => 'boolean', 'locked' => 'boolean']; }
    public function permissions(): HasMany { return $this->hasMany(RolePermission::class); }
    public function staff(): HasMany { return $this->hasMany(Staff::class); }
    public function permits(string $permission): bool { return $this->slug === 'owner' || $this->permissions->contains('permission', $permission); }
}
