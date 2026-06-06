@extends('layouts.app')

@section('title', __('app.matches'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-calendar-days text-success me-2"></i>{{ __('app.matches') }}
    </h2>
    @auth
        @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
            <a href="{{ route('matches.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> {{ __('app.create_match') }}
            </a>
        @endif
    @endauth
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('matches.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
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
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>{{ __('app.scheduled') }}</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>{{ __('app.in_progress') }}</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('app.completed') }}</option>
                    <option value="postponed" {{ request('status') === 'postponed' ? 'selected' : '' }}>{{ __('app.postponed') }}</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('app.cancelled') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date" class="form-label fw-semibold">{{ __('app.date') }}</label>
                <input type="date" class="form-control" id="date" name="date" value="{{ request('date') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> {{ __('app.filter') }}
                </button>
            </div>
        </form>
    </div>
</div>

@if($matches->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-calendar-xmark fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">{{ __('app.no_matches_found') }}</h5>
            <p class="text-muted">Matches will appear here once scheduled.</p>
        </div>
    </div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.home_team') }}</th>
                        <th class="text-center">{{ __('app.score') }}</th>
                        <th>{{ __('app.away_team') }}</th>
                        <th>{{ __('app.competition') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-center">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($matches as $match)
                        <tr>
                            <td class="text-muted">
                                {{ $match->match_date ? $match->match_date->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="fw-semibold">{{ $match->homeTeam->name ?? '-' }}</td>
                            <td class="text-center">
                                @if($match->status === 'completed')
                                    <span class="badge bg-dark fs-6">{{ $match->home_score }} - {{ $match->away_score }}</span>
                                @else
                                    <span class="text-muted">vs</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $match->awayTeam->name ?? '-' }}</td>
                            <td><small class="text-muted">{{ $match->competition->name ?? '-' }}</small></td>
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
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('matches.show', $match) }}" class="btn btn-outline-primary" title="{{ __('app.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @auth
                                        @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                                            <a href="{{ route('matches.edit', $match) }}" class="btn btn-outline-warning" title="{{ __('app.edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('matches.lineup', $match) }}" class="btn btn-outline-info" title="{{ __('app.lineup') }}">
                                                <i class="fas fa-list-ol"></i>
                                            </a>
                                            <a href="{{ route('matches.events', $match) }}" class="btn btn-outline-dark" title="{{ __('app.events') }}">
                                                <i class="fas fa-futbol"></i>
                                            </a>
                                        @endif
                                    @endauth
                                    <a href="{{ route('matches.pdf.summary', $match) }}" class="btn btn-outline-danger" title="{{ __('app.download_match_summary_pdf') }}">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $matches->appends(request()->query())->links() }}
    </div>
@endif
@endsection
