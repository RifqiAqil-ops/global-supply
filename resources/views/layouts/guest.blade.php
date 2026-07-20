<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Welcome') | Waypoint</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="text-center mb-4">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                        <img src="{{ asset('images/logo.png') }}" alt="Waypoint Logo" style="height: 36px; width: auto; flex-shrink: 0; object-fit: contain;">
                        <span class="text-primary fs-3 fw-bold" style="letter-spacing: 0.5px; font-family: 'Inter', sans-serif;">
                            Waypoint
                        </span>
                    </div>
                    <p class="text-muted small">Global Supply Chain Intelligence</p>
                </div>
                
                @yield('content')
                
            </div>
        </div>
    </div>
</body>
</html>
