@extends('layouts.app')

@section('title', 'Match Events')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-futbol text-success me-2"></i>Match Events
    </h2>
    <a href="{{ route('matches.show', $match) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Match
    </a>
</div>

<!-- Match Header Summary -->
<div class="card mb-4">
    <div class="card-body text-center">
        <h5 class="mb-1">
            <strong>{{ $match->homeTeam->name ?? 'Home' }}</strong>
            @if($match->status === 'completed' || $match->status === 'in_progress')
                <span class="badge bg-dark mx-2">{{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}</span>
            @else
                <span class="text-muted mx-2">vs</span>
            @endif
            <strong>{{ $match->awayTeam->name ?? 'Away' }}</strong>
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
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add Event</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('matches.events.store', $match) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="team_id" class="form-label fw-semibold">Team <span class="text-danger">*</span></label>
                        <select class="form-select @error('team_id') is-invalid @enderror" id="team_id" name="team_id" required>
                            <option value="">-- Select Team --</option>
                            <option value="{{ $match->home_team_id }}" {{ old('team_id') == $match->home_team_id ? 'selected' : '' }}>
                                {{ $match->homeTeam->name ?? 'Home' }}
                            </option>
                            <option value="{{ $match->away_team_id }}" {{ old('team_id') == $match->away_team_id ? 'selected' : '' }}>
                                {{ $match->awayTeam->name ?? 'Away' }}
                            </option>
                        </select>
                        @error('team_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="player_id" class="form-label fw-semibold">Player <span class="text-danger">*</span></label>
                        <select class="form-select @error('player_id') is-invalid @enderror" id="player_id" name="player_id" required>
                            <option value="">-- Select Player --</option>
                            <optgroup label="{{ $match->homeTeam->name ?? 'Home' }}">
                                @foreach($homePlayers as $player)
                                    <option value="{{ $player->id }}" {{ old('player_id') == $player->id ? 'selected' : '' }}>
                                        #{{ $player->jersey_number }} {{ $player->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="{{ $match->awayTeam->name ?? 'Away' }}">
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
                        <label for="event_type" class="form-label fw-semibold">Event Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('event_type') is-invalid @enderror" id="event_type" name="event_type" required>
                            <option value="">-- Select Event --</option>
                            <option value="goal" {{ old('event_type') === 'goal' ? 'selected' : '' }}>Goal</option>
                            <option value="own_goal" {{ old('event_type') === 'own_goal' ? 'selected' : '' }}>Own Goal</option>
                            <option value="yellow_card" {{ old('event_type') === 'yellow_card' ? 'selected' : '' }}>Yellow Card</option>
                            <option value="red_card" {{ old('event_type') === 'red_card' ? 'selected' : '' }}>Red Card</option>
                            <option value="substitution_in" {{ old('event_type') === 'substitution_in' ? 'selected' : '' }}>Substitution In</option>
                            <option value="substitution_out" {{ old('event_type') === 'substitution_out' ? 'selected' : '' }}>Substitution Out</option>
                            <option value="penalty_scored" {{ old('event_type') === 'penalty_scored' ? 'selected' : '' }}>Penalty Scored</option>
                            <option value="penalty_missed" {{ old('event_type') === 'penalty_missed' ? 'selected' : '' }}>Penalty Missed</option>
                        </select>
                        @error('event_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="minute" class="form-label fw-semibold">Minute <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('minute') is-invalid @enderror" id="minute" name="minute"
                                   value="{{ old('minute') }}" min="1" max="120" required>
                            @error('minute')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6 mb-3">
                            <label for="extra_time_minute" class="form-label fw-semibold">Extra Time Min</label>
                            <input type="number" class="form-control @error('extra_time_minute') is-invalid @enderror" id="extra_time_minute" name="extra_time_minute"
                                   value="{{ old('extra_time_minute') }}" min="1" max="30">
                            @error('extra_time_minute')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="related_player_id" class="form-label fw-semibold">Related Player <small class="text-muted">(for substitutions)</small></label>
                        <select class="form-select @error('related_player_id') is-invalid @enderror" id="related_player_id" name="related_player_id">
                            <option value="">-- None --</option>
                            <optgroup label="{{ $match->homeTeam->name ?? 'Home' }}">
                                @foreach($homePlayers as $player)
                                    <option value="{{ $player->id }}" {{ old('related_player_id') == $player->id ? 'selected' : '' }}>
                                        #{{ $player->jersey_number }} {{ $player->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="{{ $match->awayTeam->name ?? 'Away' }}">
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
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <input type="text" class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes"
                               value="{{ old('notes') }}" placeholder="Optional notes about this event">
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-plus me-1"></i> Add Event
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
                            <i class="fas fa-flag-checkered me-1"></i> Mark as Completed
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
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Match Events</h5>
                <span class="badge bg-secondary">{{ $events->count() }} events</span>
            </div>
            <div class="card-body">
                @if($events->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-clock fa-3x mb-3 d-block"></i>
                        <p>No events recorded yet.</p>
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
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
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
