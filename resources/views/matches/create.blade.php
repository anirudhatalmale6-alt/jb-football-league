@extends('layouts.app')

@section('title', 'Create Match')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-plus-circle text-success me-2"></i>Create Match
    </h2>
    <a href="{{ route('matches.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Matches
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('matches.store') }}" method="POST">
            @csrf

            <h5 class="fw-bold mb-3 border-bottom pb-2">
                <i class="fas fa-info-circle text-primary me-1"></i> Match Details
            </h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="competition_id" class="form-label fw-semibold">Competition <span class="text-danger">*</span></label>
                    <select class="form-select @error('competition_id') is-invalid @enderror" id="competition_id" name="competition_id" required>
                        <option value="">-- Select Competition --</option>
                        @foreach($competitions as $competition)
                            <option value="{{ $competition->id }}" {{ old('competition_id') == $competition->id ? 'selected' : '' }}>
                                {{ $competition->name }} ({{ $competition->season }})
                            </option>
                        @endforeach
                    </select>
                    @error('competition_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="matchday" class="form-label fw-semibold">Matchday</label>
                    <input type="number" class="form-control @error('matchday') is-invalid @enderror" id="matchday" name="matchday"
                           value="{{ old('matchday') }}" min="1">
                    @error('matchday')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="match_date" class="form-label fw-semibold">Match Date & Time <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control @error('match_date') is-invalid @enderror" id="match_date" name="match_date"
                           value="{{ old('match_date') }}" required>
                    @error('match_date')
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
                            <option value="{{ $team->id }}" {{ old('home_team_id') == $team->id ? 'selected' : '' }}>
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
                            <option value="{{ $team->id }}" {{ old('away_team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('away_team_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="venue" class="form-label fw-semibold">Venue</label>
                <input type="text" class="form-control @error('venue') is-invalid @enderror" id="venue" name="venue"
                       value="{{ old('venue') }}" placeholder="e.g. Sultan Ibrahim Stadium">
                @error('venue')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <h5 class="fw-bold mb-3 mt-4 border-bottom pb-2">
                <i class="fas fa-user-tie text-primary me-1"></i> Match Officials
            </h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="referee" class="form-label fw-semibold">Referee</label>
                    <input type="text" class="form-control @error('referee') is-invalid @enderror" id="referee" name="referee"
                           value="{{ old('referee') }}">
                    @error('referee')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="assistant_referee_1" class="form-label fw-semibold">Assistant Referee 1</label>
                    <input type="text" class="form-control @error('assistant_referee_1') is-invalid @enderror" id="assistant_referee_1" name="assistant_referee_1"
                           value="{{ old('assistant_referee_1') }}">
                    @error('assistant_referee_1')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="assistant_referee_2" class="form-label fw-semibold">Assistant Referee 2</label>
                    <input type="text" class="form-control @error('assistant_referee_2') is-invalid @enderror" id="assistant_referee_2" name="assistant_referee_2"
                           value="{{ old('assistant_referee_2') }}">
                    @error('assistant_referee_2')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="fourth_official" class="form-label fw-semibold">Fourth Official</label>
                    <input type="text" class="form-control @error('fourth_official') is-invalid @enderror" id="fourth_official" name="fourth_official"
                           value="{{ old('fourth_official') }}">
                    @error('fourth_official')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="match_commissioner" class="form-label fw-semibold">Match Commissioner</label>
                    <input type="text" class="form-control @error('match_commissioner') is-invalid @enderror" id="match_commissioner" name="match_commissioner"
                           value="{{ old('match_commissioner') }}">
                    @error('match_commissioner')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> Create Match
                </button>
                <a href="{{ route('matches.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
