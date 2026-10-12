@extends('layouts.admin')
@section('title', 'الأدوار والصلاحيات - Titanic Ways')
@section('section-name', 'الإعدادات')
@section('page-name', 'الأدوار والصلاحيات')
@push('styles')<link rel="stylesheet" href="{{ asset('settings.css') }}">@endpush
@section('page-actions')<div class="actions settings-actions">@can('activity.view')<a class="settings-action-link" href="{{ route('settings.activity') }}">سجل النشاط ←</a>
@endcan
</div>@endsection
@section('content')
<div class="settings-intro panel"><div><span class="settings-eyebrow">إدارة الوصول</span><h1>الأدوار والصلاحيات</h1><p>الصلاحيات المطبقة على حسابات الموظفين.</p></div></div>
@can('roles.manage')<details class="settings-section panel" style="padding:18px;margin:18px 0"><summary>＋ دور جديد</summary><form method="post" action="{{ route('roles.store') }}">@csrf<label>اسم الدور<input name="name" required maxlength="60"></label><label>الوصف<input name="description" maxlength="160"></label><h3>الصلاحيات</h3><div class="permissions-grid">@foreach(config('local_permissions') as $permission)<label><input type="checkbox" name="permissions[]" value="{{ $permission }}"> {{ config('permission_labels.'.$permission) }}</label>
@endforeach
</div><button class="btn green" type="submit">حفظ الدور</button></form></details>
@endcan

<div class="settings-stats"><div class="settings-stat panel"><span>إجمالي الأدوار</span><strong>{{ $roles->count() }}</strong></div><div class="settings-stat panel"><span>أدوار مخصصة</span><strong>{{ $roles->where('built_in',false)->count() }}</strong></div><div class="settings-stat panel"><span>موظفون مرتبطون</span><strong>{{ $roles->sum('staff_count') }}</strong></div></div>
<section class="settings-section panel" style="padding:18px"><h2>قائمة الأدوار</h2><div class="role-list">@foreach($roles as $role)<div class="role-card" style="display:block;padding:16px"><div class="role-identity"><strong>{{ $role->name }}</strong><p>{{ $role->description }}</p><small>{{ $role->permissions->count() }} صلاحية · {{ $role->staff_count }} موظف</small></div>
@if($role->locked)<p>دور Owner محمي ويملك جميع الصلاحيات.</p>
@else

@can('roles.manage')<details><summary>تعديل الصلاحيات</summary><form method="post" action="{{ route('roles.update',$role) }}">@csrf @method('PUT')<label>اسم الدور<input name="name" value="{{ $role->name }}" required @if($role->built_in) readonly 
@endif
></label><label>الوصف<input name="description" value="{{ $role->description }}"></label><div class="permissions-grid">@foreach(config('local_permissions') as $permission)<label><input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked($role->permissions->contains('permission',$permission))> {{ config('permission_labels.'.$permission) }}</label>
@endforeach
</div><button class="btn green" type="submit">حفظ</button></form></details>
@if(!$role->built_in && !$role->staff_count)<form method="post" action="{{ route('roles.destroy',$role) }}" onsubmit="return confirm('حذف هذا الدور؟')">@csrf @method('DELETE')<button class="btn gray small" type="submit">حذف الدور</button></form>
@endif

@endcan


@endif
</div>
@endforeach
</div></section>
@endsection
