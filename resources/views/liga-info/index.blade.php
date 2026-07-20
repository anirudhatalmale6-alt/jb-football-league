@extends('layouts.app')

@section('title', __('app.liga_info'))

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h2 class="fw-bold">
            <i class="fas fa-circle-info text-success me-2"></i>{{ __('app.liga_info') }}
        </h2>
        <p class="text-muted">{{ __('app.liga_info_desc') }}</p>
    </div>

    <!-- League Manual Download -->
    <div class="card mb-4 shadow-sm border-primary">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
            <div>
                <h5 class="fw-bold mb-1"><i class="fas fa-book text-primary me-2"></i>{{ __('app.league_manual') }}</h5>
                <p class="text-muted mb-0 small">{{ __('app.league_manual_desc') }}</p>
            </div>
            <a href="{{ asset('documents/MANUAL_LIGA_JBFA_2026.pdf') }}" class="btn btn-primary" target="_blank" download>
                <i class="fas fa-file-pdf me-2"></i>{{ __('app.download_manual') }}
            </a>
        </div>
    </div>

    <!-- Tentative Schedule -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>{{ __('app.tentative_schedule') }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px" class="text-center">#</th>
                            <th>{{ __('app.date') }}</th>
                            <th>{{ __('app.event') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center"><span class="badge bg-success rounded-circle" style="width:30px;height:30px;line-height:22px;">1</span></td>
                            <td class="fw-semibold">12 Jun 2026</td>
                            <td>{{ __('app.schedule_briefing') }}</td>
                        </tr>
                        <tr>
                            <td class="text-center"><span class="badge bg-primary rounded-circle" style="width:30px;height:30px;line-height:22px;">2</span></td>
                            <td class="fw-semibold">22 Jun 2026</td>
                            <td>{{ __('app.schedule_confirm') }}</td>
                        </tr>
                        <tr>
                            <td class="text-center"><span class="badge bg-warning text-dark rounded-circle" style="width:30px;height:30px;line-height:22px;">3</span></td>
                            <td class="fw-semibold">12 Jul 2026</td>
                            <td>{{ __('app.schedule_payment') }}</td>
                        </tr>
                        <tr>
                            <td class="text-center"><span class="badge bg-info text-dark rounded-circle" style="width:30px;height:30px;line-height:22px;">4</span></td>
                            <td class="fw-semibold">17 Jul 2026</td>
                            <td>{{ __('app.schedule_draw') }}</td>
                        </tr>
                        <tr>
                            <td class="text-center"><span class="badge bg-secondary rounded-circle" style="width:30px;height:30px;line-height:22px;">5</span></td>
                            <td class="fw-semibold">20 Jul 2026</td>
                            <td>{{ __('app.schedule_registration') }}</td>
                        </tr>
                        <tr>
                            <td class="text-center"><span class="badge bg-danger rounded-circle" style="width:30px;height:30px;line-height:22px;">6</span></td>
                            <td class="fw-semibold">24 Jul 2026</td>
                            <td><strong>{{ __('app.schedule_sumbangsih') }}</strong></td>
                        </tr>
                        <tr class="table-success">
                            <td class="text-center"><span class="badge bg-dark rounded-circle" style="width:30px;height:30px;line-height:22px;">7</span></td>
                            <td class="fw-semibold">1 Aug 2026</td>
                            <td><strong>{{ __('app.schedule_league_kickoff') }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Upcoming Fixtures -->
    @if($upcomingMatches->isNotEmpty())
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-futbol me-2"></i>{{ __('app.upcoming_fixtures') }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($upcomingMatches as $match)
                    <div class="col-md-6">
                        <div class="card border-start border-success border-4 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted"><i class="fas fa-calendar me-1"></i>{{ $match->match_date ? $match->match_date->format('d M Y') : '-' }}</small>
                                    <small class="text-muted"><i class="fas fa-clock me-1"></i>{{ $match->match_date ? $match->match_date->format('H:i') : '-' }}</small>
                                </div>
                                <div class="text-center mb-2">
                                    <span class="fw-bold fs-5">{{ $match->homeTeam->name ?? '-' }}</span>
                                    <span class="badge bg-dark mx-2">VS</span>
                                    <span class="fw-bold fs-5">{{ $match->awayTeam->name ?? '-' }}</span>
                                </div>
                                <div class="text-center">
                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $match->venue ?? '-' }}</small>
                                    <br>
                                    <span class="badge bg-success bg-opacity-25 text-success mt-1">{{ $match->competition->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Competitions -->
    <h4 class="fw-bold mb-3"><i class="fas fa-trophy text-success me-2"></i>{{ __('app.competitions') }}</h4>
    <div class="row g-4">
        @forelse($competitions as $competition)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    @if($competition->logo)
                        <div class="text-center pt-4">
                            <img src="{{ asset('storage/' . $competition->logo) }}" alt="{{ $competition->name }}" style="height: 80px; object-fit: contain;">
                        </div>
                    @endif
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-center">{{ $competition->name }}</h5>
                        @if($competition->description)
                            <p class="text-muted small">{{ $competition->description }}</p>
                        @endif
                        <table class="table table-sm table-borderless mb-0 mt-3">
                            @if($competition->start_date)
                                <tr>
                                    <td class="text-muted" style="width: 120px;"><i class="fas fa-calendar me-1"></i> {{ __('app.start_date') }}</td>
                                    <td class="fw-semibold">{{ \Carbon\Carbon::parse($competition->start_date)->format('d M Y') }}</td>
                                </tr>
                            @endif
                            @if($competition->end_date)
                                <tr>
                                    <td class="text-muted"><i class="fas fa-calendar-check me-1"></i> {{ __('app.end_date') }}</td>
                                    <td class="fw-semibold">{{ \Carbon\Carbon::parse($competition->end_date)->format('d M Y') }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted"><i class="fas fa-shield-halved me-1"></i> {{ __('app.teams') }}</td>
                                <td class="fw-semibold">{{ $competition->teams_count }}</td>
                            </tr>
                            @if($competition->groups_count > 0)
                                <tr>
                                    <td class="text-muted"><i class="fas fa-layer-group me-1"></i> {{ __('app.groups') }}</td>
                                    <td class="fw-semibold">{{ $competition->groups_count }}</td>
                                </tr>
                            @endif
                            @if($competition->format)
                                <tr>
                                    <td class="text-muted"><i class="fas fa-sitemap me-1"></i> {{ __('app.format') }}</td>
                                    <td class="fw-semibold">{{ ucfirst($competition->format) }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    <div class="card-footer bg-transparent text-center">
                        <a href="{{ route('competitions.show', $competition) }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-eye me-1"></i> {{ __('app.view_details') }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">
                <i class="fas fa-circle-info fa-3x mb-3 d-block"></i>
                <p class="fs-5">{{ __('app.no_competitions') }}</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
