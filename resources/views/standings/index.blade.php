@extends('layouts.app')

@section('title', 'Standings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-ranking-star text-success me-2"></i>League Standings
    </h2>
    @auth
        @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
            @if(isset($selectedCompetition))
                <form action="{{ route('standings.recalculate') }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Recalculate standings for this competition?');">
                    @csrf
                    <input type="hidden" name="competition_id" value="{{ $selectedCompetition->id }}">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-calculator me-1"></i> Recalculate
                    </button>
                </form>
            @endif
        @endif
    @endauth
</div>

<!-- Competition Selector -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('standings.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label for="competition_id" class="form-label fw-semibold">Select Competition</label>
                <select class="form-select" id="competition_id" name="competition_id" onchange="this.form.submit()">
                    <option value="">-- Select Competition --</option>
                    @foreach($competitions as $competition)
                        <option value="{{ $competition->id }}"
                            {{ (isset($selectedCompetition) && $selectedCompetition->id == $competition->id) ? 'selected' : '' }}>
                            {{ $competition->name }} ({{ $competition->season }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> View Standings
                </button>
            </div>
        </form>
    </div>
</div>

@if(isset($selectedCompetition))
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">
                <i class="fas fa-trophy me-2"></i>{{ $selectedCompetition->name }} &mdash; {{ $selectedCompetition->season }}
            </h5>
        </div>
        @if($standings->isEmpty())
            <div class="card-body text-center py-5">
                <i class="fas fa-table fa-3x text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No standings data available</h5>
                <p class="text-muted">Standings will be generated once matches are played.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
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
                        @foreach($standings as $index => $standing)
                            @php
                                $rowClass = '';
                                if ($index === 0) {
                                    $rowClass = 'table-success';
                                } elseif ($index >= $standings->count() - 2 && $standings->count() > 4) {
                                    $rowClass = 'table-danger';
                                }
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td class="text-center fw-bold">
                                    @if($index === 0)
                                        <span class="badge bg-success">{{ $standing->position }}</span>
                                    @elseif($index >= $standings->count() - 2 && $standings->count() > 4)
                                        <span class="badge bg-danger">{{ $standing->position }}</span>
                                    @else
                                        {{ $standing->position }}
                                    @endif
                                </td>
                                <td class="fw-semibold">
                                    <a href="{{ route('teams.show', $standing->team) }}" class="text-decoration-none text-dark">
                                        {{ $standing->team->name ?? '-' }}
                                    </a>
                                </td>
                                <td class="text-center">{{ $standing->played }}</td>
                                <td class="text-center">{{ $standing->won }}</td>
                                <td class="text-center">{{ $standing->drawn }}</td>
                                <td class="text-center">{{ $standing->lost }}</td>
                                <td class="text-center">{{ $standing->goals_for }}</td>
                                <td class="text-center">{{ $standing->goals_against }}</td>
                                <td class="text-center">
                                    @if($standing->goal_difference > 0)
                                        <span class="text-success">+{{ $standing->goal_difference }}</span>
                                    @elseif($standing->goal_difference < 0)
                                        <span class="text-danger">{{ $standing->goal_difference }}</span>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td class="text-center fw-bold fs-5">{{ $standing->points }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="d-flex gap-4">
                    <div>
                        <span class="badge bg-success">&nbsp;</span>
                        <small class="text-muted ms-1">Champion Zone</small>
                    </div>
                    @if($standings->count() > 4)
                        <div>
                            <span class="badge bg-danger">&nbsp;</span>
                            <small class="text-muted ms-1">Relegation Zone</small>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-ranking-star fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">Select a competition to view standings</h5>
            <p class="text-muted">Choose a competition from the dropdown above.</p>
        </div>
    </div>
@endif
@endsection
