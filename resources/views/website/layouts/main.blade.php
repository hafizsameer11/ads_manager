<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('website.layouts.head')
</head>
<body>
    @include('website.layouts.header')

    <main id="main-content">
        @yield('content')
    </main>

    @include('website.layouts.footer')

    @include('website.layouts.script')
</body>
</html>




