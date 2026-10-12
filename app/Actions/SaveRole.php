<?php

namespace App\Actions;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveRole
{
    public function __construct(private RecordActivity $activity) {}

    public function save(array $data, ?Role $role, User $actor): Role
    {
        if ($role?->locked) throw ValidationException::withMessages(['role' => 'لا يمكن تعديل دور Owner.']);
        if ($role?->built_in && $role->name !== $data['name']) throw ValidationException::withMessages(['name' => 'لا يمكن تغيير اسم دور أساسي.']);
        $allowed = config('local_permissions');
        if (array_diff($data['permissions'] ?? [], $allowed)) throw ValidationException::withMessages(['permissions' => 'صلاحية غير معروفة.']);
        return DB::transaction(function () use ($data, $role, $actor) {
            $role ??= new Role(['slug' => 'custom-'.bin2hex(random_bytes(8))]);
            $role->fill(['name' => $data['name'], 'description' => $data['description'] ?? null]);
            $role->save();
            $role->permissions()->delete();
            foreach (array_unique($data['permissions'] ?? []) as $permission) $role->permissions()->create(['permission' => $permission]);
            $this->activity->record($actor, 'role.saved', 'role', $role->id, $role->name);
            return $role->load('permissions');
        });
    }

    public function delete(Role $role, User $actor): void
    {
        if ($role->built_in || $role->staff()->withTrashed()->exists()) throw ValidationException::withMessages(['role' => 'لا يمكن حذف دور أساسي أو مستخدم.']);
        DB::transaction(function () use ($role, $actor) {
            $this->activity->record($actor, 'role.deleted', 'role', $role->id, $role->name);
            $role->delete();
        });
    }
}
