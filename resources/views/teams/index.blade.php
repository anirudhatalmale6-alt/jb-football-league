@extends('layouts.app')

@section('title', __('app.teams'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-shield-halved text-success me-2"></i>{{ __('app.teams') }}
    </h2>
    @auth
        @if(auth()->user()->isTeamManager() && !auth()->user()->hasTeams())
        <a href="{{ route('teams.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> {{ __('app.register_team') }}
        </a>
        @elseif(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
        <a href="{{ route('teams.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> {{ __('app.register_team') }}
        </a>
        @endif
    @endauth
</div>

<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('teams.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label fw-semibold">{{ __('app.search') }}</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="{{ __('app.search') }}...">
            </div>
            <div class="col-md-3">
                <label for="competition" class="form-label fw-semibold">{{ __('app.competition') }}</label>
                <select class="form-select" id="competition" name="competition">
                    <option value="">{{ __('app.all_competitions') }}</option>
                    @if(isset($competitions))
                        @foreach($competitions as $comp)
                            <option value="{{ $comp->id }}" {{ request('competition') == $comp->id ? 'selected' : '' }}>
                                {{ $comp->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            @auth
            @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
            <div class="col-md-3">
                <label for="status" class="form-label fw-semibold">{{ __('app.status') }}</label>
                <select class="form-select" id="status" name="status">
                    <option value="">{{ __('app.all_statuses') }}</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('app.approved') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('app.rejected') }}</option>
                    <option value="withdrawn" {{ request('status') === 'withdrawn' ? 'selected' : '' }}>{{ __('app.withdrawn') }}</option>
                </select>
            </div>
            @endif
            @endauth
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> {{ __('app.filter') }}
                </button>
            </div>
        </form>
    </div>
</div>

@if($teams->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-shield-halved fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">{{ __('app.no_teams_found') }}</h5>
            <p class="text-muted">{{ __('app.teams_will_appear') }}</p>
        </div>
    </div>
@else

    {{-- ADMIN / LEAGUE ADMIN: Table View --}}
    @auth
    @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.competition') }}</th>
                        <th>{{ __('app.field_name') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th>Submission Date</th>
                        <th class="text-center">{{ __('app.officials') }}</th>
                        <th class="text-center">{{ __('app.players') }}</th>
                        <th class="text-center">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teams as $team)
                        <tr>
                            <td class="fw-semibold">
                                @if($team->logo)
                                    <img src="{{ asset('storage/' . $team->logo) }}" alt="" style="width:28px;height:28px;object-fit:contain;" class="me-2">
                                @endif
                                {{ $team->name }}
                            </td>
                            <td>
                                @php $comps = $team->all_competitions ?? collect([$team]); @endphp
                                @foreach($comps as $c)
                                    <div class="mb-1">
                                        <a href="{{ route('teams.show', $c) }}" class="badge bg-dark text-decoration-none" title="{{ __('app.view') }}">{{ $c->competition->name ?? '-' }}</a>
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                @php $comps = $team->all_competitions ?? collect([$team]); @endphp
                                @foreach($comps as $c)
                                    <div class="mb-1 small">
                                        @if($c->venue_name)
                                            <i class="fas fa-map-marker-alt text-success me-1"></i>{{ $c->venue_name }}
                                            @if($c->venue_location)<br><span class="text-muted" style="font-size:0.75rem;">{{ $c->venue_location }}</span>@endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                @php $comps = $team->all_competitions ?? collect([$team]); @endphp
                                @foreach($comps as $c)
                                    <div class="mb-1">
                                        @if($c->status === 'approved')
                                            <span class="badge bg-success">{{ __('app.approved') }}</span>
                                        @elseif($c->status === 'pending')
                                            <span class="badge bg-warning text-dark">{{ __('app.pending') }}</span>
                                        @elseif($c->status === 'rejected')
                                            <span class="badge bg-danger">{{ __('app.rejected') }}</span>
                                        @elseif($c->status === 'withdrawn')
                                            <span class="badge bg-secondary">{{ __('app.withdrawn') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($c->status) }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                <small class="text-muted">{{ $team->created_at->format('d M Y') }}</small>
                                <br><small class="text-muted" style="font-size:0.75rem;">{{ $team->created_at->format('h:i A') }}</small>
                                @if($team->resubmitted_at)
                                    <br><small class="text-warning" title="Resubmitted"><i class="fas fa-redo me-1"></i>{{ $team->resubmitted_at->format('d M Y') }}</small>
                                    <br><small class="text-warning" style="font-size:0.75rem;">{{ $team->resubmitted_at->format('h:i A') }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark">{{ $team->officials_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $team->players_count }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('teams.show', $team) }}" class="btn btn-sm btn-outline-primary" title="{{ __('app.view') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('teams.edit', $team) }}" class="btn btn-sm btn-outline-warning" title="{{ __('app.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(auth()->user()->isSuper())
                                <form action="{{ route('teams.destroy', $team) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this team?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('app.delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else

    {{-- TEAM MANAGER: Card View (unique teams) --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0"><strong>Showing {{ $teams->firstItem() }} to {{ $teams->lastItem() }} of {{ $teams->total() }} results</strong></p>
    </div>
    <div class="row g-4">
        @foreach($teams as $team)
            @php
                $isMyTeam = auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id);
                $comps = $team->all_competitions ?? collect([$team]);
            @endphp
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 text-center team-card {{ $isMyTeam ? 'border-primary border-2' : '' }}" style="transition: transform 0.2s, box-shadow 0.2s;">
                    @if($isMyTeam)
                    <div class="bg-primary text-white py-1 px-2 text-center" style="font-size: 0.8rem; font-weight: 600;">
                        <i class="fas fa-star me-1"></i>{{ __('app.your_team') }}
                    </div>
                    @endif
                    <div class="card-body d-flex flex-column align-items-center py-4">
                        <h6 class="fw-bold text-uppercase mb-3" style="min-height: 2.8em; display: flex; align-items: center; text-align: center;">{{ $team->name }}</h6>
                        <div class="mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width:100px; height:100px; background:#f0f2f5; overflow:hidden;">
                            @if($team->logo)
                                <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}" style="width:80px; height:80px; object-fit:contain;">
                            @else
                                <i class="fas fa-shield-halved fa-2x text-muted"></i>
                            @endif
                        </div>
                        <div class="d-flex flex-column align-items-center gap-1 mb-2">
                            @foreach($comps as $t)
                                @if($t->competition && $t->competition->type === 'league')
                                    <span class="badge bg-dark" style="font-size:0.7rem; min-width:140px;">{{ $t->competition->name ?? '-' }}</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size:0.7rem; min-width:140px;">{{ $t->competition->name ?? '-' }}</span>
                                @endif
                            @endforeach
                        </div>
                        <p class="text-muted mb-1">{{ __('app.officials') }} : {{ $team->officials_count }}</p>
                        <p class="text-muted mb-3">{{ __('app.players') }} : {{ $team->players_count }}</p>
                        @if($isMyTeam)
                            <a href="{{ route('teams.show', $team) }}" class="btn btn-primary btn-sm mt-auto">
                                <i class="fas fa-arrow-right me-1"></i>{{ __('app.continue_btn') }}
                            </a>
                        @else
                            <a href="{{ route('teams.show', $team) }}" class="btn btn-outline-secondary btn-sm mt-auto">
                                {{ __('app.view_team') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif
    @endauth

    {{-- GUEST / PUBLIC: Card View (unique teams) --}}
    @guest
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0"><strong>Showing {{ $teams->firstItem() }} to {{ $teams->lastItem() }} of {{ $teams->total() }} results</strong></p>
    </div>
    <div class="row g-4">
        @foreach($teams as $team)
            @php
                $comps = $team->all_competitions ?? collect([$team]);
            @endphp
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 text-center team-card" style="transition: transform 0.2s, box-shadow 0.2s;">
                    <div class="card-body d-flex flex-column align-items-center py-4">
                        <h6 class="fw-bold text-uppercase mb-3" style="min-height: 2.8em; display: flex; align-items: center; text-align: center;">{{ $team->name }}</h6>
                        <div class="mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width:100px; height:100px; background:#f0f2f5; overflow:hidden;">
                            @if($team->logo)
                                <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}" style="width:80px; height:80px; object-fit:contain;">
                            @else
                                <i class="fas fa-shield-halved fa-2x text-muted"></i>
                            @endif
                        </div>
                        <div class="d-flex flex-column align-items-center gap-1 mb-2">
                            @foreach($comps as $t)
                                @if($t->competition && $t->competition->type === 'league')
                                    <span class="badge bg-dark" style="font-size:0.7rem; min-width:140px;">{{ $t->competition->name ?? '-' }}</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size:0.7rem; min-width:140px;">{{ $t->competition->name ?? '-' }}</span>
                                @endif
                            @endforeach
                        </div>
                        <p class="text-muted mb-1">{{ __('app.officials') }} : {{ $team->officials_count }}</p>
                        <p class="text-muted mb-3">{{ __('app.players') }} : {{ $team->players_count }}</p>
                        <a href="{{ route('teams.show', $team) }}" class="btn btn-outline-secondary btn-sm mt-auto">
                            {{ __('app.view_team') }}
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endguest

    <div class="d-flex justify-content-center mt-4">
        {{ $teams->appends(request()->query())->links() }}
    </div>
@endif

@push('styles')
<style>
    .team-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
</style>
@endpush
@endsection
