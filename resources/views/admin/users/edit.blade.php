@extends('layouts.app')

@section('title', 'Edit Operator')

@section('content')
<div class="container-fluid py-4" style="max-width: 680px;">
    <!-- Back link -->
    <div class="mb-4">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary-saas">
            <i class="bi bi-arrow-left"></i> Back to Users
        </a>
    </div>

    <!-- Form card -->
    <div class="card border-0 shadow-sm p-4" style="border-radius: 24px; border: 1px solid #E5E7EB !important; background-color: #FFFFFF;">
        <h2 class="typo-h2 mb-2">Edit Operator: {{ $user->name }}</h2>
        <p class="text-muted small mb-4">Modify account metadata, assign administrative privileges, or override credentials.</p>

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="name" class="form-label text-dark fw-semibold small">Full Name</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label text-dark fw-semibold small">Email Address</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="role" class="form-label text-dark fw-semibold small">System Role</label>
                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Standard User</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="p-4 bg-light rounded-4 mb-4 mt-4">
                <h4 class="typo-h3 mb-2" style="font-size: 1rem;">Reset Password / Update Credentials</h4>
                <p class="text-muted small mb-3">Leave blank if you do not wish to reset the password for this operator account.</p>

                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="password" class="form-label text-dark fw-semibold small">New Password</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Min 8 characters">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label text-dark fw-semibold small">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Re-enter new password">
                    </div>
                </div>
            </div>

            <div class="pt-3 border-top mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary-saas">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Operator Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
