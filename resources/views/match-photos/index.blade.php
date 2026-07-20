@extends('layouts.app')

@section('title', __('app.mdp_title'))

@section('content')
@php
    use App\Models\MatchDayPhoto;

    $cards = [
        [
            'category' => MatchDayPhoto::CATEGORY_HOME_XI,
            'team'     => $match->homeTeam->name ?? __('app.home_team'),
            'sub'      => __('app.mdp_home_team'),
            'icon'     => 'fa-shield-halved',
        ],
        [
            'category' => MatchDayPhoto::CATEGORY_AWAY_XI,
            'team'     => $match->awayTeam->name ?? __('app.away_team'),
            'sub'      => __('app.mdp_away_team'),
            'icon'     => 'fa-shield-halved',
        ],
        [
            'category' => MatchDayPhoto::CATEGORY_REFEREE_CAPTAINS,
            'team'     => __('app.mdp_referee_captains'),
            'sub'      => __('app.mdp_referee_captains_sub'),
            'icon'     => 'fa-user-tie',
        ],
    ];

    $uploadedCount = collect(MatchDayPhoto::CATEGORIES)->filter(fn($c) => isset($photos[$c]))->count();
    $total = count(MatchDayPhoto::CATEGORIES);
    $complete = $uploadedCount >= $total;
@endphp

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-camera-retro text-success me-2"></i>{{ __('app.mdp_title') }}
    </h2>
    <a href="{{ route('matches.show', $match->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>{{ __('app.back_to_match') }}
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

{{-- Match header --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="fw-bold">
            {{ $match->homeTeam->name ?? __('app.home') }}
            <span class="text-muted mx-1">vs</span>
            {{ $match->awayTeam->name ?? __('app.away') }}
        </div>
        <div class="text-muted small">
            {{ $match->competition->name ?? '' }}
            @if($match->match_date) &mdash; {{ $match->match_date->format('d M Y, h:i A') }} @endif
            @if($match->venue) &mdash; {{ $match->venue }} @endif
        </div>
    </div>
</div>

{{-- Progress + purpose note --}}
<div class="card mb-3 border-{{ $complete ? 'success' : 'warning' }}">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                @if($complete)
                    <span class="fw-bold text-success"><i class="fas fa-check-circle me-1"></i>{{ __('app.mdp_complete') }}</span>
                @else
                    <span class="fw-bold text-dark">
                        <i class="fas fa-images me-1"></i>{{ __('app.mdp_progress', ['done' => $uploadedCount, 'total' => $total]) }}
                    </span>
                @endif
            </div>
            <div style="min-width:180px;flex:1;max-width:320px;">
                <div class="progress" style="height:10px;">
                    <div class="progress-bar bg-{{ $complete ? 'success' : 'warning' }}" role="progressbar"
                         style="width: {{ $total ? ($uploadedCount / $total) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>
        @unless($complete)
            <div class="alert alert-warning py-2 px-3 mb-0 mt-3 small">
                <i class="fas fa-exclamation-circle me-1"></i>{{ __('app.mdp_incomplete_reminder') }}
                <ul class="mb-0 mt-1">
                    @foreach(MatchDayPhoto::CATEGORIES as $c)
                        @unless(isset($photos[$c]))
                            <li>{{ __('app.' . MatchDayPhoto::categoryLangKey($c)) }}</li>
                        @endunless
                    @endforeach
                </ul>
            </div>
        @endunless
        <div class="text-muted small mt-2">
            <i class="fas fa-lock me-1"></i>{{ __('app.mdp_private_note') }}
        </div>
    </div>
</div>

<div class="row g-3">
    @foreach($cards as $card)
        @php $photo = $photos[$card['category']] ?? null; @endphp
        <div class="col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas {{ $card['icon'] }} me-2"></i>{{ $card['sub'] }}</h6>
                    @if($photo)
                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>{{ __('app.mdp_uploaded') }}</span>
                    @else
                        <span class="badge bg-secondary">{{ __('app.mdp_not_uploaded') }}</span>
                    @endif
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="fw-semibold mb-2">{{ $card['team'] }}</div>

                    @if($photo)
                        <a href="{{ route('match-photos.file', [$match->id, $card['category']]) }}" target="_blank">
                            <img src="{{ route('match-photos.file', [$match->id, $card['category']]) }}"
                                 class="img-fluid rounded mb-2" style="width:100%;max-height:220px;object-fit:cover;"
                                 alt="{{ $card['sub'] }}">
                        </a>
                        <div class="small text-muted mb-2">
                            <i class="fas fa-user me-1"></i>{{ __('app.mdp_uploaded_by') }}:
                            {{ $photo->uploadedByUser->name ?? __('app.mdp_commissioner') }}<br>
                            @if($photo->uploaded_at)
                                <i class="fas fa-clock me-1"></i>{{ $photo->uploaded_at->format('d M Y, h:i A') }}
                            @endif
                        </div>

                        <div class="mt-auto d-flex gap-1 flex-wrap">
                            <a href="{{ route('match-photos.file', [$match->id, $card['category']]) }}" target="_blank"
                               class="btn btn-sm btn-outline-primary flex-grow-1">
                                <i class="fas fa-eye me-1"></i>{{ __('app.mdp_view') }}
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1"
                                    data-bs-toggle="collapse" data-bs-target="#up-{{ $card['category'] }}">
                                <i class="fas fa-sync-alt me-1"></i>{{ __('app.mdp_replace') }}
                            </button>
                            <form action="{{ route('match-photos.destroy', [$match->id, $card['category']]) }}" method="POST"
                                  onsubmit="return confirm('{{ __('app.mdp_confirm_remove') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('app.mdp_remove') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>

                        <div class="collapse mt-2" id="up-{{ $card['category'] }}">
                            <form action="{{ route('match-photos.upload', $match->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="category" value="{{ $card['category'] }}">
                                <input type="file" name="photo" accept="image/*" capture="environment"
                                       class="form-control form-control-sm mb-2" required>
                                <button class="btn btn-sm btn-success w-100"><i class="fas fa-upload me-1"></i>{{ __('app.mdp_replace') }}</button>
                            </form>
                        </div>
                    @else
                        <div class="alert alert-light border text-center text-muted py-4 mb-2">
                            <i class="fas fa-image fa-2x mb-2 d-block opacity-50"></i>
                            <span class="small">{{ __('app.mdp_not_uploaded') }}</span>
                        </div>
                        <form action="{{ route('match-photos.upload', $match->id) }}" method="POST" enctype="multipart/form-data" class="mt-auto">
                            @csrf
                            <input type="hidden" name="category" value="{{ $card['category'] }}">
                            <input type="file" name="photo" accept="image/*" capture="environment"
                                   class="form-control form-control-sm mb-2" required>
                            <button class="btn btn-success w-100"><i class="fas fa-upload me-1"></i>{{ __('app.mdp_upload') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
