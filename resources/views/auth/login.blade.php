@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="card card-premium p-4 shadow border-0">
    <h2 class="h4 text-white mb-3 text-center">Sign In</h2>
    
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label text-muted small">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-secondary text-muted"><i class="bi bi-envelope"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="form-control bg-transparent border-secondary text-white @error('email') is-invalid @enderror" placeholder="name@example.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="password" class="form-label text-muted small mb-0">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-primary small text-decoration-none">Forgot password?</a>
                @endif
            </div>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-secondary text-muted"><i class="bi bi-lock"></i></span>
                <input id="password" type="password" name="password" required class="form-control bg-transparent border-secondary text-white @error('password') is-invalid @enderror" placeholder="••••••••">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Remember Me -->
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label text-muted small" for="remember">
                Remember me on this device
            </label>
        </div>

        <!-- Submit Button -->
        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary py-2 fw-semibold rounded">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </div>

        <!-- Register Link -->
        <div class="text-center">
            <span class="text-muted small">New to GSCRIP?</span>
            <a href="{{ route('register') }}" class="text-primary small text-decoration-none fw-semibold ms-1">Create an account</a>
        </div>
    </form>
</div>
@endsection
