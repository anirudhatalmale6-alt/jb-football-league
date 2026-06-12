@extends('layouts.app')

@section('title', __('app.issue_fine'))

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-gavel me-2"></i>{{ __('app.issue_fine') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('disciplinary.store') }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="competition_id" class="form-label fw-bold">{{ __('app.competition') }} <span class="text-danger">*</span></label>
                            <select name="competition_id" id="competition_id" class="form-select @error('competition_id') is-invalid @enderror" required>
                                <option value="">-- {{ __('app.select_competition') }} --</option>
                                @foreach($competitions as $competition)
                                    <option value="{{ $competition->id }}" {{ old('competition_id') == $competition->id ? 'selected' : '' }}>
                                        {{ $competition->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('competition_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="match_game_id" class="form-label fw-bold">{{ __('app.match') }} <small class="text-muted">({{ __('app.optional') }})</small></label>
                            <select name="match_game_id" id="match_game_id" class="form-select @error('match_game_id') is-invalid @enderror">
                                <option value="">-- {{ __('app.select_match') }} --</option>
                            </select>
                            @error('match_game_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="team_id" class="form-label fw-bold">{{ __('app.team') }} <span class="text-danger">*</span></label>
                            <select name="team_id" id="team_id" class="form-select @error('team_id') is-invalid @enderror" required>
                                <option value="">-- {{ __('app.select_team') }} --</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('team_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="player_id" class="form-label fw-bold">{{ __('app.player') }} <small class="text-muted">({{ __('app.optional') }})</small></label>
                            <select name="player_id" id="player_id" class="form-select @error('player_id') is-invalid @enderror">
                                <option value="">-- {{ __('app.select_player') }} --</option>
                            </select>
                            <small class="text-muted">{{ __('app.leave_empty_team_fine') }}</small>
                            @error('player_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fine_type" class="form-label fw-bold">{{ __('app.fine_type_label') }} <span class="text-danger">*</span></label>
                            <select name="fine_type" id="fine_type" class="form-select @error('fine_type') is-invalid @enderror" required>
                                <option value="">-- {{ __('app.select_fine_type') }} --</option>
                                <option value="red_card" {{ old('fine_type') === 'red_card' ? 'selected' : '' }}>{{ __('app.fine_red_card') }}</option>
                                <option value="yellow_accumulation" {{ old('fine_type') === 'yellow_accumulation' ? 'selected' : '' }}>{{ __('app.fine_yellow_accumulation') }}</option>
                                <option value="misconduct" {{ old('fine_type') === 'misconduct' ? 'selected' : '' }}>{{ __('app.fine_misconduct') }}</option>
                                <option value="late_arrival" {{ old('fine_type') === 'late_arrival' ? 'selected' : '' }}>{{ __('app.fine_late_arrival') }}</option>
                                <option value="walkover" {{ old('fine_type') === 'walkover' ? 'selected' : '' }}>{{ __('app.fine_walkover') }}</option>
                                <option value="other" {{ old('fine_type') === 'other' ? 'selected' : '' }}>{{ __('app.fine_other') }}</option>
                            </select>
                            @error('fine_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="amount" class="form-label fw-bold">{{ __('app.fine_amount') }} (RM) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">RM</span>
                                <input type="number" name="amount" id="amount" step="0.01" min="1"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount') }}" placeholder="0.00" required>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">{{ __('app.description') }} <small class="text-muted">({{ __('app.optional') }})</small></label>
                        <input type="text" name="description" id="description"
                               class="form-control @error('description') is-invalid @enderror"
                               value="{{ old('description') }}" placeholder="{{ __('app.fine_description_placeholder') }}">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="payment_url" class="form-label fw-bold">{{ __('app.payment_link') }} <small class="text-muted">({{ __('app.optional') }})</small></label>
                        <input type="url" name="payment_url" id="payment_url"
                               class="form-control @error('payment_url') is-invalid @enderror"
                               value="{{ old('payment_url') }}" placeholder="https://toyyibpay.com/...">
                        <small class="text-muted">{{ __('app.payment_link_help') }}</small>
                        @error('payment_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-bold">{{ __('app.notes') }} <small class="text-muted">({{ __('app.optional') }})</small></label>
                        <textarea name="notes" id="notes" rows="2"
                                  class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('disciplinary.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> {{ __('app.back') }}
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-gavel me-1"></i> {{ __('app.issue_fine') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const teamSelect = document.getElementById('team_id');
    const playerSelect = document.getElementById('player_id');
    const competitionSelect = document.getElementById('competition_id');
    const matchSelect = document.getElementById('match_game_id');

    teamSelect.addEventListener('change', function() {
        const teamId = this.value;
        playerSelect.innerHTML = '<option value="">-- {{ __("app.select_player") }} --</option>';
        if (!teamId) return;

        fetch('/api/teams/' + teamId + '/players')
            .then(response => response.json())
            .then(players => {
                players.forEach(function(player) {
                    const opt = document.createElement('option');
                    opt.value = player.id;
                    opt.textContent = (player.jersey_number ? '#' + player.jersey_number + ' ' : '') + player.name;
                    playerSelect.appendChild(opt);
                });
            });
    });

    competitionSelect.addEventListener('change', function() {
        const compId = this.value;
        matchSelect.innerHTML = '<option value="">-- {{ __("app.select_match") }} --</option>';
        if (!compId) return;

        fetch('/api/competitions/' + compId + '/matches')
            .then(response => response.json())
            .then(matches => {
                matches.forEach(function(match) {
                    const opt = document.createElement('option');
                    opt.value = match.id;
                    const home = match.home_team ? match.home_team.name : 'TBD';
                    const away = match.away_team ? match.away_team.name : 'TBD';
                    const code = match.match_code ? match.match_code + ' - ' : '';
                    opt.textContent = code + home + ' vs ' + away;
                    matchSelect.appendChild(opt);
                });
            });
    });
});
</script>
@endpush
