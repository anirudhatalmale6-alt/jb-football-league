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
            <div class="col-md-3">
                <label for="search" class="form-label fw-semibold">{{ __('app.search') }}</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Search by player name...">
            </div>
            <div class="col-md-3">
                <label for="team" class="form-label fw-semibold">{{ __('app.filter_by_team') }}</label>
                <select class="form-select" id="team" name="team_id">
                    <option value="">{{ __('app.all_teams') }}</option>
                    @if(isset($teams))
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ request('team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-2">
                <label for="position" class="form-label fw-semibold">{{ __('app.position') }}</label>
                <select class="form-select" id="position" name="position">
                    <option value="">{{ __('app.all_positions') }}</option>
                    <option value="goalkeeper" {{ request('position') === 'goalkeeper' ? 'selected' : '' }}>{{ __('app.goalkeeper') }}</option>
                    <option value="defender" {{ request('position') === 'defender' ? 'selected' : '' }}>{{ __('app.defender') }}</option>
                    <option value="midfielder" {{ request('position') === 'midfielder' ? 'selected' : '' }}>{{ __('app.midfielder') }}</option>
                    <option value="forward" {{ request('position') === 'forward' ? 'selected' : '' }}>{{ __('app.forward') }}</option>
                </select>
            </div>
            @if($isAdmin ?? false)
            <div class="col-md-2">
                <label class="form-label fw-semibold">Status</label>
                <select class="form-select" name="flagged">
                    <option value="">All Players</option>
                    <option value="1" {{ request('flagged') ? 'selected' : '' }}>Flagged Only</option>
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-search me-1"></i> {{ __('app.filter') }}
                </button>
                <a href="{{ route('players.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-undo me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

@if(($isAdmin ?? false) && ($showFlaggedOnly ?? false))
<div class="alert alert-info mb-3">
    <i class="fas fa-filter me-2"></i>Showing flagged players only - these need admin review.
</div>
@endif

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
                        <th style="width:50px;"></th>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.team') }}</th>
                        <th>{{ __('app.competitions_joined') }}</th>
                        <th class="text-center">{{ __('app.jersey_number') }}</th>
                        <th>{{ __('app.position') }}</th>
                        <th class="text-center">Age</th>
                        @if($isAdmin ?? false)
                        <th class="text-center">IC</th>
                        <th class="text-center">Photo</th>
                        <th class="text-center">IC Image</th>
                        @endif
                        <th>{{ __('app.status') }}</th>
                        <th class="text-center">Verification</th>
                        <th class="text-center">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($players as $player)
                        <tr @if(($isAdmin ?? false) && !empty($player->_flags)) class="table-warning" @endif>
                            <td class="text-center">
                                @if($player->photo)
                                    <img src="{{ asset('storage/' . $player->photo) }}" alt="" class="rounded-circle" style="width:35px; height:35px; object-fit:cover;">
                                @else
                                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width:35px; height:35px;">
                                        <i class="fas fa-user text-muted" style="font-size:0.8rem;"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="fw-semibold">
                                {{ $player->name }}
                                @if($player->is_u23)<span class="badge bg-warning text-dark" style="font-size:0.65rem;">U23</span>@endif
                                @if(($isAdmin ?? false) && !empty($player->_flags))
                                    <br><small class="text-danger"><i class="fas fa-flag me-1"></i>{{ implode(', ', $player->_flags) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($player->team)
                                    <a href="{{ route('teams.show', $player->team) }}">{{ $player->team->name }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if(!empty($player->_competitions) && count($player->_competitions))
                                    @foreach($player->_competitions as $comp)
                                        <span class="badge bg-info text-dark mb-1" style="font-size:0.65rem;">{{ $comp }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold">{{ $player->jersey_number ?? '-' }}</td>
                            <td>{{ ucfirst($player->position ?? '-') }}</td>
                            <td class="text-center">{{ $player->age ?? '-' }}</td>
                            @if($isAdmin ?? false)
                            <td class="text-center">
                                @if($player->ic_number)
                                    <span class="badge bg-success" title="{{ $player->ic_number }}"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times"></i></span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($player->photo)
                                    <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times"></i></span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($player->ic_photo)
                                    <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times"></i></span>
                                @endif
                            </td>
                            @endif
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
                                @if($player->verification_status === 'verified')
                                    <span class="badge bg-success"><i class="fas fa-check-circle"></i></span>
                                @elseif($player->verification_status === 'flagged')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-flag"></i></span>
                                @elseif($player->verification_status === 'rejected')
                                    <span class="badge bg-danger"><i class="fas fa-times-circle"></i></span>
                                @else
                                    <span class="badge bg-success"><i class="fas fa-check-circle"></i></span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('players.show', $player) }}" class="btn btn-sm btn-outline-primary" title="{{ __('app.view') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @auth
                                    @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($player->team_id)))
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