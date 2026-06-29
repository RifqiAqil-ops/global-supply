<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Risk Platform') | GSCRIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body style="background-color: var(--color-bg); color: var(--color-text-main);">

    <div class="main-wrapper">
        <!-- Sidebar Navigation -->
        @include('layouts.partials.sidebar')

        <!-- Content Area Wrapper -->
        <div class="content-container">
            <!-- Top Navbar -->
            @include('layouts.partials.navbar')

            <!-- Main Content Area -->
            <main class="flex-grow-1 p-4">
                <!-- Breadcrumb Section -->
                @include('layouts.partials.breadcrumbs')

                <!-- Flash Status Alert Banner -->
                @include('layouts.partials.flash')

                <!-- Dynamic Page Yield -->
                @yield('content')
            </main>

            <!-- Sticky Footer Area -->
            @include('layouts.partials.footer')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
