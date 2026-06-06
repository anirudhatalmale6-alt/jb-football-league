@extends('layouts.app')

@section('title', __('app.dashboard'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-tachometer-alt text-success me-2"></i>{{ __('app.dashboard') }}
    </h2>
    <span class="text-muted">{{ __('app.welcome_back') }}, {{ Auth::user()->name }}</span>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <!-- Total Competitions -->
    <div class="col-md-3 col-sm-6">
        <div class="card h-100 border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem;">
                            {{ __('app.competitions') }}
                        </h6>
                        <h2 class="fw-bold mb-0">{{ $competitionCount }}</h2>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-trophy fa-lg text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Teams -->
    <div class="col-md-3 col-sm-6">
        <div class="card h-100 border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem;">
                            {{ __('app.teams') }}
                        </h6>
                        <h2 class="fw-bold mb-0">{{ $teamCount }}</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-shield-halved fa-lg text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Players -->
    <div class="col-md-3 col-sm-6">
        <div class="card h-100 border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem;">
                            {{ __('app.players') }}
                        </h6>
                        <h2 class="fw-bold mb-0">{{ $playerCount }}</h2>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-users fa-lg text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Matches -->
    <div class="col-md-3 col-sm-6">
        <div class="card h-100 border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem;">
                            {{ __('app.upcoming_matches') }}
                        </h6>
                        <h2 class="fw-bold mb-0">{{ $upcomingMatchCount }}</h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-calendar-days fa-lg text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Matches & Standings -->
<div class="row g-4">
    <!-- Recent Matches -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clock-rotate-left me-2"></i>{{ __('app.recent_matches') }}
                </h5>
                <a href="{{ route('matches.index') }}" class="btn btn-sm btn-outline-light">
                    {{ __('app.view_all') }}
                </a>
            </div>
            <div class="card-body p-0">
                @if($recentMatches->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-calendar-xmark fa-3x mb-3 d-block"></i>
                        No recent matches found.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('app.date') }}</th>
                                    <th>{{ __('app.home') }}</th>
                                    <th class="text-center">{{ __('app.score') }}</th>
                                    <th>{{ __('app.away') }}</th>
                                    <th>{{ __('app.competition') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentMatches as $match)
                                    <tr>
                                        <td class="text-muted">
                                            {{ \Carbon\Carbon::parse($match->match_date)->format('d M Y') }}
                                        </td>
                                        <td class="fw-semibold">{{ $match->homeTeam->name }}</td>
                                        <td class="text-center">
                                            @if($match->status === 'completed')
                                                <span class="badge bg-dark fs-6">
                                                    {{ $match->home_score }} - {{ $match->away_score }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($match->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-semibold">{{ $match->awayTeam->name }}</td>
                                        <td>
                                            <small class="text-muted">{{ $match->competition->name ?? '-' }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- League Standings Summary -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-ranking-star me-2"></i>{{ __('app.league_standings') }}
                </h5>
                <a href="{{ route('standings.index') }}" class="btn btn-sm btn-outline-light">
                    {{ __('app.full_table') }}
                </a>
            </div>
            <div class="card-body p-0">
                @if($standings->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-table fa-3x mb-3 d-block"></i>
                        No standings data available yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>{{ __('app.team') }}</th>
                                    <th class="text-center">{{ __('app.played') }}</th>
                                    <th class="text-center">{{ __('app.won') }}</th>
                                    <th class="text-center">{{ __('app.drawn') }}</th>
                                    <th class="text-center">{{ __('app.lost') }}</th>
                                    <th class="text-center fw-bold">{{ __('app.points') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($standings as $index => $standing)
                                    <tr>
                                        <td>
                                            @if($index < 3)
                                                <span class="badge bg-success">{{ $index + 1 }}</span>
                                            @else
                                                <span class="text-muted">{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-semibold">{{ $standing->team->name }}</td>
                                        <td class="text-center">{{ $standing->played }}</td>
                                        <td class="text-center">{{ $standing->won }}</td>
                                        <td class="text-center">{{ $standing->drawn }}</td>
                                        <td class="text-center">{{ $standing->lost }}</td>
                                        <td class="text-center fw-bold">{{ $standing->points }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
