@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
<div class="card card-premium p-4 shadow border-0">
    <h2 class="h4 text-white mb-2 text-center">Reset Password</h2>
    <p class="text-muted small text-center mb-4">
        Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.
    </p>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-4">
            <label for="email" class="form-label text-muted small">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-secondary text-muted"><i class="bi bi-envelope"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="form-control bg-transparent border-secondary text-white @error('email') is-invalid @enderror" placeholder="name@example.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary py-2 fw-semibold rounded">
                <i class="bi bi-envelope-open me-2"></i>Send Reset Link
            </button>
        </div>

        <!-- Back to Login -->
        <div class="text-center">
            <a href="{{ route('login') }}" class="text-primary small text-decoration-none fw-semibold">
                <i class="bi bi-arrow-left me-1"></i>Back to Sign In
            </a>
        </div>
    </form>
</div>
@endsection
