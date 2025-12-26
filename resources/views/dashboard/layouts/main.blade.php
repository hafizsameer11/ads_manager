<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('dashboard.layouts.head')
</head>
<body>
    <div class="dashboard-wrapper">
        @include('dashboard.layouts.sidebar')
        
        <div class="dashboard-content">
            @include('dashboard.layouts.header')
            
            <main class="dashboard-main">
                @yield('content')
            </main>
            
            @include('dashboard.layouts.footer')
        </div>
    </div>

    @include('dashboard.layouts.script')
    @stack('scripts')
</body>
</html>




