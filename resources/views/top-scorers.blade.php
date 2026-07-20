@extends('layouts.app')

@section('title', 'Top Scorers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-trophy text-warning me-2"></i>Top Scorers</h2>
</div>

<!-- Competition Filter -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('top-scorers') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold small mb-1">Filter by Competition</label>
                <select name="competition_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Competitions</option>
                    @foreach($competitions as $comp)
                        <option value="{{ $comp->id }}" {{ $selectedCompetition == $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-filter me-1"></i>Filter</button>
                @if($selectedCompetition)
                    <a href="{{ route('top-scorers') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times me-1"></i>Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Top Scorers Table -->
<div class="card">
    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-futbol me-2"></i>
            @if($selectedCompetition)
                {{ $competitions->firstWhere('id', $selectedCompetition)->name ?? 'Competition' }} - Top Scorers
            @else
                Overall Top Scorers
            @endif
        </h6>
        <span class="badge bg-warning text-dark">{{ $scorers->count() }} Players</span>
    </div>

    @if($scorers->isEmpty())
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-futbol fa-3x mb-3 d-block opacity-50"></i>
            <p>No goals scored yet in {{ $selectedCompetition ? 'this competition' : 'any competition' }}.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;" class="text-center">#</th>
                        <th>Player</th>
                        <th class="d-none d-md-table-cell">Team</th>
                        <th class="text-center" style="width:70px;"><i class="fas fa-futbol text-success" title="Total Goals"></i> Goals</th>
                        <th class="text-center d-none d-md-table-cell" style="width:70px;"><i class="fas fa-bullseye text-primary" title="Penalty Goals"></i> Pen</th>
                        <th class="text-center d-none d-lg-table-cell" style="width:70px;"><i class="fas fa-calendar text-muted" title="Matches Played"></i> MP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scorers as $scorer)
                        <tr class="{{ $scorer->rank <= 3 ? 'table-warning' : '' }}">
                            <td class="text-center fw-bold">
                                @if($scorer->rank == 1)
                                    <span class="badge bg-warning text-dark" style="font-size:0.85rem;">1</span>
                                @elseif($scorer->rank == 2)
                                    <span class="badge bg-secondary" style="font-size:0.85rem;">2</span>
                                @elseif($scorer->rank == 3)
                                    <span class="badge bg-danger" style="font-size:0.8rem;">3</span>
                                @else
                                    <span class="text-muted">{{ $scorer->rank }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($scorer->player && $scorer->player->photo)
                                        <img src="{{ asset('storage/' . $scorer->player->photo) }}" alt="" class="rounded-circle me-2" style="width:32px;height:32px;object-fit:cover;">
                                    @else
                                        <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;font-size:0.7rem;">
                                            {{ $scorer->player ? strtoupper(substr($scorer->player->name, 0, 2)) : '??' }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong class="small">{{ $scorer->player->name ?? '-' }}</strong>
                                        <small class="text-muted d-block d-md-none">{{ $scorer->team->short_name ?? $scorer->team->name ?? '-' }}</small>
                                        @if($scorer->player && $scorer->player->jersey_number)
                                            <small class="text-muted d-none d-md-inline ms-1">#{{ $scorer->player->jersey_number }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <small>{{ $scorer->team->short_name ?? $scorer->team->name ?? '-' }}</small>
                            </td>
                            <td class="text-center">
                                <strong class="text-success" style="font-size:1.1rem;">{{ $scorer->total_goals }}</strong>
                            </td>
                            <td class="text-center d-none d-md-table-cell">
                                <small class="text-muted">{{ $scorer->penalty_goals > 0 ? $scorer->penalty_goals : '-' }}</small>
                            </td>
                            <td class="text-center d-none d-lg-table-cell">
                                <small class="text-muted">{{ $scorer->matches_played }}</small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
