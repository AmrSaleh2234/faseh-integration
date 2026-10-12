@extends('layouts.admin')
@section('title', 'الموظفون - Titanic Ways')
@section('section-name', 'الموظفين')
@section('page-name', 'إدارة الموظفين')
@push('styles')<link rel="stylesheet" href="{{ asset('staff.css') }}">@endpush
@section('page-actions')<div class="actions staff-actions">@can('staff.create')<a class="btn green" href="{{ route('staff.create') }}">＋ أضف موظف</a>
@endcan
 <span>{{ $staff->total() }} موظف</span></div>@endsection
@section('content')
<section class="staff-search panel"><div class="staff-search-heading"><strong>بحث وتصفية</strong></div><form method="get" action="{{ route('staff.index') }}" class="staff-search-fields" style="padding:15px"><label>الاسم أو الكود<input name="q" value="{{ request('q') }}"></label><label>الحالة<select name="status"><option value="">الكل</option><option @selected(request('status') === 'نشط')>نشط</option><option @selected(request('status') === 'غير نشط')>غير نشط</option></select></label><label>النوع<select name="type"><option value="">الكل</option><option @selected(request('type') === 'مستخدم')>مستخدم</option><option @selected(request('type') === 'موظف')>موظف</option></select></label><label>الدور<input name="role" value="{{ request('role') }}"></label><button class="btn gray small" type="submit">بحث</button><a class="btn gray small" href="{{ route('staff.index') }}">مسح</a></form></section>
<section class="staff-table panel"><div class="staff-table-head"><span>الاسم</span><span>الدور الوظيفي</span><span>البريد</span><span>النوع</span><span>الحالة</span><span>الإجراءات</span></div><div id="staff-rows">
@forelse($staff as $employee)<article class="staff-row"><div class="staff-identity"><span class="staff-avatar">{{ mb_substr($employee->first_name,0,1) }}</span><div>@can('update', $employee)<a href="{{ route('staff.edit',$employee) }}">{{ $employee->full_name }}</a>
@else
<strong>{{ $employee->full_name }}</strong>
@endcan
<small>#{{ $employee->code }}</small></div></div><span>{{ $employee->role?->name }}</span><span>{{ $employee->email ?: '—' }}</span><span>{{ $employee->type }}</span><span class="staff-status">{{ $employee->status }}</span><div>@can('update', $employee)<a href="{{ route('staff.edit',$employee) }}">تعديل</a>
@endcan
 @can('delete', $employee)<form method="post" action="{{ route('staff.destroy',$employee) }}" onsubmit="return confirm('تعطيل الموظف؟')">@csrf @method('DELETE')<button class="staff-text-button" type="submit">تعطيل</button></form>
@endcan
</div></article>

@empty
<p class="staff-empty" style="padding:30px;text-align:center">لا يوجد موظفون بعد.</p>
@endforelse

</div></section><div style="margin-top:16px">{{ $staff->links() }}</div>
@endsection
