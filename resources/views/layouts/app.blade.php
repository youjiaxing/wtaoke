<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', '淘券券')</title>

    {{-- 正式环境后记得去掉 time 参数, 避免每次都加载 --}}
    {{--<link rel="stylesheet" href="{{ asset('css/app.css') }}?time={{ time() }}">--}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?time={{ date('ymd') }}">
{{--    <link rel="stylesheet" href="{{ asset('css/app.css') . (app()->environment('local') ? "?time=".time() : "") }}">--}}
    @yield('styles')
</head>
<body>
    <div id="app" class="{{ route_class() }}-page">
        @include('layouts._header')
        <div class="container" id="content">
            @include('layouts._message')
            @yield('content')
        </div>
        @include('layouts._footer')
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    @yield('script')
</body>
</html>