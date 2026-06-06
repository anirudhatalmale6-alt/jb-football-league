@extends('layouts.app')

@section('title', ($match->homeTeam->name ?? 'Home') . ' vs ' . ($match->awayTeam->name ?? 'Away'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-futbol text-success me-2"></i>{{ __('app.match_details') }}
    </h2>
    <div class="d-flex gap-2">
        @auth
            @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                <a href="{{ route('matches.edit', $match) }}" class="btn btn-outline-warning">
                    <i class="fas fa-edit me-1"></i> {{ __('app.edit') }}
                </a>
                <a href="{{ route('matches.lineup', $match) }}" class="btn btn-outline-info">
                    <i class="fas fa-list-ol me-1"></i> {{ __('app.lineup') }}
                </a>
                <a href="{{ route('matches.events', $match) }}" class="btn btn-outline-dark">
                    <i class="fas fa-futbol me-1"></i> {{ __('app.events') }}
                </a>
            @endif
        @endauth
        <a href="{{ route('matches.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> {{ __('app.back') }}
        </a>
    </div>
</div>

<!-- Match Header -->
<div class="card mb-4">
    <div class="card-body text-center py-4">
        <small class="text-muted d-block mb-2">
            {{ $match->competition->name ?? __('app.competition') }} &mdash; {{ __('app.matchday') }} {{ $match->matchday ?? '-' }}
        </small>
        <div class="row align-items-center justify-content-center">
            <div class="col-md-4 text-md-end">
                <h3 class="fw-bold mb-0">{{ $match->homeTeam->name ?? __('app.home_team') }}</h3>
                <small class="text-muted">{{ __('app.home') }}</small>
            </div>
            <div class="col-md-2 text-center">
                @if($match->status === 'completed' || $match->status === 'in_progress')
                    <div class="bg-dark text-white rounded px-3 py-2 d-inline-block">
                        <h2 class="fw-bold mb-0">{{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}</h2>
                    </div>
                @else
                    <div class="bg-light rounded px-3 py-2 d-inline-block">
                        <h3 class="fw-bold mb-0 text-muted">vs</h3>
                    </div>
                @endif
                <div class="mt-1">
                    @if($match->status === 'completed')
                        <span class="badge bg-secondary">{{ __('app.full_time') }}</span>
                    @elseif($match->status === 'in_progress')
                        <span class="badge bg-success">{{ __('app.in_progress') }}</span>
                    @elseif($match->status === 'scheduled')
                        <span class="badge bg-info">{{ __('app.scheduled') }}</span>
                    @elseif($match->status === 'postponed')
                        <span class="badge bg-warning text-dark">{{ __('app.postponed') }}</span>
                    @elseif($match->status === 'cancelled')
                        <span class="badge bg-danger">{{ __('app.cancelled') }}</span>
                    @endif
                </div>
            </div>
            <div class="col-md-4 text-md-start">
                <h3 class="fw-bold mb-0">{{ $match->awayTeam->name ?? __('app.away_team') }}</h3>
                <small class="text-muted">{{ __('app.away') }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Match Info -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>{{ __('app.match_information') }}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width: 150px;">{{ __('app.date_and_time') }}</th>
                        <td>{{ $match->match_date ? $match->match_date->format('d M Y, H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('app.venue') }}</th>
                        <td>{{ $match->venue ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('app.competition') }}</th>
                        <td>
                            @if($match->competition)
                                <a href="{{ route('competitions.show', $match->competition) }}">{{ $match->competition->name }}</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('app.matchday') }}</th>
                        <td>{{ $match->matchday ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width: 180px;">{{ __('app.referee') }}</th>
                        <td>{{ $match->referee ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('app.assistant_referee_1') }}</th>
                        <td>{{ $match->assistant_referee_1 ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('app.assistant_referee_2') }}</th>
                        <td>{{ $match->assistant_referee_2 ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('app.fourth_official') }}</th>
                        <td>{{ $match->fourth_official ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('app.match_commissioner') }}</th>
                        <td>{{ $match->match_commissioner ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Lineups -->
@php
    $homeLineup = $match->lineups->where('team_id', $match->home_team_id);
    $awayLineup = $match->lineups->where('team_id', $match->away_team_id);
    $homeStarters = $homeLineup->where('is_starting', true)->sortBy('jersey_number');
    $homeSubs = $homeLineup->where('is_starting', false)->sortBy('jersey_number');
    $awayStarters = $awayLineup->where('is_starting', true)->sortBy('jersey_number');
    $awaySubs = $awayLineup->where('is_starting', false)->sortBy('jersey_number');
@endphp

@if($homeLineup->isNotEmpty() || $awayLineup->isNotEmpty())
    <div class="row mb-4">
        <!-- Home Lineup -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-ol me-2"></i>{{ $match->homeTeam->name ?? __('app.home') }} {{ __('app.lineup') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($homeStarters->isNotEmpty())
                        <h6 class="fw-bold px-3 pt-3 mb-2">{{ __('app.starting_xi') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>{{ __('app.player') }}</th>
                                        <th>{{ __('app.position') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($homeStarters as $lineup)
                                        <tr>
                                            <td class="fw-bold">{{ $lineup->jersey_number }}</td>
                                            <td>{{ $lineup->player->name ?? '-' }}</td>
                                            <td>{{ ucfirst($lineup->position ?? '-') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    @if($homeSubs->isNotEmpty())
                        <h6 class="fw-bold px-3 pt-3 mb-2">{{ __('app.substitutes') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>{{ __('app.player') }}</th>
                                        <th>{{ __('app.position') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($homeSubs as $lineup)
                                        <tr>
                                            <td class="fw-bold">{{ $lineup->jersey_number }}</td>
                                            <td>{{ $lineup->player->name ?? '-' }}</td>
                                            <td>{{ ucfirst($lineup->position ?? '-') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    @if($homeLineup->isEmpty())
                        <div class="text-center text-muted py-4">
                            <p class="mb-0">Lineup not yet submitted.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Away Lineup -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-ol me-2"></i>{{ $match->awayTeam->name ?? __('app.away') }} {{ __('app.lineup') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($awayStarters->isNotEmpty())
                        <h6 class="fw-bold px-3 pt-3 mb-2">{{ __('app.starting_xi') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>{{ __('app.player') }}</th>
                                        <th>{{ __('app.position') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($awayStarters as $lineup)
                                        <tr>
                                            <td class="fw-bold">{{ $lineup->jersey_number }}</td>
                                            <td>{{ $lineup->player->name ?? '-' }}</td>
                                            <td>{{ ucfirst($lineup->position ?? '-') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    @if($awaySubs->isNotEmpty())
                        <h6 class="fw-bold px-3 pt-3 mb-2">{{ __('app.substitutes') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>{{ __('app.player') }}</th>
                                        <th>{{ __('app.position') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($awaySubs as $lineup)
                                        <tr>
                                            <td class="fw-bold">{{ $lineup->jersey_number }}</td>
                                            <td>{{ $lineup->player->name ?? '-' }}</td>
                                            <td>{{ ucfirst($lineup->position ?? '-') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    @if($awayLineup->isEmpty())
                        <div class="text-center text-muted py-4">
                            <p class="mb-0">Lineup not yet submitted.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Match Events Timeline -->
@if($match->events->isNotEmpty())
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>{{ __('app.match_events') }}</h5>
        </div>
        <div class="card-body">
            <div class="list-group list-group-flush">
                @foreach($match->events->sortBy('minute') as $event)
                    <div class="list-group-item d-flex align-items-center px-0">
                        <div class="me-3 text-center" style="min-width: 50px;">
                            <span class="badge bg-dark">
                                {{ $event->minute }}'{{ $event->extra_time_minute ? '+' . $event->extra_time_minute : '' }}
                            </span>
                        </div>
                        <div class="me-3">
                            @switch($event->event_type)
                                @case('goal')
                                    <i class="fas fa-futbol text-success fa-lg"></i>
                                    @break
                                @case('own_goal')
                                    <i class="fas fa-futbol text-danger fa-lg"></i>
                                    @break
                                @case('yellow_card')
                                    <i class="fas fa-square text-warning fa-lg"></i>
                                    @break
                                @case('red_card')
                                    <i class="fas fa-square text-danger fa-lg"></i>
                                    @break
                                @case('substitution_in')
                                @case('substitution_out')
                                    <i class="fas fa-exchange-alt text-info fa-lg"></i>
                                    @break
                                @case('penalty_scored')
                                    <i class="fas fa-futbol text-success fa-lg"></i>
                                    @break
                                @case('penalty_missed')
                                    <i class="fas fa-futbol text-muted fa-lg"></i>
                                    @break
                                @default
                                    <i class="fas fa-circle text-secondary fa-lg"></i>
                            @endswitch
                        </div>
                        <div class="flex-grow-1">
                            <strong>{{ $event->player->name ?? 'Unknown Player' }}</strong>
                            <span class="text-muted ms-1">({{ $event->team->name ?? '-' }})</span>
                            <br>
                            <small class="text-muted">
                                @switch($event->event_type)
                                    @case('goal')
                                        {{ __('app.goal') }}
                                        @break
                                    @case('own_goal')
                                        {{ __('app.own_goal') }}
                                        @break
                                    @case('yellow_card')
                                        {{ __('app.yellow_card') }}
                                        @break
                                    @case('red_card')
                                        {{ __('app.red_card') }}
                                        @break
                                    @case('substitution_in')
                                        {{ __('app.substitution_in') }}
                                        @break
                                    @case('substitution_out')
                                        {{ __('app.substitution_out') }}
                                        @break
                                    @case('penalty_scored')
                                        {{ __('app.penalty_scored') }}
                                        @break
                                    @case('penalty_missed')
                                        {{ __('app.penalty_missed') }}
                                        @break
                                    @default
                                        {{ ucfirst(str_replace('_', ' ', $event->event_type)) }}
                                @endswitch
                                @if($event->notes)
                                    &mdash; {{ $event->notes }}
                                @endif
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- PDF Downloads -->
<div class="card">
    <div class="card-body d-flex gap-3 justify-content-center">
        <a href="{{ route('matches.pdf.summary', $match) }}" class="btn btn-danger">
            <i class="fas fa-file-pdf me-1"></i> {{ __('app.download_match_summary_pdf') }}
        </a>
        <a href="{{ route('matches.pdf.teamsheet', $match) }}" class="btn btn-outline-danger">
            <i class="fas fa-file-pdf me-1"></i> {{ __('app.download_team_sheet_pdf') }}
        </a>
    </div>
</div>
@endsection
