@extends('layouts.app')

@section('title', $competition->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-trophy text-success me-2"></i>{{ $competition->name }}
    </h2>
    <div class="d-flex gap-2">
        @auth
            @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                <a href="{{ route('competitions.edit', $competition) }}" class="btn btn-outline-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            @endif
        @endauth
        <a href="{{ route('competitions.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<!-- Competition Details Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width: 140px;">Season</th>
                        <td class="fw-semibold">{{ $competition->season }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Type</th>
                        <td>
                            @if($competition->type === 'league')
                                <span class="badge bg-primary">League</span>
                            @elseif($competition->type === 'knockout' || $competition->type === 'cup')
                                <span class="badge bg-warning text-dark">Knockout</span>
                            @elseif($competition->type === 'futsal')
                                <span class="badge bg-info">Futsal</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($competition->type) }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Status</th>
                        <td>
                            @if($competition->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($competition->status === 'upcoming')
                                <span class="badge bg-info">Upcoming</span>
                            @elseif($competition->status === 'completed')
                                <span class="badge bg-secondary">Completed</span>
                            @else
                                <span class="badge bg-dark">{{ ucfirst($competition->status) }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width: 140px;">Start Date</th>
                        <td>{{ $competition->start_date ? $competition->start_date->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">End Date</th>
                        <td>{{ $competition->end_date ? $competition->end_date->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Description</th>
                        <td>{{ $competition->description ?: 'No description provided.' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" id="competitionTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="teams-tab" data-bs-toggle="tab" data-bs-target="#teams-pane"
                type="button" role="tab" aria-controls="teams-pane" aria-selected="true">
            <i class="fas fa-shield-halved me-1"></i> Teams
            <span class="badge bg-secondary ms-1">{{ $competition->teams->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="fixtures-tab" data-bs-toggle="tab" data-bs-target="#fixtures-pane"
                type="button" role="tab" aria-controls="fixtures-pane" aria-selected="false">
            <i class="fas fa-calendar-days me-1"></i> Fixtures
            <span class="badge bg-secondary ms-1">{{ $competition->matchGames->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="standings-tab" data-bs-toggle="tab" data-bs-target="#standings-pane"
                type="button" role="tab" aria-controls="standings-pane" aria-selected="false">
            <i class="fas fa-ranking-star me-1"></i> Standings
        </button>
    </li>
</ul>

<div class="tab-content" id="competitionTabsContent">
    <!-- Teams Tab -->
    <div class="tab-pane fade show active" id="teams-pane" role="tabpanel" aria-labelledby="teams-tab">
        @if($competition->teams->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-shield-halved fa-3x mb-3 d-block"></i>
                <p>No teams registered for this competition yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Team Name</th>
                            <th>Short Name</th>
                            <th>Manager</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($competition->teams as $index => $team)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $team->name }}</td>
                                <td>{{ $team->short_name ?? '-' }}</td>
                                <td>{{ $team->manager_name ?? '-' }}</td>
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
                                <td class="text-center">
                                    <a href="{{ route('teams.show', $team) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Fixtures Tab -->
    <div class="tab-pane fade" id="fixtures-pane" role="tabpanel" aria-labelledby="fixtures-tab">
        @if($competition->matchGames->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-xmark fa-3x mb-3 d-block"></i>
                <p>No fixtures scheduled for this competition yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Matchday</th>
                            <th>Date</th>
                            <th>Home Team</th>
                            <th class="text-center">Score</th>
                            <th>Away Team</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($competition->matchGames->sortBy('match_date') as $match)
                            <tr>
                                <td>{{ $match->matchday ?? '-' }}</td>
                                <td>{{ $match->match_date ? $match->match_date->format('d M Y H:i') : '-' }}</td>
                                <td class="fw-semibold">{{ $match->homeTeam->name ?? '-' }}</td>
                                <td class="text-center">
                                    @if($match->status === 'completed')
                                        <span class="badge bg-dark fs-6">{{ $match->home_score }} - {{ $match->away_score }}</span>
                                    @else
                                        <span class="text-muted">vs</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $match->awayTeam->name ?? '-' }}</td>
                                <td>
                                    @if($match->status === 'completed')
                                        <span class="badge bg-secondary">Completed</span>
                                    @elseif($match->status === 'scheduled')
                                        <span class="badge bg-info">Scheduled</span>
                                    @elseif($match->status === 'in_progress')
                                        <span class="badge bg-success">In Progress</span>
                                    @elseif($match->status === 'postponed')
                                        <span class="badge bg-warning text-dark">Postponed</span>
                                    @elseif($match->status === 'cancelled')
                                        <span class="badge bg-danger">Cancelled</span>
                                    @else
                                        <span class="badge bg-dark">{{ ucfirst($match->status ?? 'unknown') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('matches.show', $match) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Standings Tab -->
    <div class="tab-pane fade" id="standings-pane" role="tabpanel" aria-labelledby="standings-tab">
        @if($standings->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-table fa-3x mb-3 d-block"></i>
                <p>No standings data available yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;" class="text-center">Pos</th>
                            <th>Team</th>
                            <th class="text-center">P</th>
                            <th class="text-center">W</th>
                            <th class="text-center">D</th>
                            <th class="text-center">L</th>
                            <th class="text-center">GF</th>
                            <th class="text-center">GA</th>
                            <th class="text-center">GD</th>
                            <th class="text-center fw-bold">Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($standings as $standing)
                            <tr>
                                <td class="text-center fw-bold">{{ $standing->position }}</td>
                                <td class="fw-semibold">{{ $standing->team->name ?? '-' }}</td>
                                <td class="text-center">{{ $standing->played }}</td>
                                <td class="text-center">{{ $standing->won }}</td>
                                <td class="text-center">{{ $standing->drawn }}</td>
                                <td class="text-center">{{ $standing->lost }}</td>
                                <td class="text-center">{{ $standing->goals_for }}</td>
                                <td class="text-center">{{ $standing->goals_against }}</td>
                                <td class="text-center">{{ $standing->goal_difference }}</td>
                                <td class="text-center fw-bold">{{ $standing->points }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
