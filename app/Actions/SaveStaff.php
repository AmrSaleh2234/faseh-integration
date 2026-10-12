<?php

namespace App\Actions;

use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveStaff
{
    public function __construct(private RecordActivity $activity) {}

    private function ensureOwnerRemains(?Staff $staff, array $data): void
    {
        if (!$staff || $staff->role?->slug !== 'owner' || !$staff->login_allowed || $staff->status !== 'نشط') return;
        $newRole = Role::find($data['role_id'] ?? $staff->role_id);
        if ($newRole?->slug === 'owner' && ($data['login_allowed'] ?? false) && ($data['status'] ?? 'نشط') === 'نشط') return;
        $others = Staff::whereHas('role', fn ($q) => $q->where('slug', 'owner'))->where('login_allowed', true)->where('status', 'نشط')->where('id', '!=', $staff->id)->exists();
        if (!$others) throw ValidationException::withMessages(['role_id' => 'يجب إبقاء حساب Owner نشط واحد على الأقل.']);
    }

    public function save(array $data, ?Staff $staff, User $actor): Staff
    {
        return DB::transaction(function () use ($data, $staff, $actor) {
            $staff?->load('role', 'user');
            $targetRole = Role::findOrFail($data['role_id']);
            if (($targetRole->slug === 'owner' || $staff?->role?->slug === 'owner') && $actor->staff?->role?->slug !== 'owner') {
                throw ValidationException::withMessages(['role_id' => 'إدارة حسابات Owner متاحة للمالك فقط.']);
            }
            $this->ensureOwnerRemains($staff, $data);
            $staff ??= new Staff();
            $staff->fill(collect($data)->only(['first_name', 'middle_name', 'last_name', 'code', 'email', 'type', 'language', 'role_id', 'notes', 'status'])->all());
            $staff->status ??= 'نشط';
            $staff->login_allowed = (bool) ($data['login_allowed'] ?? false);
            $staff->extra = collect($data)->only(['birth_date', 'gender', 'country', 'citizenship', 'identity_number', 'nationality', 'residency_expiry', 'mobile', 'phone', 'personal_email', 'job_title', 'department', 'employment_type', 'join_date'])->all();
            if (isset($data['photo'])) $staff->photo_path = $data['photo']->store('staff', 'local');
            $staff->save();
            if ($staff->login_allowed) {
                if (!$staff->email) throw ValidationException::withMessages(['email' => 'البريد الإلكتروني مطلوب للسماح بالدخول.']);
                $user = $staff->user ?: new User(['staff_id' => $staff->id]);
                if (!$user->exists && empty($data['password'])) throw ValidationException::withMessages(['password' => 'كلمة المرور مطلوبة للحساب الجديد.']);
                $user->fill(['name' => $staff->full_name, 'email' => $staff->email, 'active' => $staff->status === 'نشط']);
                if (!empty($data['password'])) { $user->password = $data['password']; $user->must_change_password = true; }
                $user->save();
            } elseif ($staff->user) {
                $staff->user->update(['active' => false]);
            }
            $this->activity->record($actor, 'staff.saved', 'staff', $staff->id, $staff->full_name);
            return $staff;
        });
    }

    public function deactivate(Staff $staff, User $actor): void
    {
        DB::transaction(function () use ($staff, $actor) {
            $staff->load('role', 'user');
            $this->ensureOwnerRemains($staff, ['login_allowed' => false, 'status' => 'غير نشط']);
            $staff->update(['status' => 'غير نشط', 'login_allowed' => false]);
            $staff->user?->update(['active' => false]);
            $this->activity->record($actor, 'staff.deactivated', 'staff', $staff->id, $staff->full_name);
        });
    }
}
