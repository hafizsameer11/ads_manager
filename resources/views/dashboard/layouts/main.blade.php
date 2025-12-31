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
                {{-- Display Announcements --}}
                @if(isset($announcements) && $announcements->count() > 0)
                    <div class="announcements-container" style="margin-bottom: 20px;">
                        @foreach($announcements as $announcement)
                            <div class="alert alert-{{ $announcement->type == 'danger' ? 'danger' : ($announcement->type == 'warning' ? 'warning' : ($announcement->type == 'success' ? 'success' : 'info')) }} alert-dismissible fade show" role="alert" style="margin-bottom: 10px;">
                                <strong>{{ $announcement->title }}</strong>
                                <div style="margin-top: 8px;">{!! nl2br(e($announcement->content)) !!}</div>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                @yield('content')
            </main>
            
            @include('dashboard.layouts.footer')
        </div>
    </div>

    @include('dashboard.layouts.script')
    @stack('scripts')
</body>
</html>




