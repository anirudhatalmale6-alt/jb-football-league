@extends('layouts.app')

@section('title', $player->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-user text-success me-2"></i>{{ $player->name }}
    </h2>
    <div class="d-flex gap-2">
        @auth
            @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->team_id == $player->team_id))
                <a href="{{ route('players.edit', $player) }}" class="btn btn-outline-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            @endif
        @endauth
        <a href="{{ route('players.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width: 150px;">Full Name</th>
                        <td class="fw-semibold">{{ $player->name }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Team</th>
                        <td>
                            @if($player->team)
                                <a href="{{ route('teams.show', $player->team) }}">{{ $player->team->name }}</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Jersey Number</th>
                        <td>
                            @if($player->jersey_number)
                                <span class="badge bg-dark fs-6">#{{ $player->jersey_number }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Position</th>
                        <td>{{ ucfirst($player->position ?? '-') }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width: 150px;">Date of Birth</th>
                        <td>{{ $player->date_of_birth ? $player->date_of_birth->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Nationality</th>
                        <td>{{ $player->nationality ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">IC Number</th>
                        <td>{{ $player->ic_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Status</th>
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
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
