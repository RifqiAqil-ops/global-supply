@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
<div class="card card-premium p-4 shadow border-0">
    <h2 class="h4 text-white mb-3 text-center">New Password</h2>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $token }}">

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label text-muted small">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-secondary text-muted"><i class="bi bi-envelope"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus class="form-control bg-transparent border-secondary text-white @error('email') is-invalid @enderror" placeholder="name@example.com">
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
        <div class="d-grid">
            <button type="submit" class="btn btn-primary py-2 fw-semibold rounded">
                <i class="bi bi-check-circle me-2"></i>Reset Password
            </button>
        </div>
    </form>
</div>
@endsection
