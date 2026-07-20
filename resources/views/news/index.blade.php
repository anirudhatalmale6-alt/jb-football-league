@extends('layouts.app')

@section('title', __('app.news'))

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-newspaper me-2 text-success"></i> {{ __('app.news') }}</h2>
            <p class="text-muted mb-0">Berita & pengumuman terkini Liga JBFA 2026</p>
        </div>
        @auth
            @if(Auth::user()->isSuper() || Auth::user()->isLeagueAdmin())
                <a href="{{ route('news.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> {{ __('app.add_news') }}
                </a>
            @endif
        @endauth
    </div>

    @if($news->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-newspaper text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-muted">No news articles yet</h4>
        </div>
    @else
        @if($news->currentPage() == 1 && $news->first())
            @php $featured = $news->first(); @endphp
            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <div class="row g-0">
                    @if($featured->image)
                        <div class="col-md-6">
                            <a href="{{ route('news.show', $featured->slug) }}">
                                <img src="{{ asset('storage/' . $featured->image) }}" class="w-100 h-100" alt="{{ $featured->title }}" style="object-fit: cover; min-height: 300px;">
                            </a>
                        </div>
                    @endif
                    <div class="col-md-{{ $featured->image ? '6' : '12' }}">
                        <div class="card-body d-flex flex-column justify-content-center p-4 p-md-5" style="min-height: 300px;">
                            <span class="badge bg-success mb-2" style="width: fit-content;">Latest</span>
                            <h3 class="card-title mb-3">
                                <a href="{{ route('news.show', $featured->slug) }}" class="text-decoration-none text-dark" style="font-weight: 700;">
                                    {{ $featured->title }}
                                </a>
                            </h3>
                            <p class="card-text text-muted mb-3" style="font-size: 1.05rem;">
                                {{ Str::limit(strip_tags($featured->content), 200) }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $featured->published_at ? $featured->published_at->format('d M Y') : $featured->created_at->format('d M Y') }}
                                    @if($featured->show_views_public || (Auth::check() && (Auth::user()->isSuper() || Auth::user()->isLeagueAdmin())))
                                        <span class="ms-3"><i class="fas fa-eye me-1"></i> {{ number_format($featured->views) }}</span>
                                    @endif
                                </small>
                                <a href="{{ route('news.show', $featured->slug) }}" class="btn btn-success">
                                    Read More <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($news->count() > 1 || $news->currentPage() > 1)
            <div class="row g-4">
                @foreach($news as $article)
                    @if($news->currentPage() == 1 && $loop->first)
                        @continue
                    @endif
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0 overflow-hidden">
                            @if($article->image)
                                <a href="{{ route('news.show', $article->slug) }}">
                                    <img src="{{ asset('storage/' . $article->image) }}" class="card-img-top" alt="{{ $article->title }}" style="height: 220px; object-fit: cover;">
                                </a>
                            @else
                                <a href="{{ route('news.show', $article->slug) }}" class="d-flex align-items-center justify-content-center bg-dark text-white" style="height: 220px;">
                                    <i class="fas fa-newspaper" style="font-size: 3rem; opacity: 0.5;"></i>
                                </a>
                            @endif
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">
                                    <a href="{{ route('news.show', $article->slug) }}" class="text-decoration-none text-dark" style="font-weight: 600;">
                                        {{ Str::limit($article->title, 80) }}
                                    </a>
                                </h5>
                                <p class="card-text text-muted small flex-grow-1">
                                    {{ Str::limit(strip_tags($article->content), 120) }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $article->published_at ? $article->published_at->format('d M Y') : $article->created_at->format('d M Y') }}
                                        @if($article->show_views_public || (Auth::check() && (Auth::user()->isSuper() || Auth::user()->isLeagueAdmin())))
                                            <span class="ms-2"><i class="fas fa-eye me-1"></i> {{ number_format($article->views) }}</span>
                                        @endif
                                    </small>
                                    <a href="{{ route('news.show', $article->slug) }}" class="btn btn-sm btn-outline-success">
                                        Read <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="d-flex justify-content-center mt-4">
            {{ $news->links() }}
        </div>
    @endif
</div>
@endsection