@section('og_title', $article->title . ' | Liga JBFA 2026')
@section('og_description', Str::limit(strip_tags($article->content), 200))
@section('og_image', $article->image ? asset('storage/' . $article->image) : asset('images/og-image.png'))
@section('og_url', route('news.show', $article->slug))
@section('og_type', 'article')

@extends('layouts.app')

@section('title', $article->title)

@section('content')
<div class="container-fluid px-0">
    @if($article->image)
        <div class="position-relative" style="max-height: 500px; overflow: hidden;">
            <img src="{{ asset('storage/' . $article->image) }}" class="w-100" alt="{{ $article->title }}" style="object-fit: cover; min-height: 300px; max-height: 500px; filter: brightness(0.85);">
            <div class="position-absolute bottom-0 start-0 end-0 p-4" style="background: linear-gradient(transparent, rgba(0,0,0,0.8));">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <h1 class="text-white mb-2" style="font-weight: 800; font-size: 2rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">{{ $article->title }}</h1>
                            <div class="text-white-50">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $article->published_at ? $article->published_at->format('d M Y, h:i A') : $article->created_at->format('d M Y, h:i A') }}
                                @if($article->author)
                                    <span class="ms-3"><i class="fas fa-user me-1"></i> {{ $article->author->name }}</span>
                                @endif
                                @if($article->show_views_public || (Auth::check() && (Auth::user()->isSuper() || Auth::user()->isLeagueAdmin())))
                                    <span class="ms-3"><i class="fas fa-eye me-1"></i> {{ number_format($article->views) }} views</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('news.index') }}"><i class="fas fa-newspaper me-1"></i> {{ __('app.news') }}</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($article->title, 50) }}</li>
                </ol>
            </nav>

            @auth
                @if(Auth::user()->isSuper() || Auth::user()->isLeagueAdmin())
                    <div class="mb-3">
                        <a href="{{ route('news.edit', $article->id) }}" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <form action="{{ route('news.destroy', $article->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this article?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i> Delete</button>
                        </form>
                        @if($article->status === 'draft')
                            <span class="badge bg-secondary ms-2">Draft</span>
                        @endif
                    </div>
                @endif
            @endauth

            @if(!$article->image)
                <h1 class="mb-3" style="font-weight: 800;">{{ $article->title }}</h1>
                <div class="text-muted mb-4">
                    <i class="fas fa-calendar me-1"></i>
                    {{ $article->published_at ? $article->published_at->format('d M Y, h:i A') : $article->created_at->format('d M Y, h:i A') }}
                    @if($article->author)
                        <span class="ms-3"><i class="fas fa-user me-1"></i> {{ $article->author->name }}</span>
                    @endif
                    @if($article->show_views_public || (Auth::check() && (Auth::user()->isSuper() || Auth::user()->isLeagueAdmin())))
                        <span class="ms-3"><i class="fas fa-eye me-1"></i> {{ number_format($article->views) }} views</span>
                    @endif
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="article-content" style="font-size: 1.1rem; line-height: 1.9; white-space: pre-line;">{{ $article->content }}</div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="{{ route('news.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to News
                </a>
                <div class="d-flex gap-2">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('news.show', $article->slug)) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Share on Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://wa.me/?text={{ urlencode($article->title . ' ' . route('news.show', $article->slug)) }}" target="_blank" class="btn btn-sm btn-outline-success" title="Share on WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            @if($related->count() > 0)
                <hr class="my-5">
                <h4 class="mb-3"><i class="fas fa-newspaper me-2"></i> More News</h4>
                <div class="row g-3">
                    @foreach($related as $rel)
                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm border-0 overflow-hidden">
                                @if($rel->image)
                                    <img src="{{ asset('storage/' . $rel->image) }}" class="card-img-top" alt="{{ $rel->title }}" style="height: 150px; object-fit: cover;">
                                @endif
                                <div class="card-body">
                                    <h6><a href="{{ route('news.show', $rel->slug) }}" class="text-decoration-none text-dark">{{ Str::limit($rel->title, 60) }}</a></h6>
                                    <small class="text-muted">{{ $rel->published_at ? $rel->published_at->format('d M Y') : '' }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection