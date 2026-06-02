@extends('layouts.app')

@section('title', 'Edit Match')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-edit text-warning me-2"></i>Edit Match
    </h2>
    <a href="{{ route('matches.show', $match) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Match
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('matches.update', $match) }}" method="POST">
            @csrf
            @method('PUT')

            <h5 class="fw-bold mb-3 border-bottom pb-2">
                <i class="fas fa-info-circle text-primary me-1"></i> Match Details
            </h5>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="competition_id" class="form-label fw-semibold">Competition <span class="text-danger">*</span></label>
                    <select class="form-select @error('competition_id') is-invalid @enderror" id="competition_id" name="competition_id" required>
                        <option value="">-- Select Competition --</option>
                        @foreach($competitions as $competition)
                            <option value="{{ $competition->id }}" {{ old('competition_id', $match->competition_id) == $competition->id ? 'selected' : '' }}>
                                {{ $competition->name }} ({{ $competition->season }})
                            </option>
                        @endforeach
                    </select>
                    @error('competition_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-2 mb-3">
                    <label for="matchday" class="form-label fw-semibold">Matchday</label>
                    <input type="number" class="form-control @error('matchday') is-invalid @enderror" id="matchday" name="matchday"
                           value="{{ old('matchday', $match->matchday) }}" min="1">
                    @error('matchday')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="match_date" class="form-label fw-semibold">Match Date & Time <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control @error('match_date') is-invalid @enderror" id="match_date" name="match_date"
                           value="{{ old('match_date', $match->match_date?->format('Y-m-d\TH:i')) }}" required>
                    @error('match_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="scheduled" {{ old('status', $match->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="in_progress" {{ old('status', $match->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ old('status', $match->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="postponed" {{ old('status', $match->status) === 'postponed' ? 'selected' : '' }}>Postponed</option>
                        <option value="cancelled" {{ old('status', $match->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="home_team_id" class="form-label fw-semibold">Home Team <span class="text-danger">*</span></label>
                    <select class="form-select @error('home_team_id') is-invalid @enderror" id="home_team_id" name="home_team_id" required>
                        <option value="">-- Select Home Team --</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ old('home_team_id', $match->home_team_id) == $team->id ? 'selected' : '' }}>
                                {{ $team->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('home_team_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="away_team_id" class="form-label fw-semibold">Away Team <span class="text-danger">*</span></label>
                    <select class="form-select @error('away_team_id') is-invalid @enderror" id="away_team_id" name="away_team_id" required>
                        <option value="">-- Select Away Team --</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ old('away_team_id', $match->away_team_id) == $team->id ? 'selected' : '' }}>
                                {{ $team->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('away_team_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h5 class="fw-bold mb-3 mt-2 border-bottom pb-2">
                <i class="fas fa-futbol text-primary me-1"></i> Score
            </h5>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="home_score" class="form-label fw-semibold">Home Score</label>
                    <input type="number" class="form-control @error('home_score') is-invalid @enderror" id="home_score" name="home_score"
                           value="{{ old('home_score', $match->home_score) }}" min="0">
                    @error('home_score')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="away_score" class="form-label fw-semibold">Away Score</label>
                    <input type="number" class="form-control @error('away_score') is-invalid @enderror" id="away_score" name="away_score"
                           value="{{ old('away_score', $match->away_score) }}" min="0">
                    @error('away_score')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="venue" class="form-label fw-semibold">Venue</label>
                    <input type="text" class="form-control @error('venue') is-invalid @enderror" id="venue" name="venue"
                           value="{{ old('venue', $match->venue) }}" placeholder="e.g. Sultan Ibrahim Stadium">
                    @error('venue')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h5 class="fw-bold mb-3 mt-2 border-bottom pb-2">
                <i class="fas fa-user-tie text-primary me-1"></i> Match Officials
            </h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="referee" class="form-label fw-semibold">Referee</label>
                    <input type="text" class="form-control @error('referee') is-invalid @enderror" id="referee" name="referee"
                           value="{{ old('referee', $match->referee) }}">
                    @error('referee')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="assistant_referee_1" class="form-label fw-semibold">Assistant Referee 1</label>
                    <input type="text" class="form-control @error('assistant_referee_1') is-invalid @enderror" id="assistant_referee_1" name="assistant_referee_1"
                           value="{{ old('assistant_referee_1', $match->assistant_referee_1) }}">
                    @error('assistant_referee_1')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="assistant_referee_2" class="form-label fw-semibold">Assistant Referee 2</label>
                    <input type="text" class="form-control @error('assistant_referee_2') is-invalid @enderror" id="assistant_referee_2" name="assistant_referee_2"
                           value="{{ old('assistant_referee_2', $match->assistant_referee_2) }}">
                    @error('assistant_referee_2')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="fourth_official" class="form-label fw-semibold">Fourth Official</label>
                    <input type="text" class="form-control @error('fourth_official') is-invalid @enderror" id="fourth_official" name="fourth_official"
                           value="{{ old('fourth_official', $match->fourth_official) }}">
                    @error('fourth_official')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="match_commissioner" class="form-label fw-semibold">Match Commissioner</label>
                    <input type="text" class="form-control @error('match_commissioner') is-invalid @enderror" id="match_commissioner" name="match_commissioner"
                           value="{{ old('match_commissioner', $match->match_commissioner) }}">
                    @error('match_commissioner')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> Update Match
                </button>
                <a href="{{ route('matches.show', $match) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
