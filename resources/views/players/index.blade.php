@extends('layouts.app')

@section('title', 'Players')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-users text-success me-2"></i>Players
    </h2>
    @auth
        <a href="{{ route('players.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> Add Player
        </a>
    @endauth
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('players.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label fw-semibold">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Search by player name...">
            </div>
            <div class="col-md-3">
                <label for="team" class="form-label fw-semibold">Filter by Team</label>
                <select class="form-select" id="team" name="team">
                    <option value="">All Teams</option>
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
                <label for="position" class="form-label fw-semibold">Position</label>
                <select class="form-select" id="position" name="position">
                    <option value="">All Positions</option>
                    <option value="goalkeeper" {{ request('position') === 'goalkeeper' ? 'selected' : '' }}>Goalkeeper</option>
                    <option value="defender" {{ request('position') === 'defender' ? 'selected' : '' }}>Defender</option>
                    <option value="midfielder" {{ request('position') === 'midfielder' ? 'selected' : '' }}>Midfielder</option>
                    <option value="forward" {{ request('position') === 'forward' ? 'selected' : '' }}>Forward</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

@if($players->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">No players found</h5>
            <p class="text-muted">Players will appear here once registered.</p>
        </div>
    </div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Team</th>
                        <th class="text-center">Jersey #</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
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
                                    <span class="badge bg-success">Active</span>
                                @elseif($player->status === 'suspended')
                                    <span class="badge bg-danger">Suspended</span>
                                @elseif($player->status === 'injured')
                                    <span class="badge bg-warning text-dark">Injured</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($player->status ?? 'unknown') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('players.show', $player) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @auth
                                    @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->team_id == $player->team_id))
                                        <a href="{{ route('players.edit', $player) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('players.destroy', $player) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this player?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
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
