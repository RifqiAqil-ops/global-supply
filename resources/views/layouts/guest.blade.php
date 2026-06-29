<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Welcome') | GSCRIP Platform</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="text-center mb-4">
                    <h1 class="text-primary fw-extrabold" style="letter-spacing: 2px;">
                        <i class="bi bi-globe-americas me-2"></i>GSCRIP
                    </h1>
                    <p class="text-muted small">Global Supply Chain Risk Intelligence Platform</p>
                </div>
                
                @yield('content')
                
            </div>
        </div>
    </div>
</body>
</html>
