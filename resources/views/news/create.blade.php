@extends('layouts.app')

@section('title', 'Create News')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2><i class="fas fa-plus-circle me-2"></i> Create News Article</h2>
            <hr>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('news.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold">Title / Tajuk</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Content / Kandungan</label>
                    <textarea name="content" class="form-control" rows="12" required>{{ old('content') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Image / Gambar</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <small class="text-muted">Max 5MB. Recommended: 1200x630px</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Publish
                    </button>
                    <a href="{{ route('news.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection