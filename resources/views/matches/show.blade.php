@extends('layouts.app')

@section('title', ($match->homeTeam->name ?? 'Home') . ' vs ' . ($match->awayTeam->name ?? 'Away'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-futbol text-success me-2"></i>{{ __('app.match_details') }}
    </h2>
    <div class="d-flex gap-2 flex-wrap">
        @auth
            @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                <a href="{{ route('matches.edit', $match) }}" class="btn btn-outline-warning btn-sm">
                    <i class="fas fa-edit me-1"></i>{{ __('app.edit') }}
                </a>
            @endif
        @endauth
        <a href="{{ route('matches.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>{{ __('app.back') }}
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<!-- Match Header -->
<div class="card mb-3 {{ $match->isLive() ? 'border-success border-2' : '' }}">
    <div class="card-body text-center py-3">
        <small class="text-muted d-block mb-1">
            {{ $match->competition->name ?? __('app.competition') }} &mdash; {{ __('app.matchday') }} {{ $match->matchday ?? '-' }}
        </small>

        @if($match->isLive())
            <div class="mb-2">
                <span class="badge bg-danger fs-6 px-3 py-2 pulse-live"
                      id="liveBadge"
                      data-status="{{ $match->status }}"
                      data-live-start="{{ optional($match->live_started_at)->toIso8601String() }}"
                      data-secondhalf-start="{{ optional($match->second_half_at)->toIso8601String() }}"
                      data-half="{{ $match->halfDuration() }}"
                      data-full="{{ $match->matchDuration() }}"
                      data-fhstop="{{ (int) ($match->first_half_stoppage ?? 0) }}"
                      data-shstop="{{ (int) ($match->second_half_stoppage ?? 0) }}">
                    <i class="fas fa-circle me-1" style="font-size:8px;"></i> LIVE <span id="liveMinute">{{ $match->match_minute }}</span>
                </span>
            </div>
        @elseif($match->status === 'half_time')
            <div class="mb-2"><span class="badge bg-warning text-dark fs-6 px-3 py-2">HALF TIME</span></div>
        @endif

        <div class="row align-items-center justify-content-center">
            <div class="col-5 col-md-4 text-end">
                @if($match->homeTeam && $match->homeTeam->logo)
                    <img src="{{ asset('storage/' . $match->homeTeam->logo) }}" alt="" style="height:40px;" class="mb-1 d-block d-md-inline ms-md-auto">
                @endif
                <h4 class="fw-bold mb-0">{{ $match->homeTeam->name ?? __('app.home_team') }}</h4>
                <small class="text-muted">{{ __('app.home') }}</small>
            </div>
            <div class="col-2 text-center">
                @if($match->status !== 'scheduled')
                    <div class="{{ $match->isLive() ? 'bg-success' : 'bg-dark' }} text-white rounded px-2 py-1 d-inline-block">
                        <h2 class="fw-bold mb-0" id="liveScore">{{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}</h2>
                    </div>
                @else
                    <div class="bg-light rounded px-2 py-1 d-inline-block">
                        <h3 class="fw-bold mb-0 text-muted">vs</h3>
                    </div>
                @endif
                <div class="mt-1">
                    @if($match->status === 'scheduled')
                        <span class="badge bg-info">{{ __('app.scheduled') }}</span>
                    @elseif($match->status === 'full_time' || $match->status === 'completed')
                        <span class="badge bg-secondary">{{ __('app.full_time_label') }}</span>
                    @elseif($match->status === 'closed')
                        <span class="badge bg-dark"><i class="fas fa-lock me-1"></i>{{ __('app.match_closed') }}</span>
                    @elseif($match->status === 'postponed')
                        <span class="badge bg-warning text-dark">{{ __('app.postponed') }}</span>
                    @endif
                </div>
            </div>
            <div class="col-5 col-md-4 text-start">
                @if($match->awayTeam && $match->awayTeam->logo)
                    <img src="{{ asset('storage/' . $match->awayTeam->logo) }}" alt="" style="height:40px;" class="mb-1 d-block d-md-inline">
                @endif
                <h4 class="fw-bold mb-0">{{ $match->awayTeam->name ?? __('app.away_team') }}</h4>
                <small class="text-muted">{{ __('app.away') }}</small>
            </div>
        </div>

        <div class="mt-2 text-muted small">
            <i class="fas fa-calendar me-1"></i>{{ $match->match_date ? $match->match_date->format('d M Y, H:i') : '-' }}
            &bull;
            <i class="fas fa-map-marker-alt me-1"></i>{{ $match->venue ?? '-' }}
        </div>
    </div>
</div>

@php
    $viewer = auth()->user();
    $canOperate = $viewer && $match->canOperateBy($viewer);
    $canEditMatch = $viewer && $match->canEditBy($viewer);
    $matchLocked = $match->isLocked();
@endphp

<!-- Match Control Panel (Admin/Commissioner only) -->
@auth
@if($canOperate)
<div class="card mb-3 border-primary">
    <div class="card-header bg-primary text-white py-2">
        <h6 class="mb-0"><i class="fas fa-gamepad me-2"></i>{{ __('app.match_control') }}</h6>
    </div>
    <div class="card-body py-2">
        @if($matchLocked)
            <div class="alert alert-dark mb-2 py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span>
                    <i class="fas fa-lock me-2"></i><strong>{{ __('app.match_locked_banner') }}</strong>
                    @if($match->final_submitted_at)
                        <span class="ms-2 small">
                            {{ __('app.submitted_by') }}: {{ optional($match->finalSubmittedBy)->name ?? '-' }},
                            {{ \App\Support\Tz::myt($match->final_submitted_at, 'd M Y, H:i') }}
                            @if($match->final_minute) &bull; {{ $match->final_minute }}' @endif
                        </span>
                    @endif
                </span>
                @if($viewer->isSuper() || $viewer->isHeadMatchCommissioner())
                    <form method="POST" action="{{ route('matches.unlock', $match) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Unlock this match report? This will revert status to Full Time.')">
                            <i class="fas fa-unlock me-1"></i>{{ __('app.unlock_match') }}
                        </button>
                    </form>
                @endif
            </div>
        @endif
        <div class="d-flex flex-wrap gap-2 align-items-center {{ $matchLocked ? 'opacity-50' : '' }}">
            @if($match->status === 'scheduled')
                <form method="POST" action="{{ route('matches.update-status', $match) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="status" value="live">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Start the match?')">
                        <i class="fas fa-play me-1"></i>{{ __('app.go_live') }}
                    </button>
                </form>
            @endif
            @if($match->status === 'live')
                <form method="POST" action="{{ route('matches.update-status', $match) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="status" value="half_time">
                    <button type="submit" class="btn btn-warning"><i class="fas fa-pause me-1"></i>{{ __('app.half_time_btn') }}</button>
                </form>
            @endif
            @if($match->status === 'half_time')
                <form method="POST" action="{{ route('matches.update-status', $match) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="status" value="second_half">
                    <button type="submit" class="btn btn-success"><i class="fas fa-play me-1"></i>{{ __('app.start_2nd_half') }}</button>
                </form>
            @endif
            @if($match->status === 'second_half')
                <form method="POST" action="{{ route('matches.update-status', $match) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="status" value="full_time">
                    <button type="submit" class="btn btn-dark" onclick="return confirm('End the match?')">
                        <i class="fas fa-flag-checkered me-1"></i>{{ __('app.full_time_btn') }}
                    </button>
                </form>
            @endif
            @if($canEditMatch && in_array($match->status, ['live','half_time','second_half','full_time']))
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#submitFinalModal">
                    <i class="fas fa-flag-checkered me-1"></i>{{ __('app.submit_final_report') }}
                </button>
            @endif

            @if($canEditMatch && $match->status !== 'scheduled')
                <span class="border-start ps-2 ms-1"></span>
                <form method="POST" action="{{ route('matches.update-score', $match) }}" class="d-inline d-flex align-items-center gap-1">
                    @csrf
                    <input type="number" name="home_score" value="{{ $match->home_score ?? 0 }}" class="form-control form-control-sm" min="0" style="width:55px;">
                    <span class="fw-bold">-</span>
                    <input type="number" name="away_score" value="{{ $match->away_score ?? 0 }}" class="form-control form-control-sm" min="0" style="width:55px;">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i></button>
                </form>
            @endif
        </div>
        @if($match->live_started_at)
            <div class="mt-1 text-muted small">
                <i class="fas fa-clock me-1"></i>{{ __('app.started_at') }}: {{ $match->live_started_at->format('H:i') }}
                @if($match->half_time_at) | HT: {{ $match->half_time_at->format('H:i') }} @endif
                @if($match->second_half_at) | 2H: {{ $match->second_half_at->format('H:i') }} @endif
                @if($match->full_time_at) | FT: {{ $match->full_time_at->format('H:i') }} @endif
            </div>
        @endif
    </div>
</div>

@auth
@if($canOperate)
@include('matches.partials.prematch-checklist')
@endif
@endauth

<!-- Match Day Photos (directly below Pre-Match Checklist, per match-day workflow) -->
@auth
@if($canOperate)
@include('matches.partials.match-day-photos-summary')
@endif
@endauth

<!-- Quick Match Event Update (embedded) -->
@if($canEditMatch && $match->status !== 'scheduled')
<div class="card mb-3">
    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#quickEventPanel" role="button">
        <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>{{ __('app.quick_event_update') }}</h6>
        <i class="fas fa-chevron-down" id="eventPanelArrow"></i>
    </div>
    <div class="collapse show" id="quickEventPanel">
        <div class="card-body py-2">
            <div class="row">
                <!-- Home Team -->
                <div class="col-md-6 mb-2 mb-md-0">
                    <div class="border-start border-success border-3 ps-2 mb-2">
                        <strong class="text-success"><i class="fas fa-home me-1"></i>{{ $match->homeTeam->name ?? 'Home' }}</strong>
                    </div>
                    <form method="POST" action="{{ route('matches.events.store', $match) }}" id="homeForm">
                        @csrf
                        <input type="hidden" name="team_id" value="{{ $match->home_team_id }}">
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <button type="button" class="btn btn-sm btn-outline-success quick-event" data-type="goal" data-form="homeForm"><i class="fas fa-futbol"></i> Goal</button>
                            <button type="button" class="btn btn-sm btn-outline-warning quick-event" data-type="yellow_card" data-form="homeForm"><span style="display:inline-block;width:8px;height:12px;background:#ffc107;border-radius:1px;"></span> Yellow</button>
                            <button type="button" class="btn btn-sm btn-outline-danger quick-event" data-type="red_card" data-form="homeForm"><span style="display:inline-block;width:8px;height:12px;background:#dc3545;border-radius:1px;"></span> Red</button>
                            <button type="button" class="btn btn-sm btn-outline-primary quick-event" data-type="substitution" data-form="homeForm"><i class="fas fa-exchange-alt"></i> Sub</button>
                            <button type="button" class="btn btn-sm btn-outline-dark quick-event" data-type="penalty_scored" data-form="homeForm">Pen</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-event" data-type="own_goal" data-form="homeForm">OG</button>
                        </div>
                        <div class="row g-1 mb-1">
                            <div class="col-5">
                                <select name="event_type" class="form-select form-select-sm" id="homeEventType" required>
                                    <option value="goal">Goal</option>
                                    <option value="penalty_scored">Penalty</option>
                                    <option value="own_goal">Own Goal</option>
                                    <option value="yellow_card">Yellow</option>
                                    <option value="red_card">Red</option>
                                    <option value="substitution">Sub</option>
                                    <option value="penalty_missed">Pen Miss</option>
                                    <option value="injury">Injury</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <select name="player_id" class="form-select form-select-sm" required>
                                    <option value="">-- Player --</option>
                                    @foreach($homePlayers as $p)
                                        <option value="{{ $p->id }}">{{ $p->jersey_number }} {{ $p->name }}{{ $p->is_u23 ? " [U23]" : "" }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <div class="input-group input-group-sm">
                                    <input type="number" name="minute" class="form-control" min="1" max="120" placeholder="Min" required>
                                    <input type="number" name="extra_time_minute" class="form-control" min="1" placeholder="+" style="max-width:45px;">
                                </div>
                            </div>
                        </div>
                        <div class="home-player-out-label mb-0" style="display:none;">
                            <label class="small text-danger fw-bold mb-0"><i class="fas fa-arrow-down text-danger me-1"></i>Player Out (Keluar)</label>
                        </div>
                        <div class="home-sub-field mb-1" style="display:none;">
                            <label class="small text-success fw-bold mb-0"><i class="fas fa-arrow-up text-success me-1"></i>Player In (Masuk)</label>
                            <select name="related_player_id" class="form-select form-select-sm">
                                <option value="">-- Select Player In --</option>
                                @foreach($homePlayers as $p)
                                    <option value="{{ $p->id }}">{{ $p->jersey_number }} {{ $p->name }}{{ $p->is_u23 ? " [U23]" : "" }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex gap-1">
                            <input type="text" name="notes" class="form-control form-control-sm flex-grow-1" placeholder="{{ __('app.notes') }}">
                            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-plus me-1"></i>Add</button>
                        </div>
                    </form>
                </div>

                <!-- Away Team -->
                <div class="col-md-6">
                    <div class="border-start border-primary border-3 ps-2 mb-2">
                        <strong class="text-primary"><i class="fas fa-plane me-1"></i>{{ $match->awayTeam->name ?? 'Away' }}</strong>
                    </div>
                    <form method="POST" action="{{ route('matches.events.store', $match) }}" id="awayForm">
                        @csrf
                        <input type="hidden" name="team_id" value="{{ $match->away_team_id }}">
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <button type="button" class="btn btn-sm btn-outline-success quick-event" data-type="goal" data-form="awayForm"><i class="fas fa-futbol"></i> Goal</button>
                            <button type="button" class="btn btn-sm btn-outline-warning quick-event" data-type="yellow_card" data-form="awayForm"><span style="display:inline-block;width:8px;height:12px;background:#ffc107;border-radius:1px;"></span> Yellow</button>
                            <button type="button" class="btn btn-sm btn-outline-danger quick-event" data-type="red_card" data-form="awayForm"><span style="display:inline-block;width:8px;height:12px;background:#dc3545;border-radius:1px;"></span> Red</button>
                            <button type="button" class="btn btn-sm btn-outline-primary quick-event" data-type="substitution" data-form="awayForm"><i class="fas fa-exchange-alt"></i> Sub</button>
                            <button type="button" class="btn btn-sm btn-outline-dark quick-event" data-type="penalty_scored" data-form="awayForm">Pen</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-event" data-type="own_goal" data-form="awayForm">OG</button>
                        </div>
                        <div class="row g-1 mb-1">
                            <div class="col-5">
                                <select name="event_type" class="form-select form-select-sm" id="awayEventType" required>
                                    <option value="goal">Goal</option>
                                    <option value="penalty_scored">Penalty</option>
                                    <option value="own_goal">Own Goal</option>
                                    <option value="yellow_card">Yellow</option>
                                    <option value="red_card">Red</option>
                                    <option value="substitution">Sub</option>
                                    <option value="penalty_missed">Pen Miss</option>
                                    <option value="injury">Injury</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <select name="player_id" class="form-select form-select-sm" required>
                                    <option value="">-- Player --</option>
                                    @foreach($awayPlayers as $p)
                                        <option value="{{ $p->id }}">{{ $p->jersey_number }} {{ $p->name }}{{ $p->is_u23 ? " [U23]" : "" }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <div class="input-group input-group-sm">
                                    <input type="number" name="minute" class="form-control" min="1" max="120" placeholder="Min" required>
                                    <input type="number" name="extra_time_minute" class="form-control" min="1" placeholder="+" style="max-width:45px;">
                                </div>
                            </div>
                        </div>
                        <div class="away-player-out-label mb-0" style="display:none;">
                            <label class="small text-danger fw-bold mb-0"><i class="fas fa-arrow-down text-danger me-1"></i>Player Out (Keluar)</label>
                        </div>
                        <div class="away-sub-field mb-1" style="display:none;">
                            <label class="small text-success fw-bold mb-0"><i class="fas fa-arrow-up text-success me-1"></i>Player In (Masuk)</label>
                            <select name="related_player_id" class="form-select form-select-sm">
                                <option value="">-- Select Player In --</option>
                                @foreach($awayPlayers as $p)
                                    <option value="{{ $p->id }}">{{ $p->jersey_number }} {{ $p->name }}{{ $p->is_u23 ? " [U23]" : "" }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex gap-1">
                            <input type="text" name="notes" class="form-control form-control-sm flex-grow-1" placeholder="{{ __('app.notes') }}">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endif
@endauth

@auth
@include('matches.partials.substitutions')
@endauth

<!-- Match Events Timeline - Home vs Away -->
@php
    $allEvents = $match->events->sortBy('minute');
    $homeEvents = $allEvents->where('team_id', $match->home_team_id)->values();
    $awayEvents = $allEvents->where('team_id', $match->away_team_id)->values();
@endphp
@if($allEvents->isNotEmpty())
<div class="card mb-3">
    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between">
        <h6 class="mb-0"><i class="fas fa-stream me-2"></i>{{ __('app.match_events') }}</h6>
        <span class="badge bg-light text-dark">{{ $allEvents->count() }}</span>
    </div>
    <div class="card-body p-2">
        <div class="row g-2">
            <!-- Home Team Events -->
            <div class="col-md-6">
                <div class="border-start border-success border-3 ps-2 mb-2">
                    <strong class="text-success small"><i class="fas fa-home me-1"></i>{{ $match->homeTeam->name ?? 'Home' }}</strong>
                    <span class="badge bg-success ms-1">{{ $homeEvents->count() }}</span>
                </div>
                @forelse($homeEvents as $event)
                    <div class="d-flex align-items-start py-1 px-1 mb-1 rounded {{ $loop->even ? 'bg-light' : '' }}" style="border-left:3px solid #198754;">
                        <div class="me-2 text-center" style="min-width:36px;">
                            <span class="badge bg-dark" style="font-size:10px;">{{ $event->minute }}'</span>
                            @if($event->extra_time_minute)
                                <small class="text-danger d-block" style="font-size:9px;">+{{ $event->extra_time_minute }}</small>
                            @endif
                        </div>
                        <div class="me-1">
                            @if(in_array($event->event_type, ['goal', 'penalty_scored']))
                                <i class="fas fa-futbol text-success"></i>
                            @elseif($event->event_type === 'own_goal')
                                <i class="fas fa-futbol text-danger"></i>
                            @elseif($event->event_type === 'yellow_card')
                                <span style="display:inline-block;width:10px;height:14px;background:#ffc107;border-radius:1px;"></span>
                            @elseif($event->event_type === 'red_card')
                                <span style="display:inline-block;width:10px;height:14px;background:#dc3545;border-radius:1px;"></span>
                            @elseif($event->event_type === 'substitution')
                                <span><i class="fas fa-arrow-down text-danger" style="font-size:9px;"></i><i class="fas fa-arrow-up text-success" style="font-size:9px;"></i></span>
                            @elseif($event->event_type === 'penalty_missed')
                                <i class="fas fa-times-circle text-danger"></i>
                            @elseif($event->event_type === 'injury')
                                <i class="fas fa-medkit text-warning"></i>
                            @else
                                <i class="fas fa-flag text-muted"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            @if($event->event_type === 'substitution')
                                <small class="fw-bold">Substitution</small>
                                <small class="d-block"><i class="fas fa-arrow-down text-danger me-1" style="font-size:9px;"></i><span class="text-danger">Out:</span> {{ $event->player->name ?? '-' }}</small>
                                @if($event->related_player_id && $event->relatedPlayer)
                                <small class="d-block"><i class="fas fa-arrow-up text-success me-1" style="font-size:9px;"></i><span class="text-success">In:</span> {{ $event->relatedPlayer->name }}</small>
                                @endif
                                @if($event->notes)<small class="text-muted d-block">{{ $event->notes }}</small>@endif
                            @else
                                <strong class="small">{{ $event->player->name ?? '-' }}</strong>
                                <small class="text-muted d-block">{{ str_replace('_', ' ', ucfirst($event->event_type)) }}@if($event->notes) - {{ $event->notes }}@endif</small>
                            @endif
                            @if($canOperate && $event->recorded_by_user_id)
                                <small class="text-muted d-block" style="font-size:9px;"><i class="fas fa-user-edit me-1"></i>{{ optional($event->recordedBy)->name }}</small>
                            @endif
                        </div>
                        @auth
                        @if($canEditMatch)
                            <form method="POST" action="{{ route('matches.events.destroy', [$match->id, $event->id]) }}" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-1"><i class="fas fa-trash" style="font-size:10px;"></i></button>
                            </form>
                        @endif
                        @endauth
                    </div>
                @empty
                    <div class="text-center text-muted py-2"><small>No events</small></div>
                @endforelse
            </div>

            <!-- Away Team Events -->
            <div class="col-md-6">
                <div class="border-start border-primary border-3 ps-2 mb-2">
                    <strong class="text-primary small"><i class="fas fa-plane me-1"></i>{{ $match->awayTeam->name ?? 'Away' }}</strong>
                    <span class="badge bg-primary ms-1">{{ $awayEvents->count() }}</span>
                </div>
                @forelse($awayEvents as $event)
                    <div class="d-flex align-items-start py-1 px-1 mb-1 rounded {{ $loop->even ? 'bg-light' : '' }}" style="border-left:3px solid #0d6efd;">
                        <div class="me-2 text-center" style="min-width:36px;">
                            <span class="badge bg-dark" style="font-size:10px;">{{ $event->minute }}'</span>
                            @if($event->extra_time_minute)
                                <small class="text-danger d-block" style="font-size:9px;">+{{ $event->extra_time_minute }}</small>
                            @endif
                        </div>
                        <div class="me-1">
                            @if(in_array($event->event_type, ['goal', 'penalty_scored']))
                                <i class="fas fa-futbol text-success"></i>
                            @elseif($event->event_type === 'own_goal')
                                <i class="fas fa-futbol text-danger"></i>
                            @elseif($event->event_type === 'yellow_card')
                                <span style="display:inline-block;width:10px;height:14px;background:#ffc107;border-radius:1px;"></span>
                            @elseif($event->event_type === 'red_card')
                                <span style="display:inline-block;width:10px;height:14px;background:#dc3545;border-radius:1px;"></span>
                            @elseif($event->event_type === 'substitution')
                                <span><i class="fas fa-arrow-down text-danger" style="font-size:9px;"></i><i class="fas fa-arrow-up text-success" style="font-size:9px;"></i></span>
                            @elseif($event->event_type === 'penalty_missed')
                                <i class="fas fa-times-circle text-danger"></i>
                            @elseif($event->event_type === 'injury')
                                <i class="fas fa-medkit text-warning"></i>
                            @else
                                <i class="fas fa-flag text-muted"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            @if($event->event_type === 'substitution')
                                <small class="fw-bold">Substitution</small>
                                <small class="d-block"><i class="fas fa-arrow-down text-danger me-1" style="font-size:9px;"></i><span class="text-danger">Out:</span> {{ $event->player->name ?? '-' }}</small>
                                @if($event->related_player_id && $event->relatedPlayer)
                                <small class="d-block"><i class="fas fa-arrow-up text-success me-1" style="font-size:9px;"></i><span class="text-success">In:</span> {{ $event->relatedPlayer->name }}</small>
                                @endif
                                @if($event->notes)<small class="text-muted d-block">{{ $event->notes }}</small>@endif
                            @else
                                <strong class="small">{{ $event->player->name ?? '-' }}</strong>
                                <small class="text-muted d-block">{{ str_replace('_', ' ', ucfirst($event->event_type)) }}@if($event->notes) - {{ $event->notes }}@endif</small>
                            @endif
                            @if($canOperate && $event->recorded_by_user_id)
                                <small class="text-muted d-block" style="font-size:9px;"><i class="fas fa-user-edit me-1"></i>{{ optional($event->recordedBy)->name }}</small>
                            @endif
                        </div>
                        @auth
                        @if($canEditMatch)
                            <form method="POST" action="{{ route('matches.events.destroy', [$match->id, $event->id]) }}" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-1"><i class="fas fa-trash" style="font-size:10px;"></i></button>
                            </form>
                        @endif
                        @endauth
                    </div>
                @empty
                    <div class="text-center text-muted py-2"><small>No events</small></div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@elseif($match->status !== 'scheduled')
<div class="card mb-3">
    <div class="card-body text-center text-muted py-3">
        <i class="fas fa-stream fa-2x mb-2 d-block"></i>
        {{ __('app.no_events') }}
    </div>
</div>
@endif

<!-- Match Information (collapsible) -->
<div class="card mb-3">
    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#matchInfoPanel" role="button">
        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>{{ __('app.match_information') }}</h6>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="collapse {{ $match->isPlaying() ? '' : 'show' }}" id="matchInfoPanel">
        <div class="card-body py-2">
            @php $canSeeOfficials = Auth::check() && $match->canOperateBy(Auth::user()); @endphp
            <div class="row">
                <div class="{{ $canSeeOfficials ? 'col-md-6' : 'col-md-12' }}">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th class="text-muted" style="width:140px;">{{ __('app.date_and_time') }}</th>
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
                                @else - @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">{{ __('app.match_code_label') }}</th>
                            <td>{{ $match->match_code ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                @if($canSeeOfficials)
                {{-- Match officials — visible only to Super Admin, League Admin, Head MC and the assigned Match Commissioner. Empty rows are hidden. --}}
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        @php $hasOfficials = $match->referee || $match->assistant_referee_1 || $match->assistant_referee_2 || $match->match_commissioner; @endphp
                        @if($match->referee)
                        <tr>
                            <th class="text-muted" style="width:160px;">{{ __('app.referee') }}</th>
                            <td>{{ $match->referee }}</td>
                        </tr>
                        @endif
                        @if($match->assistant_referee_1)
                        <tr>
                            <th class="text-muted">{{ __('app.assistant_referee_1') }}</th>
                            <td>{{ $match->assistant_referee_1 }}</td>
                        </tr>
                        @endif
                        @if($match->assistant_referee_2)
                        <tr>
                            <th class="text-muted">{{ __('app.assistant_referee_2') }}</th>
                            <td>{{ $match->assistant_referee_2 }}</td>
                        </tr>
                        @endif
                        @if($match->match_commissioner)
                        <tr>
                            <th class="text-muted" style="width:160px;">{{ __('app.match_commissioner') }}</th>
                            <td>{{ $match->match_commissioner }}</td>
                        </tr>
                        @endif
                        @unless($hasOfficials)
                        <tr>
                            <td class="text-muted small"><i class="fas fa-user-shield me-1"></i>{{ __('app.officials_not_assigned') }}</td>
                        </tr>
                        @endunless
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<a id="jersey-section"></a>
@include('matches.partials.jersey')

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
<div class="card mb-3">
    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#lineupsPanel" role="button">
        <h6 class="mb-0"><i class="fas fa-list-ol me-2"></i>{{ __('app.lineup') }}</h6>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="collapse {{ $match->isPlaying() ? '' : 'show' }}" id="lineupsPanel">
        <div class="card-body py-2">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-success small fw-bold"><i class="fas fa-home me-1"></i>{{ $match->homeTeam->name ?? 'Home' }}</h6>
                    @if($homeStarters->isNotEmpty())
                        <p class="text-muted small mb-1"><i class="fas fa-star me-1"></i>{{ __('app.starting_eleven') }}</p>
                        <table class="table table-sm table-striped mb-2">
                            <tbody>
                                @foreach($homeStarters as $l)
                                    <tr><td style="width:35px">{{ $l->jersey_number }}</td><td>{{ $l->player->name ?? '-' }} @if($l->player && $l->player->is_u23)<span class="badge bg-warning text-dark" style="font-size:0.6rem;">U23</span>@endif</td><td style="width:90px">{{ ucfirst($l->position ?? '-') }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                    @if($homeSubs->isNotEmpty())
                        <p class="text-warning small mb-1"><i class="fas fa-exchange-alt me-1"></i>{{ __('app.substitutes') }}</p>
                        <table class="table table-sm table-striped mb-0">
                            <tbody>
                                @foreach($homeSubs as $l)
                                    <tr><td style="width:35px">{{ $l->jersey_number }}</td><td>{{ $l->player->name ?? '-' }} @if($l->player && $l->player->is_u23)<span class="badge bg-warning text-dark" style="font-size:0.6rem;">U23</span>@endif</td><td style="width:90px">{{ ucfirst($l->position ?? '-') }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                    @if($homeLineup->isEmpty())
                        <p class="text-muted small"><i class="fas fa-info-circle me-1"></i>{{ __('app.lineup_not_submitted') }}</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary small fw-bold"><i class="fas fa-plane me-1"></i>{{ $match->awayTeam->name ?? 'Away' }}</h6>
                    @if($awayStarters->isNotEmpty())
                        <p class="text-muted small mb-1"><i class="fas fa-star me-1"></i>{{ __('app.starting_eleven') }}</p>
                        <table class="table table-sm table-striped mb-2">
                            <tbody>
                                @foreach($awayStarters as $l)
                                    <tr><td style="width:35px">{{ $l->jersey_number }}</td><td>{{ $l->player->name ?? '-' }} @if($l->player && $l->player->is_u23)<span class="badge bg-warning text-dark" style="font-size:0.6rem;">U23</span>@endif</td><td style="width:90px">{{ ucfirst($l->position ?? '-') }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                    @if($awaySubs->isNotEmpty())
                        <p class="text-warning small mb-1"><i class="fas fa-exchange-alt me-1"></i>{{ __('app.substitutes') }}</p>
                        <table class="table table-sm table-striped mb-0">
                            <tbody>
                                @foreach($awaySubs as $l)
                                    <tr><td style="width:35px">{{ $l->jersey_number }}</td><td>{{ $l->player->name ?? '-' }} @if($l->player && $l->player->is_u23)<span class="badge bg-warning text-dark" style="font-size:0.6rem;">U23</span>@endif</td><td style="width:90px">{{ ucfirst($l->position ?? '-') }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                    @if($awayLineup->isEmpty())
                        <p class="text-muted small"><i class="fas fa-info-circle me-1"></i>{{ __('app.lineup_not_submitted') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- E-Signature & Match Remarks (Admin/Commissioner only) -->
@auth
@if($canOperate)
@php
    $sigRoles = ['head_referee', 'home_team_rep', 'away_team_rep', 'match_commissioner'];
    $sigIcons = [
        'head_referee' => 'fas fa-whistle',
        'home_team_rep' => 'fas fa-home',
        'away_team_rep' => 'fas fa-plane',
        'match_commissioner' => 'fas fa-user-shield',
    ];
    $sigLabels = [
        'head_referee' => __('app.sig_head_referee'),
        'home_team_rep' => __('app.sig_home_team_rep'),
        'away_team_rep' => __('app.sig_away_team_rep'),
        'match_commissioner' => __('app.sig_match_commissioner'),
    ];
    $sigDefaults = [
        'head_referee' => $match->referee ?? '',
        'home_team_rep' => '',
        'away_team_rep' => '',
        'match_commissioner' => $match->match_commissioner ?? '',
    ];
    $allSigned = $match->allSignaturesConfirmed();
    $sigCount = $match->signatures->where('confirmed', true)->count();
@endphp

<div class="card mb-3 {{ $allSigned ? 'border-success' : 'border-warning' }}">
    <div class="card-header {{ $allSigned ? 'bg-success' : 'bg-warning' }} text-{{ $allSigned ? 'white' : 'dark' }} py-2 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#esignPanel" role="button">
        <h6 class="mb-0">
            <i class="fas fa-signature me-2"></i>{{ __('app.esignature_remarks') }}
        </h6>
        <div>
            <span class="badge {{ $allSigned ? 'bg-light text-success' : 'bg-dark' }}">{{ $sigCount }}/4</span>
            <i class="fas fa-chevron-down ms-2"></i>
        </div>
    </div>
    <div class="collapse {{ $matchLocked || $match->isFinished() ? 'show' : '' }}" id="esignPanel">
        <div class="card-body py-3">

            @if($matchLocked)
                <div class="alert alert-dark mb-3 py-2">
                    <i class="fas fa-lock me-2"></i>{{ __('app.match_closed_locked') }}
                </div>
            @endif

            <!-- Signature Cards -->
            <div class="row g-2 mb-3">
                @foreach($sigRoles as $index => $role)
                    @php
                        $sig = $match->getSignature($role);
                        $stepNum = $index + 1;
                    @endphp
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 {{ $sig && $sig->confirmed ? 'border-success' : 'border-secondary' }}">
                            <div class="card-header py-1 {{ $sig && $sig->confirmed ? 'bg-success text-white' : 'bg-light' }}">
                                <small class="fw-bold">
                                    <span class="badge {{ $sig && $sig->confirmed ? 'bg-white text-success' : 'bg-secondary' }} rounded-circle me-1" style="width:20px;height:20px;line-height:14px;font-size:10px;">{{ $stepNum }}</span>
                                    <i class="{{ $sigIcons[$role] ?? 'fas fa-pen' }} me-1"></i>{{ $sigLabels[$role] }}
                                </small>
                            </div>
                            <div class="card-body py-2 px-2">
                                @if($sig && $sig->confirmed)
                                    <div class="text-center">
                                        <i class="fas fa-check-circle text-success fa-2x mb-1"></i>
                                        <div class="fw-bold small">{{ $sig->name }}</div>
                                        <div class="text-muted" style="font-size:10px;">
                                            {{ $sig->signed_at ? $sig->signed_at->format('d M Y, H:i') : '-' }}
                                        </div>
                                        @if($sig->signature_data)
                                            <img src="{{ $sig->signature_data }}" alt="Signature" class="img-fluid mt-1" style="max-height:50px;border:1px solid #ddd;border-radius:4px;">
                                        @endif
                                    </div>
                                    <div class="mt-1 pt-1 border-top">
                                        <div class="text-muted" style="font-size:9px;text-transform:uppercase;letter-spacing:.3px;">{{ __('app.sig_remarks_label') }}</div>
                                        <div class="small {{ $sig->remarks ? '' : 'text-muted fst-italic' }}">{{ $sig->remarks ?: __('app.sig_remarks_none') }}</div>
                                    </div>
                                    @if($canEditMatch)
                                        <form method="POST" action="{{ route('matches.signature.destroy', [$match->id, $sig->id]) }}" class="text-center mt-1">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-1" style="font-size:10px;" onclick="return confirm('Remove this signature?')">
                                                <i class="fas fa-times me-1"></i>{{ __('app.remove') }}
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    @if($canEditMatch)
                                        <form method="POST" action="{{ route('matches.signature.store', $match) }}">
                                            @csrf
                                            <input type="hidden" name="role" value="{{ $role }}">
                                            <div class="mb-1">
                                                <input type="text" name="name" class="form-control form-control-sm" placeholder="{{ __('app.full_name') }}" value="{{ $sigDefaults[$role] }}" required>
                                            </div>
                                            <div class="mb-1">
                                                <div class="border rounded bg-white position-relative" style="height:80px;cursor:crosshair;" id="sigPad_{{ $role }}" onclick="openSignaturePad('{{ $role }}')">
                                                    <div class="text-center text-muted py-3" style="font-size:10px;">
                                                        <i class="fas fa-pen-fancy"></i> {{ __('app.tap_to_sign') }}
                                                    </div>
                                                </div>
                                                <input type="hidden" name="signature_data" id="sigData_{{ $role }}">
                                            </div>
                                            <div class="mb-1">
                                                <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="{{ __('app.sig_remarks_placeholder') }}" style="font-size:11px;"></textarea>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" name="confirmed" value="1" id="confirm_{{ $role }}" required>
                                                <label class="form-check-label" for="confirm_{{ $role }}" style="font-size:10px;">
                                                    {{ __('app.sig_confirm_text') }}
                                                </label>
                                            </div>
                                            <button type="submit" class="btn btn-success btn-sm w-100 py-0">
                                                <i class="fas fa-check me-1"></i>{{ __('app.confirm_sign') }}
                                            </button>
                                        </form>
                                    @else
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-times-circle fa-2x mb-1 d-block"></i>
                                            <small>{{ __('app.not_signed') }}</small>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Match Remarks -->
            <div class="border rounded p-3 bg-light">
                <h6 class="fw-bold mb-2"><i class="fas fa-clipboard-list me-2"></i>{{ __('app.match_remarks') }}</h6>

                @if($canEditMatch)
                    <form method="POST" action="{{ route('matches.remarks.store', $match) }}">
                        @csrf
                        <textarea name="match_remarks" class="form-control form-control-sm mb-2" rows="4" placeholder="{{ __('app.remarks_placeholder') }}">{{ $match->match_remarks }}</textarea>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">{{ __('app.remarks_hint') }}</small>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save me-1"></i>{{ __('app.save_remarks') }}
                            </button>
                        </div>
                    </form>
                @else
                    @if($match->match_remarks)
                        <div class="bg-white border rounded p-2">
                            {!! nl2br(e($match->match_remarks)) !!}
                        </div>
                    @else
                        <p class="text-muted mb-0 small"><i class="fas fa-info-circle me-1"></i>{{ __('app.no_remarks') }}</p>
                    @endif
                @endif
            </div>

            <!-- Status Summary -->
            <div class="mt-3 text-center">
                @if($allSigned)
                    <div class="alert alert-success py-2 mb-0">
                        <i class="fas fa-check-double me-2"></i>{{ __('app.all_signatures_complete') }}
                    </div>
                @else
                    <div class="alert alert-warning py-2 mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ __('app.signatures_pending', ['count' => 4 - $sigCount]) }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endif
@endauth


<!-- PDF Downloads -->
@auth
@php
    $canDownloadPdf = auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->hasTeams() && (auth()->user()->managesTeam($match->home_team_id) || auth()->user()->managesTeam($match->away_team_id)));
@endphp
@if($canDownloadPdf)
<div class="d-flex gap-2 flex-wrap mb-3">
    <a href="{{ route('matches.pdf.summary', $match) }}" class="btn btn-outline-danger btn-sm" target="_blank">
        <i class="fas fa-file-pdf me-1"></i>{{ __('app.match_summary_pdf') }}
    </a>
    <a href="{{ route('matches.pdf.teamsheet', $match) }}" class="btn btn-outline-danger btn-sm" target="_blank">
        <i class="fas fa-file-pdf me-1"></i>{{ __('app.team_sheet_pdf') }}
    </a>
    @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
        <a href="{{ route('matches.events', $match) }}" class="btn btn-outline-dark btn-sm">
            <i class="fas fa-futbol me-1"></i>{{ __('app.events') }} ({{ __('app.full_page') }})
        </a>
    @endif
</div>
@endif
@endauth

@push('styles')
<style>
    .pulse-live { animation: pulse 1.5s ease-in-out infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.quick-event').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var formId = this.dataset.form;
            var eventType = this.dataset.type;
            var form = document.getElementById(formId);
            var select = form.querySelector('select[name=event_type]');
            select.value = eventType;
            select.dispatchEvent(new Event('change'));
            form.querySelector('select[name=player_id]').focus();
        });
    });

    var homeET = document.getElementById('homeEventType');
    var awayET = document.getElementById('awayEventType');
    if (homeET) {
        homeET.addEventListener('change', function() {
            var isSub = this.value === 'substitution';
            document.querySelector('.home-sub-field').style.display = isSub ? 'block' : 'none';
            var lbl = document.querySelector('.home-player-out-label');
            if (lbl) lbl.style.display = isSub ? 'block' : 'none';
        });
    }
    if (awayET) {
        awayET.addEventListener('change', function() {
            var isSub = this.value === 'substitution';
            document.querySelector('.away-sub-field').style.display = isSub ? 'block' : 'none';
            var lbl = document.querySelector('.away-player-out-label');
            if (lbl) lbl.style.display = isSub ? 'block' : 'none';
        });
    }

    var arrow = document.getElementById('eventPanelArrow');
    var panel = document.getElementById('quickEventPanel');
    if (panel && arrow) {
        panel.addEventListener('hidden.bs.collapse', function() { arrow.classList.replace('fa-chevron-up', 'fa-chevron-down'); });
        panel.addEventListener('shown.bs.collapse', function() { arrow.classList.replace('fa-chevron-down', 'fa-chevron-up'); });
    }

    // Live match clock: ticks up smoothly from the server kick-off timestamp.
    // First half counts 0' -> half; second half continues half -> full.
    (function () {
        var badge = document.getElementById('liveBadge');
        var out = document.getElementById('liveMinute');
        if (!badge || !out) return;
        var status = badge.dataset.status;
        var half = parseInt(badge.dataset.half, 10) || 45;
        var full = parseInt(badge.dataset.full, 10) || 90;
        var fhstop = parseInt(badge.dataset.fhstop, 10) || 0;
        var shstop = parseInt(badge.dataset.shstop, 10) || 0;
        var liveStart = badge.dataset.liveStart ? new Date(badge.dataset.liveStart) : null;
        var shStart = badge.dataset.secondhalfStart ? new Date(badge.dataset.secondhalfStart) : null;
        function tick() {
            var now = new Date();
            var m = null;
            if (status === 'live' && liveStart) {
                m = Math.min(Math.floor((now - liveStart) / 60000), half + fhstop);
            } else if (status === 'second_half' && shStart) {
                m = Math.min(half + Math.floor((now - shStart) / 60000), full + shstop);
            }
            if (m !== null && m >= 0) { out.textContent = m + "'"; }
        }
        tick();
        setInterval(tick, 1000);
    })();

    // Auto-collapse completed sections (Pre-Match Verification, Match Day Photos)
    // so a busy MC sees less clutter; they can still expand to review/replace.
    document.querySelectorAll('[data-autocollapse="1"]').forEach(function (el) {
        var c = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
        c.hide();
    });
});
</script>
@endpush

<!-- Signature Pad Modal -->
<div class="modal fade" id="signatureModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2 bg-dark text-white">
                <h6 class="modal-title"><i class="fas fa-signature me-2"></i>{{ __('app.draw_signature') }}</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <canvas id="sigCanvas" style="width:100%;height:180px;border:2px solid #003366;border-radius:6px;cursor:crosshair;touch-action:none;-ms-touch-action:none;-webkit-user-select:none;user-select:none;"></canvas>
            </div>
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSignature()"><i class="fas fa-eraser me-1"></i>{{ __('app.clear') }}</button>
                <button type="button" class="btn btn-success btn-sm" onclick="confirmSignature()"><i class="fas fa-check me-1"></i>{{ __('app.use_signature') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Submit Final Match Report Modal --}}
@auth
@if($canOperate && !$matchLocked)
<div class="modal fade" id="submitFinalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('matches.submit-final', $match) }}">
                @csrf
                <div class="modal-header py-2 bg-danger text-white">
                    <h6 class="modal-title"><i class="fas fa-flag-checkered me-2"></i>{{ __('app.submit_final_confirm_title') }}</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 small mb-3"><i class="fas fa-exclamation-triangle me-1"></i>{{ __('app.submit_final_confirm_body') }}</div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold mb-1">{{ __('app.final_minute_label') }}</label>
                        <input type="number" name="final_minute" class="form-control form-control-sm" min="0" max="200" placeholder="{{ $match->matchDuration() }}">
                        <div class="form-text" style="font-size:11px;">{{ __('app.final_minute_hint') }}</div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label small fw-bold mb-1">{{ __('app.match_remarks') }}</label>
                        <textarea name="match_remarks" class="form-control form-control-sm" rows="3" placeholder="{{ __('app.remarks_placeholder') }}">{{ $match->match_remarks }}</textarea>
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-lock me-1"></i>{{ __('app.confirm_submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endauth

@push('scripts')

<script>
var currentSigRole = null;
var sigCanvas = null;
var sigCtx = null;
var sigDrawing = false;
var sigLastX = 0;
var sigLastY = 0;

function openSignaturePad(role) {
    currentSigRole = role;
    var modalEl = document.getElementById('signatureModal');
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    // Remove old event listeners by cloning canvas
    var oldCanvas = document.getElementById('sigCanvas');
    var newCanvas = oldCanvas.cloneNode(true);
    oldCanvas.parentNode.replaceChild(newCanvas, oldCanvas);

    modal.show();

    // Wait for modal to be fully visible before initializing canvas
    modalEl.addEventListener('shown.bs.modal', function onShown() {
        modalEl.removeEventListener('shown.bs.modal', onShown);
        setupCanvas();
    }, { once: true });
}

function setupCanvas() {
    sigCanvas = document.getElementById('sigCanvas');
    sigCtx = sigCanvas.getContext('2d');

    // Set actual pixel dimensions from CSS dimensions
    var rect = sigCanvas.getBoundingClientRect();
    var dpr = window.devicePixelRatio || 1;
    sigCanvas.width = rect.width * dpr;
    sigCanvas.height = rect.height * dpr;
    sigCtx.scale(dpr, dpr);

    // Clear with white
    sigCtx.fillStyle = '#ffffff';
    sigCtx.fillRect(0, 0, rect.width, rect.height);

    // Drawing style
    sigCtx.strokeStyle = '#003366';
    sigCtx.lineWidth = 2.5;
    sigCtx.lineCap = 'round';
    sigCtx.lineJoin = 'round';

    sigDrawing = false;

    // Mouse events
    sigCanvas.addEventListener('mousedown', sigStart);
    sigCanvas.addEventListener('mousemove', sigMove);
    sigCanvas.addEventListener('mouseup', sigEnd);
    sigCanvas.addEventListener('mouseleave', sigEnd);

    // Touch events - must prevent default to stop scrolling
    sigCanvas.addEventListener('touchstart', sigTouchStart, { passive: false });
    sigCanvas.addEventListener('touchmove', sigTouchMove, { passive: false });
    sigCanvas.addEventListener('touchend', sigTouchEnd, { passive: false });
    sigCanvas.addEventListener('touchcancel', sigTouchEnd, { passive: false });
}

function getCanvasPos(clientX, clientY) {
    var rect = sigCanvas.getBoundingClientRect();
    return {
        x: clientX - rect.left,
        y: clientY - rect.top
    };
}

function sigStart(e) {
    e.preventDefault();
    sigDrawing = true;
    var pos = getCanvasPos(e.clientX, e.clientY);
    sigLastX = pos.x;
    sigLastY = pos.y;
    sigCtx.beginPath();
    sigCtx.moveTo(pos.x, pos.y);
}

function sigMove(e) {
    if (!sigDrawing) return;
    e.preventDefault();
    var pos = getCanvasPos(e.clientX, e.clientY);
    sigCtx.beginPath();
    sigCtx.moveTo(sigLastX, sigLastY);
    sigCtx.lineTo(pos.x, pos.y);
    sigCtx.stroke();
    sigLastX = pos.x;
    sigLastY = pos.y;
}

function sigEnd(e) {
    sigDrawing = false;
}

function sigTouchStart(e) {
    e.preventDefault();
    if (e.touches.length === 1) {
        var touch = e.touches[0];
        sigDrawing = true;
        var pos = getCanvasPos(touch.clientX, touch.clientY);
        sigLastX = pos.x;
        sigLastY = pos.y;
        sigCtx.beginPath();
        sigCtx.moveTo(pos.x, pos.y);
    }
}

function sigTouchMove(e) {
    e.preventDefault();
    if (!sigDrawing || e.touches.length !== 1) return;
    var touch = e.touches[0];
    var pos = getCanvasPos(touch.clientX, touch.clientY);
    sigCtx.beginPath();
    sigCtx.moveTo(sigLastX, sigLastY);
    sigCtx.lineTo(pos.x, pos.y);
    sigCtx.stroke();
    sigLastX = pos.x;
    sigLastY = pos.y;
}

function sigTouchEnd(e) {
    e.preventDefault();
    sigDrawing = false;
}

function clearSignature() {
    if (!sigCanvas || !sigCtx) return;
    var rect = sigCanvas.getBoundingClientRect();
    sigCtx.fillStyle = '#ffffff';
    sigCtx.fillRect(0, 0, rect.width, rect.height);
    sigCtx.strokeStyle = '#003366';
    sigCtx.lineWidth = 2.5;
    sigCtx.lineCap = 'round';
    sigCtx.lineJoin = 'round';
}

function confirmSignature() {
    if (!sigCanvas) return;
    var dataUrl = sigCanvas.toDataURL('image/png');
    document.getElementById('sigData_' + currentSigRole).value = dataUrl;
    var pad = document.getElementById('sigPad_' + currentSigRole);
    pad.innerHTML = '<img src="' + dataUrl + '" class="img-fluid" style="max-height:76px;">';
    bootstrap.Modal.getInstance(document.getElementById('signatureModal')).hide();
}
</script>

@endpush

@if($match->isLive())
@push('scripts')
<script>setTimeout(function() { location.reload(); }, 60000);</script>
@endpush
@endif
@endsection
