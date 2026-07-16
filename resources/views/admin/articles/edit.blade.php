@extends('layouts.app')

@section('title', 'Edit Article')

@section('content')
<div class="container-fluid py-4" style="max-width: 800px;">
    <!-- Back Link -->
    <div class="mb-4">
        <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary-saas">
            <i class="bi bi-arrow-left"></i> Back to Articles
        </a>
    </div>

    <!-- Form card -->
    <div class="card border-0 shadow-sm p-4" style="border-radius: 24px; border: 1px solid #E5E7EB !important; background-color: #FFFFFF;">
        <h2 class="typo-h2 mb-2">Edit Analysis Article</h2>
        <p class="text-muted small mb-4">Update draft details or change live publishing settings.</p>

        <form action="{{ route('admin.articles.update', $article->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="title" class="form-label text-dark fw-semibold small">Article Title</label>
                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $article->title) }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="summary" class="form-label text-dark fw-semibold small">Brief Summary</label>
                <textarea name="summary" id="summary" class="form-control @error('summary') is-invalid @enderror" rows="2">{{ old('summary', $article->summary) }}</textarea>
                @error('summary')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="content" class="form-label text-dark fw-semibold small">Full Content</label>
                <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror" rows="10" required>{{ old('content', $article->content) }}</textarea>
                @error('content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="status" class="form-label text-dark fw-semibold small">Publishing Status</label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="draft" {{ old('status', $article->status) === 'draft' ? 'selected' : '' }}>Draft (Admin-Only Visibility)</option>
                    <option value="published" {{ old('status', $article->status) === 'published' ? 'selected' : '' }}>Published (Live to Feed)</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="pt-3 border-top mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary-saas">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Article</button>
            </div>
        </form>
    </div>
</div>
@endsection
