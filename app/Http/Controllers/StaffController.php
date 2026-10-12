<?php

namespace App\Http\Controllers;

use App\Actions\SaveStaff;
use App\Models\Staff;
use App\Services\LocalReadService;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request, LocalReadService $read) { return view('staff.index', ['staff' => $read->staff($request)]); }
    public function create(LocalReadService $read) { return view('staff.form', ['staff' => null, 'roles' => $read->roleOptions()]); }
    public function edit(Staff $staff, LocalReadService $read) { return view('staff.form', ['staff' => $staff, 'roles' => $read->roleOptions()]); }
    public function store(Request $request, SaveStaff $action)
    {
        $staff = $action->save($this->validated($request), null, $request->user());
        return redirect()->route('staff.index')->with('status', 'تم حفظ الموظف '.$staff->full_name);
    }
    public function update(Request $request, Staff $staff, SaveStaff $action)
    {
        $action->save($this->validated($request, $staff), $staff, $request->user());
        return redirect()->route('staff.index')->with('status', 'تم تحديث الموظف.');
    }
    public function destroy(Request $request, Staff $staff, SaveStaff $action)
    {
        $action->deactivate($staff, $request->user());
        return redirect()->route('staff.index')->with('status', 'تم تعطيل الموظف.');
    }
    private function validated(Request $request, ?Staff $staff = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'], 'middle_name' => ['nullable', 'string', 'max:100'], 'last_name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'unique:staff,code,'.($staff?->id ?? 'NULL')],
            'email' => ['nullable', 'email', 'max:255', 'unique:staff,email,'.($staff?->id ?? 'NULL')],
            'role_id' => ['required', 'exists:roles,id'], 'type' => ['required', 'in:مستخدم,موظف'], 'language' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:نشط,غير نشط'], 'notes' => ['nullable', 'string'], 'photo' => ['nullable', 'image', 'max:5120'],
            'login_allowed' => ['sometimes', 'boolean'], 'password' => ['nullable', 'string', 'min:12', 'confirmed'],
            'birth_date' => ['nullable', 'date'], 'gender' => ['nullable', 'string'], 'country' => ['nullable', 'string'], 'citizenship' => ['nullable', 'string'],
            'identity_number' => ['nullable', 'string'], 'nationality' => ['nullable', 'string'], 'residency_expiry' => ['nullable', 'date'],
            'mobile' => ['nullable', 'string'], 'phone' => ['nullable', 'string'], 'personal_email' => ['nullable', 'email'],
            'job_title' => ['nullable', 'string'], 'department' => ['nullable', 'string'], 'employment_type' => ['nullable', 'string'], 'join_date' => ['nullable', 'date'],
        ]);
    }
}
