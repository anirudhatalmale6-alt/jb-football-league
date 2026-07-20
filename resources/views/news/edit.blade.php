@extends('layouts.app')

@section('title', 'Edit News')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2><i class="fas fa-edit me-2"></i> Edit News Article</h2>
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

            <form action="{{ route('news.update', $article->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-bold">Title / Tajuk</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $article->title) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Content / Kandungan</label>
                    <textarea name="content" class="form-control" rows="12" required>{{ old('content', $article->content) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Image / Gambar</label>
                    @if($article->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $article->image) }}" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    @endif
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <small class="text-muted">Leave empty to keep current image</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="published" {{ $article->status === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ $article->status === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Update
                    </button>
                    <a href="{{ route('news.show', $article->slug) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection