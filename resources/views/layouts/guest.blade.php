<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Welcome') | GSCRIP Platform</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="text-center mb-4">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="36" height="36" fill="none" style="flex-shrink: 0;">
                            <path d="M 24 8 L 16 4 L 8 8 L 4 16 L 8 24 L 16 28 L 24 24 L 24 16 L 16 16" stroke="#2563EB" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M 16 4 L 16 16" stroke="#2563EB" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M 4 16 L 16 16" stroke="#2563EB" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M 16 28 L 16 16" stroke="#2563EB" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="24" cy="8" r="2.2" fill="#2563EB" />
                            <circle cx="16" cy="4" r="2.2" fill="#2563EB" />
                            <circle cx="8" cy="8" r="2.2" fill="#2563EB" />
                            <circle cx="4" cy="16" r="2.2" fill="#2563EB" />
                            <circle cx="8" cy="24" r="2.2" fill="#2563EB" />
                            <circle cx="16" cy="28" r="2.2" fill="#2563EB" />
                            <circle cx="24" cy="24" r="2.2" fill="#2563EB" />
                            <circle cx="24" cy="16" r="2.2" fill="#2563EB" />
                            <circle cx="16" cy="16" r="3.2" fill="#2563EB" stroke="#FFFFFF" stroke-width="1.2" />
                        </svg>
                        <span class="text-primary fs-3 fw-extrabold" style="letter-spacing: 0.5px; font-family: 'Outfit', sans-serif;">
                            GSCRIP
                        </span>
                    </div>
                    <p class="text-muted small">Global Supply Chain Risk Intelligence Platform</p>
                </div>
                
                @yield('content')
                
            </div>
        </div>
    </div>
</body>
</html>
