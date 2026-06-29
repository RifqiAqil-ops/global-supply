@extends('layouts.guest')

@section('title', 'Register')

@section('content')
<div class="card card-premium p-4 shadow border-0">
    <h2 class="h4 text-white mb-3 text-center">Create Account</h2>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="form-label text-muted small">Full Name</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-secondary text-muted"><i class="bi bi-person"></i></span>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="form-control bg-transparent border-secondary text-white @error('name') is-invalid @enderror" placeholder="John Doe">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label text-muted small">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-secondary text-muted"><i class="bi bi-envelope"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="form-control bg-transparent border-secondary text-white @error('email') is-invalid @enderror" placeholder="john@example.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label text-muted small">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-secondary text-muted"><i class="bi bi-lock"></i></span>
                <input id="password" type="password" name="password" required class="form-control bg-transparent border-secondary text-white @error('password') is-invalid @enderror" placeholder="••••••••">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label text-muted small">Confirm Password</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-secondary text-muted"><i class="bi bi-lock-fill"></i></span>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="form-control bg-transparent border-secondary text-white" placeholder="••••••••">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary py-2 fw-semibold rounded">
                <i class="bi bi-person-plus me-2"></i>Sign Up
            </button>
        </div>

        <!-- Login Link -->
        <div class="text-center">
            <span class="text-muted small">Already have an account?</span>
            <a href="{{ route('login') }}" class="text-primary small text-decoration-none fw-semibold ms-1">Sign In</a>
        </div>
    </form>
</div>
@endsection
