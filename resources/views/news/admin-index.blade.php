@extends('layouts.app')

@section('title', 'Manage News')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-newspaper me-2"></i> Manage News</h2>
        <a href="{{ route('news.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> Add News
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($news as $article)
                    <tr>
                        <td>
                            @if($article->image)
                                <img src="{{ asset('storage/' . $article->image) }}" class="rounded" style="width: 60px; height: 40px; object-fit: cover;">
                            @else
                                <span class="text-muted"><i class="fas fa-image"></i></span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('news.show', $article->slug) }}" class="text-decoration-none">{{ Str::limit($article->title, 60) }}</a>
                        </td>
                        <td>
                            @if($article->status === 'published')
                                <span class="badge bg-success">Published</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </td>
                        <td><i class="fas fa-eye text-muted me-1"></i> {{ number_format($article->views) }}</td>
                        <td>{{ $article->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('news.edit', $article->id) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('news.destroy', $article->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this article?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No news articles yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $news->links() }}
</div>
@endsection