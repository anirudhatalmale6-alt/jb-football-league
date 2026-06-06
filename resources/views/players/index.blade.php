@extends('layouts.app')

@section('title', __('app.players'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-users text-success me-2"></i>{{ __('app.players') }}
    </h2>
    @auth
        <a href="{{ route('players.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> {{ __('app.add_player') }}
        </a>
    @endauth
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('players.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label fw-semibold">{{ __('app.search') }}</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Search by player name...">
            </div>
            <div class="col-md-3">
                <label for="team" class="form-label fw-semibold">{{ __('app.filter_by_team') }}</label>
                <select class="form-select" id="team" name="team">
                    <option value="">{{ __('app.all_teams') }}</option>
                    @if(isset($teams))
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ request('team') == $team->id ? 'selected' : '' }}>
                                {{ $team->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-3">
                <label for="position" class="form-label fw-semibold">{{ __('app.position') }}</label>
                <select class="form-select" id="position" name="position">
                    <option value="">{{ __('app.all_positions') }}</option>
                    <option value="goalkeeper" {{ request('position') === 'goalkeeper' ? 'selected' : '' }}>{{ __('app.goalkeeper') }}</option>
                    <option value="defender" {{ request('position') === 'defender' ? 'selected' : '' }}>{{ __('app.defender') }}</option>
                    <option value="midfielder" {{ request('position') === 'midfielder' ? 'selected' : '' }}>{{ __('app.midfielder') }}</option>
                    <option value="forward" {{ request('position') === 'forward' ? 'selected' : '' }}>{{ __('app.forward') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> {{ __('app.filter') }}
                </button>
            </div>
        </form>
    </div>
</div>

@if($players->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">{{ __('app.no_players_found') }}</h5>
            <p class="text-muted">Players will appear here once registered.</p>
        </div>
    </div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.team') }}</th>
                        <th class="text-center">{{ __('app.jersey_number') }}</th>
                        <th>{{ __('app.position') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-center">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($players as $player)
                        <tr>
                            <td class="fw-semibold">{{ $player->name }}</td>
                            <td>
                                @if($player->team)
                                    <a href="{{ route('teams.show', $player->team) }}">{{ $player->team->name }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center fw-bold">{{ $player->jersey_number ?? '-' }}</td>
                            <td>{{ ucfirst($player->position ?? '-') }}</td>
                            <td>
                                @if($player->status === 'active')
                                    <span class="badge bg-success">{{ __('app.active') }}</span>
                                @elseif($player->status === 'suspended')
                                    <span class="badge bg-danger">{{ __('app.suspended') }}</span>
                                @elseif($player->status === 'injured')
                                    <span class="badge bg-warning text-dark">{{ __('app.injured') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($player->status ?? 'unknown') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('players.show', $player) }}" class="btn btn-sm btn-outline-primary" title="{{ __('app.view') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @auth
                                    @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->team_id == $player->team_id))
                                        <a href="{{ route('players.edit', $player) }}" class="btn btn-sm btn-outline-warning" title="{{ __('app.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('players.destroy', $player) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this player?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('app.delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endauth
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $players->appends(request()->query())->links() }}
    </div>
@endif
@endsection
