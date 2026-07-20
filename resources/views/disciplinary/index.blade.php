@extends('layouts.app')

@section('title', __('app.disciplinary_fines'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="mb-0"><i class="fas fa-gavel me-2 text-danger"></i>{{ __('app.disciplinary_fines') }}</h2>
    <div class="d-flex gap-2">
        <form action="{{ route('disciplinary.sync') }}" method="POST" class="d-inline"
              onsubmit="return confirm('{{ __('app.confirm_sync_fines') }}')">
            @csrf
            <button type="submit" class="btn btn-outline-primary" title="{{ __('app.sync_from_events_help') }}">
                <i class="fas fa-rotate me-1"></i> {{ __('app.sync_from_events') }}
            </button>
        </form>
        <a href="{{ route('disciplinary.create') }}" class="btn btn-danger">
            <i class="fas fa-plus me-1"></i> {{ __('app.issue_fine') }}
        </a>
    </div>
</div>

<div class="alert alert-light border small d-flex align-items-center mb-4">
    <i class="fas fa-robot me-2 text-primary"></i>
    <div>{{ __('app.auto_fines_note') }}</div>
</div>

{{-- Summary Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-start border-warning border-4">
            <div class="card-body">
                <div class="text-muted small">{{ __('app.total_pending_fines') }}</div>
                <div class="h4 fw-bold text-warning">RM {{ number_format($totalPending, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-success border-4">
            <div class="card-body">
                <div class="text-muted small">{{ __('app.total_paid_fines') }}</div>
                <div class="h4 fw-bold text-success">RM {{ number_format($totalPaid, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-primary border-4">
            <div class="card-body">
                <div class="text-muted small">{{ __('app.total_fines_count') }}</div>
                <div class="h4 fw-bold text-primary">{{ $fines->total() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-danger border-4">
            <div class="card-body">
                <div class="text-muted small">{{ __('app.active_suspensions') }}</div>
                <div class="h4 fw-bold text-danger">{{ $activeSuspensions }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('disciplinary.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3 col-6">
                <label class="form-label small text-muted mb-1">{{ __('app.competition') }}</label>
                <select name="competition_id" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($competitions as $c)
                        <option value="{{ $c->id }}" @selected(request('competition_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label small text-muted mb-1">{{ __('app.team') }}</label>
                <select name="team_id" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($teams as $t)
                        <option value="{{ $t->id }}" @selected(request('team_id') == $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label small text-muted mb-1">{{ __('app.player') }}</label>
                <input type="text" name="player" value="{{ request('player') }}" class="form-control form-control-sm" placeholder="{{ __('app.player') }}">
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label small text-muted mb-1">{{ __('app.card_type') }}</label>
                <select name="card_type" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    <option value="yellow_card" @selected(request('card_type') === 'yellow_card')>{{ __('app.card_yellow') }}</option>
                    <option value="red_card" @selected(request('card_type') === 'red_card')>{{ __('app.card_red') }}</option>
                    <option value="second_yellow" @selected(request('card_type') === 'second_yellow')>{{ __('app.card_second_yellow') }}</option>
                </select>
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label small text-muted mb-1">{{ __('app.status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('app.all') }}</option>
                    <option value="pending" @selected(request('status') === 'pending')>{{ __('app.status_pending') }}</option>
                    <option value="paid" @selected(request('status') === 'paid')>{{ __('app.status_paid') }}</option>
                    <option value="waived" @selected(request('status') === 'waived')>{{ __('app.status_waived') }}</option>
                </select>
            </div>
            <div class="col-12 d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>{{ __('app.apply_filters') }}</button>
                <a href="{{ route('disciplinary.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('app.clear_filters') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>{{ __('app.team') }}</th>
                        <th>{{ __('app.player') }}</th>
                        <th>{{ __('app.fine_type_label') }}</th>
                        <th>{{ __('app.competition') }}</th>
                        <th>{{ __('app.match_card') }}</th>
                        <th class="text-end">{{ __('app.amount') }} (RM)</th>
                        <th>{{ __('app.status') }}</th>
                        <th>{{ __('app.suspension') }}</th>
                        <th>{{ __('app.proof') }}</th>
                        <th>{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fines as $fine)
                        <tr>
                            <td>{{ $fine->id }}</td>
                            <td><strong>{{ $fine->team->name ?? '-' }}</strong></td>
                            <td>
                                @if($fine->player)
                                    <i class="fas fa-user me-1 text-muted"></i>
                                    {{ $fine->player->name }}
                                    @if($fine->player->jersey_number)
                                        <span class="badge bg-secondary">#{{ $fine->player->jersey_number }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">{{ __('app.team_fine') }}</span>
                                @endif
                            </td>
                            <td>
                                @if(in_array($fine->fine_type, ['red_card', 'red_direct', 'red_second_yellow']))
                                    <span class="badge bg-danger">{{ $fine->fineTypeLabel() }}</span>
                                @elseif($fine->fine_type === 'yellow_accumulation')
                                    <span class="badge bg-warning text-dark">{{ $fine->fineTypeLabel() }}</span>
                                @else
                                    <span class="badge bg-info text-dark">{{ $fine->fineTypeLabel() }}</span>
                                @endif
                                @if($fine->isAuto())
                                    <span class="badge bg-dark" title="{{ __('app.from_match_event') }}"><i class="fas fa-robot"></i> {{ __('app.auto') }}</span>
                                @endif
                                @if($fine->description)
                                    <br><small class="text-muted">{{ $fine->description }}</small>
                                @endif
                            </td>
                            <td>{{ $fine->competition->name ?? '-' }}</td>
                            <td>
                                @if($fine->matchGame)
                                    <span class="small fw-semibold">{{ $fine->matchGame->match_code ?? ('#' . $fine->matchGame->id) }}</span>
                                    @if($fine->matchGame->match_date)
                                        <br><span class="text-muted" style="font-size:0.72rem;">{{ $fine->matchGame->match_date->format('d/m/Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                                @if($fine->cardLabel())
                                    <br><span class="badge {{ $fine->card_type === 'yellow_card' ? 'bg-warning text-dark' : 'bg-danger' }}" style="font-size:0.7rem;">{{ $fine->cardLabel() }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">{{ number_format($fine->amount, 2) }}</td>
                            <td>{!! $fine->statusBadge() !!}</td>
                            <td>
                                {!! $fine->suspensionBadge() !!}
                                @if($fine->is_suspended && $fine->suspension_type === 'match_ban' && !$fine->suspension_lifted_at)
                                    <form action="{{ route('disciplinary.matches-served', $fine->id) }}" method="POST" class="d-inline mt-1">
                                        @csrf
                                        <div class="input-group input-group-sm" style="width: 120px;">
                                            <input type="number" name="matches_served" value="{{ $fine->matches_served }}" min="0" max="{{ $fine->suspension_matches }}" class="form-control form-control-sm">
                                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="{{ __('app.update') }}"><i class="fas fa-save"></i></button>
                                        </div>
                                    </form>
                                @endif
                            </td>
                            <td>
                                @if($fine->proof_file)
                                    <a href="{{ route('disciplinary.view-proof', $fine->id) }}" target="_blank" class="btn btn-outline-info btn-sm" title="{{ __('app.view_proof') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if($fine->status === 'pending')
                                        <form action="{{ route('disciplinary.mark-paid', $fine->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm" title="{{ __('app.mark_as_paid') }}">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('disciplinary.waive', $fine->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-sm" title="{{ __('app.waive_fine') }}">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($fine->is_suspended && !$fine->suspension_lifted_at)
                                        <form action="{{ route('disciplinary.lift-suspension', $fine->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm" title="{{ __('app.lift_suspension') }}" onclick="return confirm('{{ __('app.confirm_lift_suspension') }}')">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($fine->status === 'paid')
                                        <a href="{{ route('disciplinary.receipt', $fine->id) }}" class="btn btn-outline-primary btn-sm" title="{{ __('app.download_receipt') }}">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @endif
                                    <form action="{{ route('disciplinary.destroy', $fine->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete_fine') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="{{ __('app.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                <i class="fas fa-gavel fa-2x mb-2 d-block"></i>
                                {{ __('app.no_fines') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $fines->links() }}
</div>
@endsection
