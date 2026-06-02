@extends('layouts.app')

@section('title', $team->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-shield-halved text-success me-2"></i>{{ $team->name }}
    </h2>
    <div class="d-flex gap-2">
        @auth
            @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                <a href="{{ route('teams.edit', $team) }}" class="btn btn-outline-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            @endif
        @endauth
        <a href="{{ route('teams.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<!-- Team Details Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width: 150px;">Team Name</th>
                        <td class="fw-semibold">{{ $team->name }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Short Name</th>
                        <td>{{ $team->short_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Competition</th>
                        <td>
                            @if($team->competition)
                                <a href="{{ route('competitions.show', $team->competition) }}">
                                    {{ $team->competition->name }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width: 150px;">Status</th>
                        <td>
                            @if($team->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($team->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($team->status === 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($team->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Manager</th>
                        <td>{{ $team->manager_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Contact Email</th>
                        <td>{{ $team->contact_email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Contact Phone</th>
                        <td>{{ $team->contact_phone ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Players Section -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i>Players
            <span class="badge bg-secondary ms-1">{{ $team->players->count() }}</span>
        </h5>
        @auth
            @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->team_id == $team->id))
                <a href="{{ route('players.create', ['team_id' => $team->id]) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus me-1"></i> Add Player
                </a>
            @endif
        @endauth
    </div>
    <div class="card-body p-0">
        @if($team->players->isEmpty())
            <div class="text-center text-muted py-4">
                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                <p class="mb-0">No players registered for this team yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;" class="text-center">Jersey #</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($team->players->sortBy('jersey_number') as $player)
                            <tr>
                                <td class="text-center fw-bold">{{ $player->jersey_number ?? '-' }}</td>
                                <td class="fw-semibold">{{ $player->name }}</td>
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
                                        @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->team_id == $team->id))
                                            <a href="{{ route('players.edit', $player) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    @endauth
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Officials Section -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-user-tie me-2"></i>Officials
            <span class="badge bg-secondary ms-1">{{ $team->officials->count() }}</span>
        </h5>
        @auth
            @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->team_id == $team->id))
                <a href="{{ route('officials.create', ['team_id' => $team->id]) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus me-1"></i> Add Official
                </a>
            @endif
        @endauth
    </div>
    <div class="card-body p-0">
        @if($team->officials->isEmpty())
            <div class="text-center text-muted py-4">
                <i class="fas fa-user-tie fa-2x mb-2 d-block"></i>
                <p class="mb-0">No officials registered for this team yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Contact Phone</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($team->officials as $official)
                            <tr>
                                <td class="fw-semibold">{{ $official->name }}</td>
                                <td>{{ ucfirst($official->role ?? '-') }}</td>
                                <td>{{ $official->contact_phone ?? '-' }}</td>
                                <td class="text-center">
                                    @auth
                                        @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->team_id == $team->id))
                                            <a href="{{ route('officials.edit', $official) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('officials.destroy', $official) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to remove this official?');">
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
        @endif
    </div>
</div>
@endsection
