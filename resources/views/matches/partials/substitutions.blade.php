@php
    $pendingReqs = $substitutionRequests->where('status', 'pending');
    $matchInPlay = in_array($match->status, ['live', 'half_time', 'second_half']);
    $myReqs = $mySubTeamId ? $substitutionRequests->where('team_id', $mySubTeamId) : collect();
@endphp

{{-- Match Commissioner: pending substitution requests to approve / reject --}}
@auth
@if($canOperate && $pendingReqs->isNotEmpty())
<div class="card mb-3 border-primary">
    <div class="card-header bg-primary text-white py-2">
        <h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>{{ __('app.sub_requests') }}
            <span class="badge bg-light text-primary ms-1">{{ $pendingReqs->count() }}</span></h6>
    </div>
    <div class="card-body py-2">
        @foreach($pendingReqs as $req)
            <div class="border rounded p-2 mb-2">
                <div class="d-flex justify-content-between flex-wrap gap-2 align-items-center">
                    <div>
                        <span class="badge bg-secondary">{{ $req->team->name ?? '-' }}</span>
                        <span class="ms-1 small text-muted">{{ __('app.sub_requested_at') }} {{ $req->minute }}'</span>
                        <div class="mt-1">
                            <span class="badge bg-danger"><i class="fas fa-arrow-down me-1"></i>OUT: #{{ $req->playerOut->jersey_number ?? '' }} {{ $req->playerOut->name ?? '-' }}</span>
                            <span class="badge bg-success ms-1"><i class="fas fa-arrow-up me-1"></i>IN: #{{ $req->playerIn->jersey_number ?? '' }} {{ $req->playerIn->name ?? '-' }}</span>
                        </div>
                        @if($req->reason)<div class="small text-muted mt-1"><i class="fas fa-comment me-1"></i>{{ $req->reason }}</div>@endif
                        <div class="small text-muted">{{ __('app.sub_requested_by') }} {{ $req->requestedBy->name ?? '-' }}</div>
                    </div>
                    <div class="d-flex gap-1">
                        <form method="POST" action="{{ route('substitution-requests.approve', [$match->id, $req->id]) }}"
                              onsubmit="return confirm('{{ __('app.sub_confirm_approve') }}')">
                            @csrf
                            <button class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i>{{ __('app.approve') }}</button>
                        </form>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectSub{{ $req->id }}">
                            <i class="fas fa-times me-1"></i>{{ __('app.reject') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="rejectSub{{ $req->id }}" tabindex="-1">
                <div class="modal-dialog"><div class="modal-content">
                    <form method="POST" action="{{ route('substitution-requests.reject', [$match->id, $req->id]) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('app.sub_reject_title') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">{{ __('app.sub_reject_reason') }}</label>
                            <textarea name="rejection_reason" class="form-control" rows="2" maxlength="500"
                                      placeholder="{{ __('app.sub_reject_placeholder') }}"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                            <button type="submit" class="btn btn-danger">{{ __('app.reject') }}</button>
                        </div>
                    </form>
                </div></div>
            </div>
        @endforeach
    </div>
</div>
@endif
@endauth

{{-- Team Manager: request a substitution + see own request statuses --}}
@if($mySubTeamId)
<div class="card mb-3 border-success">
    <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-people-arrows me-2"></i>{{ __('app.team_match_actions') }}</h6>
        @if($matchInPlay)
            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#requestSubModal">
                <i class="fas fa-exchange-alt me-1"></i>{{ __('app.request_substitution') }}
            </button>
        @endif
    </div>
    <div class="card-body py-2">
        @if(!$matchInPlay)
            <div class="text-muted small"><i class="fas fa-info-circle me-1"></i>{{ __('app.sub_only_when_live') }}</div>
        @endif
        @if($myReqs->isNotEmpty())
            <div class="mt-1">
                <div class="small fw-bold text-muted mb-1">{{ __('app.your_sub_requests') }}</div>
                @foreach($myReqs as $req)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-1 flex-wrap gap-1">
                        <span class="small">
                            {{ $req->minute }}' —
                            <span class="text-danger">OUT #{{ $req->playerOut->jersey_number ?? '' }} {{ $req->playerOut->name ?? '-' }}</span>,
                            <span class="text-success">IN #{{ $req->playerIn->jersey_number ?? '' }} {{ $req->playerIn->name ?? '-' }}</span>
                        </span>
                        <span class="text-end">
                            {!! $req->statusBadge() !!}
                            @if($req->status === 'rejected' && $req->rejection_reason)
                                <div class="small text-danger">{{ __('app.rejected') }}: {{ $req->rejection_reason }}</div>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@if($matchInPlay)
<div class="modal fade" id="requestSubModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="{{ route('substitution-requests.store', $match->id) }}">
            @csrf
            <input type="hidden" name="team_id" value="{{ $mySubTeamId }}">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-exchange-alt me-2"></i>{{ __('app.request_substitution') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border small"><i class="fas fa-clock me-1"></i>{{ __('app.sub_time_auto') }}</div>
                @if($subOnField->isEmpty() || $subBench->isEmpty())
                    <div class="alert alert-warning small"><i class="fas fa-exclamation-triangle me-1"></i>{{ __('app.sub_no_players') }}</div>
                @endif
                <div class="mb-3">
                    <label class="form-label fw-bold text-danger"><i class="fas fa-arrow-down me-1"></i>{{ __('app.player_out') }}</label>
                    <select name="player_out_id" class="form-select" required>
                        <option value="">{{ __('app.sub_select_on_field') }}</option>
                        @foreach($subOnField as $p)
                            <option value="{{ $p->id }}">#{{ $p->jersey_number }} {{ $p->name }}@if($p->is_u23) [U23]@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold text-success"><i class="fas fa-arrow-up me-1"></i>{{ __('app.player_in') }}</label>
                    <select name="player_in_id" class="form-select" required>
                        <option value="">{{ __('app.sub_select_bench') }}</option>
                        @foreach($subBench as $p)
                            <option value="{{ $p->id }}">#{{ $p->jersey_number }} {{ $p->name }}@if($p->is_u23) [U23]@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">{{ __('app.sub_reason_optional') }}</label>
                    <input type="text" name="reason" class="form-control" maxlength="500" placeholder="{{ __('app.optional') }}">
                </div>
                <div class="alert alert-warning small mb-0"><i class="fas fa-shield-alt me-1"></i>{{ __('app.sub_u23_reminder') }}</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i>{{ __('app.sub_send_request') }}</button>
            </div>
        </form>
    </div></div>
</div>
@endif
@endif
