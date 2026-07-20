@extends('layouts.app')

@section('title', $competition->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0 d-flex align-items-center">
        @if($competition->logo)
            <img src="{{ asset('storage/'.$competition->logo) }}" alt="" class="me-2" style="height:64px;width:64px;object-fit:contain;">
        @else
            <i class="fas fa-trophy text-success me-2"></i>
        @endif
        {{ $competition->name }}
    </h2>
    <div class="d-flex gap-2">
        @auth
            @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                <a href="{{ route('competitions.edit', $competition) }}" class="btn btn-outline-warning">
                    <i class="fas fa-edit me-1"></i> {{ __('app.edit') }}
                </a>
            @endif
        @endauth
        <a href="{{ route('competitions.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> {{ __('app.back') }}
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
                        <th class="text-muted" style="width: 140px;">{{ __('app.season') }}</th>
                        <td class="fw-semibold">{{ $competition->season }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('app.type') }}</th>
                        <td>
                            @if($competition->type === 'league')
                                <span class="badge bg-primary">{{ __('app.league') }}</span>
                            @elseif($competition->type === 'knockout' || $competition->type === 'cup')
                                <span class="badge bg-warning text-dark">{{ __('app.knockout') }}</span>
                            @elseif($competition->type === 'futsal')
                                <span class="badge bg-info">{{ __('app.futsal') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($competition->type) }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('app.status') }}</th>
                        <td>
                            @if($competition->status === 'active')
                                <span class="badge bg-success">{{ __('app.active') }}</span>
                            @elseif($competition->status === 'upcoming')
                                <span class="badge bg-info">{{ __('app.upcoming') }}</span>
                            @elseif($competition->status === 'completed')
                                <span class="badge bg-secondary">{{ __('app.completed') }}</span>
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
                        <th class="text-muted" style="width: 140px;">{{ __('app.start_date') }}</th>
                        <td>{{ $competition->start_date ? $competition->start_date->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('app.end_date') }}</th>
                        <td>{{ $competition->end_date ? $competition->end_date->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('app.description') }}</th>
                        <td>{{ $competition->description ?: __('app.no_description') }}</td>
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
            <i class="fas fa-shield-halved me-1"></i> {{ __('app.teams') }}
            <span class="badge bg-secondary ms-1">{{ $competition->teams->count() }}</span>
        </button>
    </li>
    @if($competition->type !== 'knockout')
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="groups-tab" data-bs-toggle="tab" data-bs-target="#groups-pane"
                type="button" role="tab" aria-controls="groups-pane" aria-selected="false">
            <i class="fas fa-layer-group me-1"></i> {{ __('app.groups') }}
            <span class="badge bg-secondary ms-1">{{ $competition->groups->count() }}</span>
        </button>
    </li>
    @endif
    @if($competition->type === 'knockout')
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="knockout-tab" data-bs-toggle="tab" data-bs-target="#knockout-pane"
                type="button" role="tab" aria-controls="knockout-pane" aria-selected="false">
            <i class="fas fa-sitemap me-1"></i> {{ __('app.knockout_stage') }}
        </button>
    </li>
    @endif
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="fixtures-tab" data-bs-toggle="tab" data-bs-target="#fixtures-pane"
                type="button" role="tab" aria-controls="fixtures-pane" aria-selected="false">
            <i class="fas fa-calendar-days me-1"></i> {{ __('app.fixtures') }}
            <span class="badge bg-secondary ms-1">{{ $competition->matchGames->count() }}</span>
        </button>
    </li>
    @if($competition->type !== 'knockout')
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="standings-tab" data-bs-toggle="tab" data-bs-target="#standings-pane"
                type="button" role="tab" aria-controls="standings-pane" aria-selected="false">
            <i class="fas fa-ranking-star me-1"></i> {{ __('app.standings') }}
        </button>
    </li>
    @endif
</ul>

@php
    $canSeeStatus = auth()->check() && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin());
@endphp
<div class="tab-content" id="competitionTabsContent">
    <!-- Teams Tab -->
    <div class="tab-pane fade show active" id="teams-pane" role="tabpanel" aria-labelledby="teams-tab">
        @if($competition->teams->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-shield-halved fa-3x mb-3 d-block"></i>
                <p>{{ __('app.no_teams_registered') }}</p>
            </div>
        @elseif($competition->groups->isNotEmpty())
            {{-- Teams organized by group --}}
            @foreach($competition->groups->sortBy('order') as $group)
                <h5 class="fw-bold mt-3 mb-2">
                    <i class="fas fa-layer-group me-1 text-primary"></i> {{ $group->name }}
                    <span class="badge bg-secondary ms-1">{{ $group->teams->count() }}</span>
                </h5>
                @if($group->teams->isEmpty())
                    <p class="text-muted ms-3">{{ __('app.no_teams_in_group') }}</p>
                @else
                    <div class="table-responsive mb-3">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('app.team_name') }}</th>
                                    <th>{{ __('app.short_name') }}</th>
                                    <th>{{ __('app.manager') }}</th>
                                    @if($canSeeStatus)<th>{{ __('app.status') }}</th>@endif
                                    <th class="text-center">{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($group->teams as $index => $team)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="fw-semibold">
                                        {{ $team->name }}
                                        @auth
                                            @if(auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))
                                                <span class="badge bg-primary ms-1"><i class="fas fa-star me-1"></i>{{ __('app.your_team') }}</span>
                                            @endif
                                        @endauth
                                    </td>
                                        <td>{{ $team->short_name ?? '-' }}</td>
                                        <td>{{ $team->manager_name ?? '-' }}</td>
                                        @if($canSeeStatus)
                                        <td>
                                            @if($team->status === 'approved')
                                                <span class="badge bg-success">{{ __('app.approved') }}</span>
                                            @elseif($team->status === 'pending')
                                                <span class="badge bg-warning text-dark">{{ __('app.pending') }}</span>
                                            @elseif($team->status === 'rejected')
                                                <span class="badge bg-danger">{{ __('app.rejected') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($team->status) }}</span>
                                            @endif
                                        </td>
                                        @endif
                                        <td class="text-center">
                                            @auth
                                                @if(auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))
                                                    <a href="{{ route('teams.show', $team) }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-arrow-right me-1"></i>{{ __('app.continue_btn') }}
                                                    </a>
                                                @else
                                                    <a href="{{ route('teams.show', $team) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> {{ __('app.view') }}
                                                    </a>
                                                @endif
                                            @else
                                                <a href="{{ route('teams.show', $team) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> {{ __('app.view') }}
                                                </a>
                                            @endauth
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endforeach

            {{-- Teams without a group --}}
            @php $ungroupedTeams = $competition->teams->whereNull('group_id'); @endphp
            @if($ungroupedTeams->isNotEmpty())
                <h5 class="fw-bold mt-3 mb-2">
                    <i class="fas fa-question-circle me-1 text-muted"></i> {{ __('app.ungrouped') }}
                    <span class="badge bg-secondary ms-1">{{ $ungroupedTeams->count() }}</span>
                </h5>
                <div class="table-responsive mb-3">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Team Name</th>
                                <th>Short Name</th>
                                <th>Manager</th>
                                @if($canSeeStatus)<th>{{ __('app.status') }}</th>@endif
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ungroupedTeams as $index => $team)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">
                                        {{ $team->name }}
                                        @auth
                                            @if(auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))
                                                <span class="badge bg-primary ms-1"><i class="fas fa-star me-1"></i>{{ __('app.your_team') }}</span>
                                            @endif
                                        @endauth
                                    </td>
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
                                        @auth
                                            @if(auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))
                                                <a href="{{ route('teams.show', $team) }}" class="btn btn-sm btn-success">
                                                    <i class="fas fa-arrow-right me-1"></i>{{ __('app.continue_btn') }}
                                                </a>
                                            @else
                                                <a href="{{ route('teams.show', $team) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> {{ __('app.view') }}
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ route('teams.show', $team) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> {{ __('app.view') }}
                                            </a>
                                        @endauth
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>{{ __('app.team_name') }}</th>
                            <th>{{ __('app.short_name') }}</th>
                            <th>{{ __('app.manager') }}</th>
                            @if($canSeeStatus)<th>{{ __('app.status') }}</th>@endif
                            <th class="text-center">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($competition->teams as $index => $team)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">
                                        {{ $team->name }}
                                        @auth
                                            @if(auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))
                                                <span class="badge bg-primary ms-1"><i class="fas fa-star me-1"></i>{{ __('app.your_team') }}</span>
                                            @endif
                                        @endauth
                                    </td>
                                <td>{{ $team->short_name ?? '-' }}</td>
                                <td>{{ $team->manager_name ?? '-' }}</td>
                                @if($canSeeStatus)
                                <td>
                                    @if($team->status === 'approved')
                                        <span class="badge bg-success">{{ __('app.approved') }}</span>
                                    @elseif($team->status === 'pending')
                                        <span class="badge bg-warning text-dark">{{ __('app.pending') }}</span>
                                    @elseif($team->status === 'rejected')
                                        <span class="badge bg-danger">{{ __('app.rejected') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($team->status) }}</span>
                                    @endif
                                </td>
                                @endif
                                <td class="text-center">
                                    @auth
                                        @if(auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))
                                            <a href="{{ route('teams.show', $team) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-arrow-right me-1"></i>{{ __('app.continue_btn') }}
                                            </a>
                                        @else
                                            <a href="{{ route('teams.show', $team) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> {{ __('app.view') }}
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('teams.show', $team) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> {{ __('app.view') }}
                                        </a>
                                    @endauth
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($competition->type !== 'knockout')
    <!-- Groups Tab -->
    <div class="tab-pane fade" id="groups-pane" role="tabpanel" aria-labelledby="groups-tab">
        @auth
            @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">{{ __('app.add_group') }}</h6>
                        <form action="{{ route('competitions.groups.store', $competition) }}" method="POST" class="d-flex gap-2 align-items-end">
                            @csrf
                            <div class="flex-grow-1">
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                                       placeholder="{{ __('app.group_name_placeholder') }}" required maxlength="100">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i> {{ __('app.add_group') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @endauth

        @if($competition->groups->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-layer-group fa-3x mb-3 d-block"></i>
                <p>{{ __('app.no_groups_created') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;" class="text-center">#</th>
                            <th>{{ __('app.group_name') }}</th>
                            <th class="text-center">{{ __('app.teams') }}</th>
                            @auth
                                @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                                    <th class="text-center">{{ __('app.actions') }}</th>
                                @endif
                            @endauth
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($competition->groups->sortBy('order') as $group)
                            <tr>
                                <td class="text-center">{{ $group->order }}</td>
                                <td class="fw-semibold">{{ $group->name }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $group->teams->count() }}</span>
                                </td>
                                @auth
                                    @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                                        <td class="text-center">
                                            <form action="{{ route('competitions.groups.destroy', [$competition, $group]) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('{{ __('app.confirm_delete_group') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('app.delete_group') }}">
                                                    <i class="fas fa-trash"></i> {{ __('app.delete') }}
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                @endauth
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @endif

    <!-- Fixtures Tab -->
    <div class="tab-pane fade" id="fixtures-pane" role="tabpanel" aria-labelledby="fixtures-tab">
        @if($competition->matchGames->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-xmark fa-3x mb-3 d-block"></i>
                <p>{{ __('app.no_fixtures_scheduled') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>{{ __('app.matchday') }}</th>
                            <th>{{ __('app.date') }}</th>
                            <th>{{ __('app.home_team') }}</th>
                            <th class="text-center">{{ __('app.score') }}</th>
                            <th>{{ __('app.away_team') }}</th>
                            @if($canSeeStatus)<th>{{ __('app.status') }}</th>@endif
                            <th class="text-center">{{ __('app.actions') }}</th>
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
                                        <span class="badge bg-secondary">{{ __('app.completed') }}</span>
                                    @elseif($match->status === 'scheduled')
                                        <span class="badge bg-info">{{ __('app.scheduled') }}</span>
                                    @elseif($match->status === 'in_progress')
                                        <span class="badge bg-success">{{ __('app.in_progress') }}</span>
                                    @elseif($match->status === 'postponed')
                                        <span class="badge bg-warning text-dark">{{ __('app.postponed') }}</span>
                                    @elseif($match->status === 'cancelled')
                                        <span class="badge bg-danger">{{ __('app.cancelled') }}</span>
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

    @if($competition->type !== 'knockout')
    <!-- Standings Tab -->
    <div class="tab-pane fade" id="standings-pane" role="tabpanel" aria-labelledby="standings-tab">
        @if($standings->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-table fa-3x mb-3 d-block"></i>
                <p>{{ __('app.no_standings_data') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;" class="text-center">Pos</th>
                            <th>{{ __('app.team') }}</th>
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
    @endif

    @if($competition->type === 'knockout')
    <!-- Knockout Stage Tab -->
    <div class="tab-pane fade" id="knockout-pane" role="tabpanel" aria-labelledby="knockout-tab">
        @include('competitions.knockout')
    </div>
    @endif
</div>

@if($competition->type === 'knockout')
@push('styles')
<style>
.bracket-container { overflow-x: auto; padding: 10px 0; }
.bracket-scroll { display: flex; gap: 8px; min-width: max-content; align-items: flex-start; }
.bracket-round { display: flex; flex-direction: column; gap: 8px; min-width: 160px; }
.bracket-round-title { text-align: center; margin-bottom: 4px; }
.bracket-match { background: #fff; border: 2px solid #dee2e6; border-radius: 6px; padding: 4px 6px; position: relative; }
.bracket-match.completed { border-color: #198754; }
.bracket-match-num { font-size: 9px; color: #999; text-align: center; margin-bottom: 2px; }
.bracket-team { display: flex; justify-content: space-between; align-items: center; padding: 4px 6px; border-radius: 3px; margin: 1px 0; font-size: 12px; background: #f8f9fa; }
.bracket-team.winner { background: #d1e7dd; font-weight: bold; }
.team-name { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 110px; }
.team-score { font-weight: bold; min-width: 30px; text-align: right; }
.team-score small { font-size: 9px; color: #666; }
.bracket-admin { text-align: center; }
.champion-slot { border-color: #ffc107; border-width: 3px; background: #fff8e1; }
.champion-slot .bracket-team { background: #fff3cd; font-size: 14px; }
@media (max-width: 768px) {
    .bracket-round { min-width: 140px; }
    .team-name { max-width: 85px; font-size: 11px; }
}
</style>
@endpush

@push('scripts')
<script>
function showSeedModal(kmId, round, pos, hasHome, hasAway) {
    document.getElementById('seed_km_id').value = kmId;
    var sideSelect = document.getElementById('seed_side');
    if (hasHome && !hasAway) {
        sideSelect.value = 'away';
    } else if (!hasHome && hasAway) {
        sideSelect.value = 'home';
    } else {
        sideSelect.value = 'home';
    }
    new bootstrap.Modal(document.getElementById('seedModal')).show();
}

function showWinnerModal(kmId, homeName, homeId, awayName, awayId) {
    var form = document.getElementById('winnerForm');
    form.action = '/competitions/' + {{ $competition->id }} + '/knockout/' + kmId + '/winner';
    var select = document.getElementById('winner_select');
    select.innerHTML = '<option value="' + homeId + '">' + homeName + '</option><option value="' + awayId + '">' + awayName + '</option>';
    document.getElementById('pen_home_label').textContent = homeName + ' Pen';
    document.getElementById('pen_away_label').textContent = awayName + ' Pen';
    new bootstrap.Modal(document.getElementById('winnerModal')).show();
}
</script>
@endpush
@endif

@endsection
