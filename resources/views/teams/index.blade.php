@extends('layouts.app')

@section('title', __('app.teams'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-shield-halved text-success me-2"></i>{{ __('app.teams') }}
    </h2>
    @auth
        <a href="{{ route('teams.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> {{ __('app.register_team') }}
        </a>
    @endauth
</div>

<!-- Search Form -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('teams.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label fw-semibold">{{ __('app.search') }}</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Search by team name...">
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
            <div class="col-md-3">
                <label for="status" class="form-label fw-semibold">{{ __('app.status') }}</label>
                <select class="form-select" id="status" name="status">
                    <option value="">{{ __('app.all_statuses') }}</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('app.approved') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('app.rejected') }}</option>
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

@if($teams->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-shield-halved fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">{{ __('app.no_teams_found') }}</h5>
            <p class="text-muted">Teams will appear here once registered.</p>
        </div>
    </div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.competition') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-center">{{ __('app.players') }}</th>
                        <th class="text-center">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teams as $team)
                        <tr>
                            <td class="fw-semibold">{{ $team->name }}</td>
                            <td>{{ $team->competition->name ?? '-' }}</td>
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
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $team->players_count ?? $team->players->count() }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('teams.show', $team) }}" class="btn btn-sm btn-outline-primary" title="{{ __('app.view') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @auth
                                    @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                                        <a href="{{ route('teams.edit', $team) }}" class="btn btn-sm btn-outline-warning" title="{{ __('app.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('teams.destroy', $team) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this team?');">
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
        {{ $teams->appends(request()->query())->links() }}
    </div>
@endif
@endsection
