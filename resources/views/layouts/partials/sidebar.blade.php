<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="ship">⚓</div>
        <strong>Titanic<span>Ways</span></strong>
    </div>

    <div class="side-row">
        <a href="{{ route('invoices.index') }}" class="side-link">
            <span class="side-icon">▤</span><span>المبيعات</span>
        </a>
        <button class="side-toggle" type="button" aria-label="عرض قائمة المبيعات" aria-controls="sales-subnav" aria-expanded="{{ request()->routeIs('invoices.*') ? 'true' : 'false' }}">⌄</button>
    </div>
    <nav class="side-subnav" id="sales-subnav" aria-label="قائمة المبيعات" @unless(request()->routeIs('invoices.*')) hidden @endunless>
        <a href="{{ route('invoices.index') }}" @if(request()->routeIs('invoices.index', 'invoices.show', 'invoices.edit')) aria-current="page" @endif>الفواتير</a>
        <a href="{{ route('invoices.create') }}" @if(request()->routeIs('invoices.create')) aria-current="page" @endif>إضافة فاتورة</a>
    </nav>

    <div class="side-row">
        <a href="{{ route('clients.index') }}" class="side-link">
            <span class="side-icon">♙</span><span>العملاء</span>
        </a>
        <button class="side-toggle" type="button" aria-label="عرض قائمة العملاء" aria-controls="clients-subnav" aria-expanded="{{ request()->routeIs('clients.*') ? 'true' : 'false' }}">⌄</button>
    </div>
    <nav class="side-subnav" id="clients-subnav" aria-label="قائمة العملاء" @unless(request()->routeIs('clients.*')) hidden @endunless>
        <a href="{{ route('clients.index') }}" @if(request()->routeIs('clients.index', 'clients.show', 'clients.edit')) aria-current="page" @endif>إدارة العملاء</a>
        <a href="{{ route('clients.create') }}" @if(request()->routeIs('clients.create')) aria-current="page" @endif>إضافة عميل جديد</a>
    </nav>

    <div class="side-row">
        <a href="{{ route('staff.index') }}" class="side-link">
            <span class="side-icon">♟</span><span>الموظفين</span>
        </a>
        <button class="side-toggle" type="button" aria-label="عرض قائمة الموظفين" aria-controls="staff-subnav" aria-expanded="{{ request()->routeIs('staff.*') ? 'true' : 'false' }}">⌄</button>
    </div>
    <nav class="side-subnav" id="staff-subnav" aria-label="قائمة الموظفين" @unless(request()->routeIs('staff.*')) hidden @endunless>
        <a href="{{ route('staff.index') }}" @if(request()->routeIs('staff.index', 'staff.edit')) aria-current="page" @endif>إدارة الموظفين</a>
        <a href="{{ route('staff.create') }}" @if(request()->routeIs('staff.create')) aria-current="page" @endif>إضافة موظف</a>
    </nav>
</aside>
