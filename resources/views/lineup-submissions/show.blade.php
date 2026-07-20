@extends('layouts.app')
@section('title', __('app.lineup_details'))

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('lineup-submissions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>{{ __('app.back') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-futbol me-2"></i>{{ __('app.match_details') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>{{ __('app.competition') }}:</strong> {{ $match->competition->name ?? '-' }}</div>
                <div class="col-md-3"><strong>{{ __('app.match') }}:</strong> {{ $match->homeTeam->name ?? '-' }} vs {{ $match->awayTeam->name ?? '-' }}</div>
                <div class="col-md-3"><strong>{{ __('app.date') }}:</strong> {{ $match->match_date ? $match->match_date->format('d M Y H:i') : '-' }}</div>
                <div class="col-md-3"><strong>{{ __('app.venue') }}:</strong> {{ $match->venue ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center
            @if($submission->isLocked()) bg-dark text-white
            @elseif($submission->isApproved()) bg-success text-white
            @elseif($submission->isRejected()) bg-danger text-white
            @elseif($submission->isSubmitted()) bg-warning text-dark
            @else bg-secondary text-white
            @endif">
            <h5 class="mb-0">
                {{ $team->name }} - {{ __('app.lineup') }}
                @if($submission->isLocked()) <i class="fas fa-lock ms-2"></i> @endif
            </h5>
            <span class="badge bg-light text-dark fs-6">{{ strtoupper(__('app.' . $submission->status)) }}</span>
        </div>
        <div class="card-body">
            @if($submission->isRejected())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>{{ __('app.rejection_reason') }}:</strong> {{ $submission->rejection_reason }}
                    @if($submission->reviewedByUser)
                        <br><small>{{ __('app.reviewed_by') }}: {{ $submission->reviewedByUser->name }} ({{ $submission->reviewed_at->format('d M Y H:i') }})</small>
                    @endif
                </div>
            @endif

            @if($submission->isApproved() && $submission->reviewedByUser)
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ __('app.approved_by') }}: {{ $submission->reviewedByUser->name }} ({{ $submission->reviewed_at->format('d M Y H:i') }})
                </div>
            @endif

            <h6 class="text-success mb-3"><i class="fas fa-star me-2"></i>{{ __('app.starting_eleven') }} ({{ $starting->count() }})</h6>
            <div class="table-responsive mb-4">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>{{ __('app.name') }}</th>
                            <th style="width:110px">{{ __('app.position') }}</th>
                            <th class="d-none d-md-table-cell" style="width:120px">IC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($starting as $lineup)
                            <tr>
                                <td>{{ $lineup->jersey_number }}</td>
                                <td>{{ $lineup->player->name ?? '-' }}</td>
                                <td><span class="badge bg-info text-dark">{{ ucfirst($lineup->position ?? $lineup->player->position ?? '-') }}</span></td>
                                <td class="d-none d-md-table-cell">{{ $lineup->player->ic_number ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <h6 class="text-warning mb-3"><i class="fas fa-exchange-alt me-2"></i>{{ __('app.substitutes') }} ({{ $subs->count() }})</h6>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>{{ __('app.name') }}</th>
                            <th style="width:110px">{{ __('app.position') }}</th>
                            <th class="d-none d-md-table-cell" style="width:120px">IC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subs as $lineup)
                            <tr>
                                <td>{{ $lineup->jersey_number }}</td>
                                <td>{{ $lineup->player->name ?? '-' }}</td>
                                <td><span class="badge bg-info text-dark">{{ ucfirst($lineup->position ?? $lineup->player->position ?? '-') }}</span></td>
                                <td class="d-none d-md-table-cell">{{ $lineup->player->ic_number ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">{{ __('app.no_subs') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($submission->submittedByUser)
                <p class="text-muted mt-3 mb-0">
                    <small>{{ __('app.submitted_by') }}: {{ $submission->submittedByUser->name }}
                    @if($submission->submitted_at) ({{ $submission->submitted_at->format('d M Y H:i') }}) @endif</small>
                </p>
            @endif
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        @if($submission->canEdit())
            @if(Auth::user()->isTeamManager() && Auth::user()->managesTeam($team->id) || Auth::user()->isSuper() || Auth::user()->isLeagueAdmin())
                <a href="{{ route('lineup-submissions.edit', [$match->id, $team->id]) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i>{{ __('app.edit_lineup') }}
                </a>
            @endif
        @endif

        @if($submission->canSubmit() && $starting->count() === 11)
            @if(Auth::user()->isTeamManager() && Auth::user()->managesTeam($team->id) || Auth::user()->isSuper() || Auth::user()->isLeagueAdmin())
                <form method="POST" action="{{ route('lineup-submissions.submit', [$match->id, $team->id]) }}" class="d-inline"
                      onsubmit="return confirm('{{ __('app.confirm_submit_lineup') }}')">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-1"></i>{{ __('app.submit_lineup') }}
                    </button>
                </form>
            @endif
        @endif

        @if(in_array($submission->status, ['submitted', 'approved', 'locked']))
            <a href="{{ route('lineup-submissions.pdf', [$match->id, $team->id]) }}" class="btn btn-outline-danger" target="_blank">
                <i class="fas fa-file-pdf me-1"></i>{{ __('app.download_pdf') }}
            </a>
        @endif
    </div>
</div>
@endsection
