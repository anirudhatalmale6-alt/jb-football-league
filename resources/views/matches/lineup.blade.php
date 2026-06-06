@extends('layouts.app')

@section('title', __('app.manage_lineup'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-list-ol text-success me-2"></i>{{ __('app.manage_lineup') }}
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
            <span class="text-muted mx-2">vs</span>
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
    <!-- Home Team Lineup -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-shield-halved me-2"></i>{{ $match->homeTeam->name ?? __('app.home_team') }}
                </h5>
            </div>
            <div class="card-body">
                <!-- Add Player to Home Lineup -->
                <form action="{{ route('matches.lineup.store', $match) }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="team_id" value="{{ $match->home_team_id }}">

                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label for="home_player_id" class="form-label fw-semibold small">{{ __('app.player') }}</label>
                            <select class="form-select form-select-sm @error('player_id') is-invalid @enderror" id="home_player_id" name="player_id" required>
                                <option value="">-- Select --</option>
                                @foreach($homePlayers as $player)
                                    <option value="{{ $player->id }}">
                                        #{{ $player->jersey_number }} {{ $player->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="home_jersey" class="form-label fw-semibold small">{{ __('app.jersey_number') }}</label>
                            <input type="number" class="form-select form-select-sm" id="home_jersey" name="jersey_number" min="1" max="99">
                        </div>
                        <div class="col-md-3">
                            <label for="home_position" class="form-label fw-semibold small">{{ __('app.position') }}</label>
                            <select class="form-select form-select-sm" id="home_position" name="position">
                                <option value="goalkeeper">GK</option>
                                <option value="defender">DEF</option>
                                <option value="midfielder">MID</option>
                                <option value="forward">FWD</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="home_starting" name="is_starting" value="1" checked>
                                <label class="form-check-label small" for="home_starting">{{ __('app.start') }}</label>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-sm btn-success" title="Add">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Current Home Lineup -->
                @php
                    $homeStarters = $homeLineup->where('is_starting', true)->sortBy('jersey_number');
                    $homeSubs = $homeLineup->where('is_starting', false)->sortBy('jersey_number');
                @endphp

                @if($homeStarters->isNotEmpty())
                    <h6 class="fw-bold text-success mb-2">{{ __('app.starting_xi') }} ({{ $homeStarters->count() }})</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-3">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('app.player') }}</th>
                                    <th>{{ __('app.position') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($homeStarters as $lineup)
                                    <tr>
                                        <td class="fw-bold">{{ $lineup->jersey_number }}</td>
                                        <td>{{ $lineup->player->name ?? '-' }}</td>
                                        <td>{{ strtoupper(substr($lineup->position ?? '-', 0, 3)) }}</td>
                                        <td class="text-end">
                                            <form action="{{ route('matches.lineup.destroy', [$match, $lineup]) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Remove this player from lineup?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if($homeSubs->isNotEmpty())
                    <h6 class="fw-bold text-info mb-2">{{ __('app.substitutes') }} ({{ $homeSubs->count() }})</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('app.player') }}</th>
                                    <th>{{ __('app.position') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($homeSubs as $lineup)
                                    <tr>
                                        <td class="fw-bold">{{ $lineup->jersey_number }}</td>
                                        <td>{{ $lineup->player->name ?? '-' }}</td>
                                        <td>{{ strtoupper(substr($lineup->position ?? '-', 0, 3)) }}</td>
                                        <td class="text-end">
                                            <form action="{{ route('matches.lineup.destroy', [$match, $lineup]) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Remove this player from lineup?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if($homeLineup->isEmpty())
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-users fa-2x mb-2 d-block"></i>
                        <p class="mb-0">No players added to home lineup yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Away Team Lineup -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-shield-halved me-2"></i>{{ $match->awayTeam->name ?? __('app.away_team') }}
                </h5>
            </div>
            <div class="card-body">
                <!-- Add Player to Away Lineup -->
                <form action="{{ route('matches.lineup.store', $match) }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="team_id" value="{{ $match->away_team_id }}">

                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label for="away_player_id" class="form-label fw-semibold small">{{ __('app.player') }}</label>
                            <select class="form-select form-select-sm" id="away_player_id" name="player_id" required>
                                <option value="">-- Select --</option>
                                @foreach($awayPlayers as $player)
                                    <option value="{{ $player->id }}">
                                        #{{ $player->jersey_number }} {{ $player->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="away_jersey" class="form-label fw-semibold small">{{ __('app.jersey_number') }}</label>
                            <input type="number" class="form-select form-select-sm" id="away_jersey" name="jersey_number" min="1" max="99">
                        </div>
                        <div class="col-md-3">
                            <label for="away_position" class="form-label fw-semibold small">{{ __('app.position') }}</label>
                            <select class="form-select form-select-sm" id="away_position" name="position">
                                <option value="goalkeeper">GK</option>
                                <option value="defender">DEF</option>
                                <option value="midfielder">MID</option>
                                <option value="forward">FWD</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="away_starting" name="is_starting" value="1" checked>
                                <label class="form-check-label small" for="away_starting">{{ __('app.start') }}</label>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-sm btn-success" title="Add">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Current Away Lineup -->
                @php
                    $awayStarters = $awayLineup->where('is_starting', true)->sortBy('jersey_number');
                    $awaySubs = $awayLineup->where('is_starting', false)->sortBy('jersey_number');
                @endphp

                @if($awayStarters->isNotEmpty())
                    <h6 class="fw-bold text-success mb-2">{{ __('app.starting_xi') }} ({{ $awayStarters->count() }})</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-3">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('app.player') }}</th>
                                    <th>{{ __('app.position') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($awayStarters as $lineup)
                                    <tr>
                                        <td class="fw-bold">{{ $lineup->jersey_number }}</td>
                                        <td>{{ $lineup->player->name ?? '-' }}</td>
                                        <td>{{ strtoupper(substr($lineup->position ?? '-', 0, 3)) }}</td>
                                        <td class="text-end">
                                            <form action="{{ route('matches.lineup.destroy', [$match, $lineup]) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Remove this player from lineup?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if($awaySubs->isNotEmpty())
                    <h6 class="fw-bold text-info mb-2">{{ __('app.substitutes') }} ({{ $awaySubs->count() }})</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('app.player') }}</th>
                                    <th>{{ __('app.position') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($awaySubs as $lineup)
                                    <tr>
                                        <td class="fw-bold">{{ $lineup->jersey_number }}</td>
                                        <td>{{ $lineup->player->name ?? '-' }}</td>
                                        <td>{{ strtoupper(substr($lineup->position ?? '-', 0, 3)) }}</td>
                                        <td class="text-end">
                                            <form action="{{ route('matches.lineup.destroy', [$match, $lineup]) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Remove this player from lineup?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if($awayLineup->isEmpty())
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-users fa-2x mb-2 d-block"></i>
                        <p class="mb-0">No players added to away lineup yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
