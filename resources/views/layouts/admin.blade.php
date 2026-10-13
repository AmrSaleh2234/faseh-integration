<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Titanic ways')</title>
    @stack('styles')
</head>
<body>
    <div class="app">
        @include('layouts.partials.sidebar')

        <div class="workspace">
            @include('layouts.partials.topbar')
            @yield('page-actions')

            <main class="content">
                @if(session('status'))<div class="app-feedback" role="status">{{ session('status') }}</div>@endif
                @if($errors->any())<div class="app-feedback error" role="alert"><strong>تعذر حفظ البيانات. اضغط على الخطأ للانتقال إلى الحقل:</strong><ul>@foreach($errors->messages() as $field => $messages)@foreach($messages as $error)<li><a href="#" class="validation-link" data-validation-field="{{ $field }}">{{ $error }}</a></li>@endforeach @endforeach</ul></div>@endif
                @yield('content')
            </main>
        </div>
    </div>

    <button class="chat" type="button">✦ لديك سؤال</button>
    @stack('dialogs')
    <script src="{{ asset('sidebar.js') }}"></script>
    <script defer src="{{ asset('validation-errors.js') }}"></script>
    <style>.app-feedback{background:#e7f6ed;color:#21664c;border:1px solid #aad6bd;padding:12px 16px;margin:0 0 18px}.app-feedback.error{background:#fff0ed;color:#9b3f37;border-color:#efbeb8}.app-feedback ul{margin:6px 0 0}.app-feedback.error a{color:inherit;text-decoration:underline}.field-error{display:block;color:#a83730;font-size:.82em;margin-top:4px}.has-error{border-color:#d65b52!important;outline-color:#d65b52}.topbar-logout{border:0;background:transparent;color:white;cursor:pointer;font:inherit;padding:4px 8px}.topbar-logout:hover{text-decoration:underline}</style>
    @stack('scripts')
</body>
</html>
