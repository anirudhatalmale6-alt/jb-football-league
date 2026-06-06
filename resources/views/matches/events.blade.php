@extends('layouts.app')

@section('title', __('app.match_events'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-futbol text-success me-2"></i>{{ __('app.match_events') }}
    </h2>
    <a href="{{ route('matches.show', $match) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> {{ __('app.back_to_match') }}
    </a>
</div>

<!-- Match Header Summary -->
<div class="card mb-4">
    <div class="card-body text-center">
        <h5 class="mb-1">
            <strong>{{ $match->homeTeam->name ?? __('app.home') }}</strong>
            @if($match->status === 'completed' || $match->status === 'in_progress')
                <span class="badge bg-dark mx-2">{{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}</span>
            @else
                <span class="text-muted mx-2">vs</span>
            @endif
            <strong>{{ $match->awayTeam->name ?? __('app.away') }}</strong>
        </h5>
        <small class="text-muted">
            {{ $match->competition->name ?? '' }} &mdash;
            {{ $match->match_date ? $match->match_date->format('d M Y, H:i') : '' }} &mdash;
            {{ $match->venue ?? '' }}
        </small>
    </div>
</div>

<div class="row">
    <!-- Add Event Form -->
    <div class="col-lg-5 mb-4">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>{{ __('app.add_event') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('matches.events.store', $match) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="team_id" class="form-label fw-semibold">{{ __('app.team') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('team_id') is-invalid @enderror" id="team_id" name="team_id" required>
                            <option value="">{{ __('app.select_team') }}</option>
                            <option value="{{ $match->home_team_id }}" {{ old('team_id') == $match->home_team_id ? 'selected' : '' }}>
                                {{ $match->homeTeam->name ?? __('app.home') }}
                            </option>
                            <option value="{{ $match->away_team_id }}" {{ old('team_id') == $match->away_team_id ? 'selected' : '' }}>
                                {{ $match->awayTeam->name ?? __('app.away') }}
                            </option>
                        </select>
                        @error('team_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="player_id" class="form-label fw-semibold">{{ __('app.player') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('player_id') is-invalid @enderror" id="player_id" name="player_id" required>
                            <option value="">{{ __('app.select_player') }}</option>
                            <optgroup label="{{ $match->homeTeam->name ?? __('app.home') }}">
                                @foreach($homePlayers as $player)
                                    <option value="{{ $player->id }}" {{ old('player_id') == $player->id ? 'selected' : '' }}>
                                        #{{ $player->jersey_number }} {{ $player->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="{{ $match->awayTeam->name ?? __('app.away') }}">
                                @foreach($awayPlayers as $player)
                                    <option value="{{ $player->id }}" {{ old('player_id') == $player->id ? 'selected' : '' }}>
                                        #{{ $player->jersey_number }} {{ $player->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                        @error('player_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="event_type" class="form-label fw-semibold">{{ __('app.event_type') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('event_type') is-invalid @enderror" id="event_type" name="event_type" required>
                            <option value="">{{ __('app.select_event') }}</option>
                            <option value="goal" {{ old('event_type') === 'goal' ? 'selected' : '' }}>{{ __('app.goal') }}</option>
                            <option value="own_goal" {{ old('event_type') === 'own_goal' ? 'selected' : '' }}>{{ __('app.own_goal') }}</option>
                            <option value="yellow_card" {{ old('event_type') === 'yellow_card' ? 'selected' : '' }}>{{ __('app.yellow_card') }}</option>
                            <option value="red_card" {{ old('event_type') === 'red_card' ? 'selected' : '' }}>{{ __('app.red_card') }}</option>
                            <option value="substitution_in" {{ old('event_type') === 'substitution_in' ? 'selected' : '' }}>{{ __('app.substitution_in') }}</option>
                            <option value="substitution_out" {{ old('event_type') === 'substitution_out' ? 'selected' : '' }}>{{ __('app.substitution_out') }}</option>
                            <option value="penalty_scored" {{ old('event_type') === 'penalty_scored' ? 'selected' : '' }}>{{ __('app.penalty_scored') }}</option>
                            <option value="penalty_missed" {{ old('event_type') === 'penalty_missed' ? 'selected' : '' }}>{{ __('app.penalty_missed') }}</option>
                        </select>
                        @error('event_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="minute" class="form-label fw-semibold">{{ __('app.minute') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('minute') is-invalid @enderror" id="minute" name="minute"
                                   value="{{ old('minute') }}" min="1" max="120" required>
                            @error('minute')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6 mb-3">
                            <label for="extra_time_minute" class="form-label fw-semibold">{{ __('app.extra_time_min') }}</label>
                            <input type="number" class="form-control @error('extra_time_minute') is-invalid @enderror" id="extra_time_minute" name="extra_time_minute"
                                   value="{{ old('extra_time_minute') }}" min="1" max="30">
                            @error('extra_time_minute')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="related_player_id" class="form-label fw-semibold">{{ __('app.related_player') }} <small class="text-muted">(for substitutions)</small></label>
                        <select class="form-select @error('related_player_id') is-invalid @enderror" id="related_player_id" name="related_player_id">
                            <option value="">-- None --</option>
                            <optgroup label="{{ $match->homeTeam->name ?? __('app.home') }}">
                                @foreach($homePlayers as $player)
                                    <option value="{{ $player->id }}" {{ old('related_player_id') == $player->id ? 'selected' : '' }}>
                                        #{{ $player->jersey_number }} {{ $player->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="{{ $match->awayTeam->name ?? __('app.away') }}">
                                @foreach($awayPlayers as $player)
                                    <option value="{{ $player->id }}" {{ old('related_player_id') == $player->id ? 'selected' : '' }}>
                                        #{{ $player->jersey_number }} {{ $player->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                        @error('related_player_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold">{{ __('app.notes') }}</label>
                        <input type="text" class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes"
                               value="{{ old('notes') }}" placeholder="Optional notes about this event">
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-plus me-1"></i> {{ __('app.add_event') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Mark as Completed -->
        @if($match->status !== 'completed')
            <div class="card mt-3">
                <div class="card-body text-center">
                    <form action="{{ route('matches.complete', $match) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to mark this match as completed? This will finalize the result.');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-warning btn-lg w-100">
                            <i class="fas fa-flag-checkered me-1"></i> {{ __('app.mark_as_completed') }}
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <!-- Events List -->
    <div class="col-lg-7 mb-4">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>{{ __('app.match_events') }}</h5>
                <span class="badge bg-secondary">{{ $events->count() }} {{ strtolower(__('app.events')) }}</span>
            </div>
            <div class="card-body">
                @if($events->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-clock fa-3x mb-3 d-block"></i>
                        <p>{{ __('app.no_events_recorded') }}</p>
                        <p class="small">Use the form to add match events.</p>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($events->sortBy('minute') as $event)
                            <div class="list-group-item d-flex align-items-center">
                                <div class="me-3 text-center" style="min-width: 55px;">
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
                                    <strong>{{ $event->player->name ?? 'Unknown' }}</strong>
                                    <span class="text-muted">({{ $event->team->name ?? '-' }})</span>
                                    <br>
                                    <small class="text-muted">
                                        {{ ucfirst(str_replace('_', ' ', $event->event_type)) }}
                                        @if($event->notes)
                                            &mdash; {{ $event->notes }}
                                        @endif
                                    </small>
                                </div>
                                <div>
                                    <form action="{{ route('matches.events.destroy', [$match, $event]) }}" method="POST"
                                          onsubmit="return confirm('Delete this event?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('app.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
