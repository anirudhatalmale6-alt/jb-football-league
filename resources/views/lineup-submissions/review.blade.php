@extends('layouts.app')
@section('title', __('app.review_lineups'))

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

    @if($submissions->isEmpty())
        <div class="alert alert-info">{{ __('app.no_lineups_submitted') }}</div>
    @endif

    @foreach($submissions as $submission)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center
                @if($submission->isLocked()) bg-dark text-white
                @elseif($submission->isApproved()) bg-success text-white
                @elseif($submission->isRejected()) bg-danger text-white
                @elseif($submission->isSubmitted()) bg-warning text-dark
                @else bg-secondary text-white
                @endif">
                <h5 class="mb-0">
                    @if($submission->team->logo)
                        <img src="{{ asset('storage/' . $submission->team->logo) }}" alt="" style="height:25px;" class="me-2">
                    @endif
                    {{ $submission->team->name }}
                </h5>
                <span class="badge bg-light text-dark fs-6">{{ strtoupper(__('app.' . $submission->status)) }}</span>
            </div>
            <div class="card-body">
                @php
                    $starting = $submission->lineups->where('is_starting', true)->sortBy('jersey_number');
                    $subs = $submission->lineups->where('is_starting', false)->sortBy('jersey_number');
                @endphp

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-success"><i class="fas fa-star me-1"></i>{{ __('app.starting_eleven') }} ({{ $starting->count() }})</h6>
                        <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead><tr>
                                <th style="width:35px">#</th>
                                <th>{{ __('app.name') }}</th>
                                <th style="width:90px">{{ __('app.position') }}</th>
                                <th class="d-none d-lg-table-cell" style="width:90px">IC</th>
                                <th style="width:40px">{{ __('app.status') }}</th>
                            </tr></thead>
                            <tbody>
                                @foreach($starting as $lineup)
                                    @php $playerOk = $lineup->player && $lineup->player->team_id === $submission->team_id && $lineup->player->status === 'active'; @endphp
                                    <tr class="{{ !$playerOk ? 'table-danger' : '' }}">
                                        <td>{{ $lineup->jersey_number }}</td>
                                        <td>{{ $lineup->player->name ?? '-' }}</td>
                                        <td>{{ ucfirst($lineup->position ?? '-') }}</td>
                                        <td class="d-none d-lg-table-cell">{{ $lineup->player->ic_number ?? '-' }}</td>
                                        <td>
                                            @if(!$lineup->player)
                                                <span class="badge bg-danger">{{ __('app.player_not_found') }}</span>
                                            @elseif($lineup->player->team_id !== $submission->team_id)
                                                <span class="badge bg-danger">{{ __('app.wrong_team') }}</span>
                                            @elseif($lineup->player->status === 'suspended')
                                                <span class="badge bg-danger">{{ __('app.suspended') }}</span>
                                            @else
                                                <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-warning"><i class="fas fa-exchange-alt me-1"></i>{{ __('app.substitutes') }} ({{ $subs->count() }})</h6>
                        <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead><tr>
                                <th style="width:35px">#</th>
                                <th>{{ __('app.name') }}</th>
                                <th style="width:90px">{{ __('app.position') }}</th>
                                <th class="d-none d-lg-table-cell" style="width:90px">IC</th>
                                <th style="width:40px">{{ __('app.status') }}</th>
                            </tr></thead>
                            <tbody>
                                @forelse($subs as $lineup)
                                    @php $playerOk = $lineup->player && $lineup->player->team_id === $submission->team_id && $lineup->player->status === 'active'; @endphp
                                    <tr class="{{ !$playerOk ? 'table-danger' : '' }}">
                                        <td>{{ $lineup->jersey_number }}</td>
                                        <td>{{ $lineup->player->name ?? '-' }}</td>
                                        <td>{{ ucfirst($lineup->position ?? '-') }}</td>
                                        <td class="d-none d-lg-table-cell">{{ $lineup->player->ic_number ?? '-' }}</td>
                                        <td>
                                            @if(!$lineup->player)
                                                <span class="badge bg-danger">{{ __('app.player_not_found') }}</span>
                                            @elseif($lineup->player->team_id !== $submission->team_id)
                                                <span class="badge bg-danger">{{ __('app.wrong_team') }}</span>
                                            @elseif($lineup->player->status === 'suspended')
                                                <span class="badge bg-danger">{{ __('app.suspended') }}</span>
                                            @else
                                                <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-muted">{{ __('app.no_subs') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>

                @if($submission->submittedByUser)
                    <p class="text-muted mb-2"><small>{{ __('app.submitted_by') }}: {{ $submission->submittedByUser->name }}
                    @if($submission->submitted_at) ({{ $submission->submitted_at->format('d M Y H:i') }}) @endif</small></p>
                @endif

                <div class="d-flex gap-2 mt-3">
                    @if($submission->status === 'submitted')
                        <form method="POST" action="{{ route('lineup-submissions.approve', [$match->id, $submission->team_id]) }}"
                              onsubmit="return confirm('{{ __('app.confirm_approve') }}')">
                            @csrf
                            <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>{{ __('app.approve') }}</button>
                        </form>

                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $submission->id }}">
                            <i class="fas fa-times me-1"></i>{{ __('app.reject') }}
                        </button>

                        <div class="modal fade" id="rejectModal{{ $submission->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('lineup-submissions.reject', [$match->id, $submission->team_id]) }}">
                                        @csrf
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">{{ __('app.reject_lineup') }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>{{ __('app.reject_lineup_info') }}</p>
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('app.rejection_reason') }}</label>
                                                <textarea name="rejection_reason" class="form-control" rows="3" required
                                                          placeholder="{{ __('app.rejection_reason_placeholder') }}"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                                            <button type="submit" class="btn btn-danger">{{ __('app.reject') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($submission->status === 'approved')
                        <form method="POST" action="{{ route('lineup-submissions.lock', [$match->id, $submission->team_id]) }}"
                              onsubmit="return confirm('{{ __('app.confirm_lock') }}')">
                            @csrf
                            <button type="submit" class="btn btn-dark"><i class="fas fa-lock me-1"></i>{{ __('app.lock_lineup') }}</button>
                        </form>
                    @endif

                    @if(in_array($submission->status, ['submitted', 'approved', 'locked']))
                        <a href="{{ route('lineup-submissions.pdf', [$match->id, $submission->team_id]) }}" class="btn btn-outline-danger" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i>PDF
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
