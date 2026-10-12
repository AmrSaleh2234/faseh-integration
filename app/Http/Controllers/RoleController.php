<?php

namespace App\Http\Controllers;

use App\Actions\SaveRole;
use App\Models\Role;
use App\Services\LocalReadService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(LocalReadService $read) { return view('settings.roles', ['roles' => $read->roles()]); }
    public function store(Request $request, SaveRole $action)
    {
        $action->save($this->validated($request), null, $request->user());
        return redirect()->route('settings.roles')->with('status', 'تم حفظ الدور.');
    }
    public function update(Request $request, Role $role, SaveRole $action)
    {
        $action->save($this->validated($request, $role), $role, $request->user());
        return redirect()->route('settings.roles')->with('status', 'تم تحديث الدور.');
    }
    public function destroy(Request $request, Role $role, SaveRole $action)
    {
        $action->delete($role, $request->user());
        return redirect()->route('settings.roles')->with('status', 'تم حذف الدور.');
    }
    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:60', 'unique:roles,name,'.($role?->id ?? 'NULL')], 'description' => ['nullable', 'string', 'max:160'], 'permissions' => ['nullable', 'array'], 'permissions.*' => ['string', 'in:'.implode(',', config('local_permissions'))]]);
    }
}
