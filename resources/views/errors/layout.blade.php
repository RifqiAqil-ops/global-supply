<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | GSCRIP Platform</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card card-premium p-5 shadow-lg border-0">
                    <div class="display-1 text-primary fw-extrabold mb-3">
                        @yield('code')
                    </div>
                    <h2 class="h3 text-white mb-3">@yield('title')</h2>
                    <p class="text-muted mb-4">@yield('message')</p>
                    <a href="{{ url('/') }}" class="btn btn-primary px-4 py-2 rounded-pill fw-semibold">
                        <i class="bi bi-house-door me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
