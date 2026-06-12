@extends('layouts.app')

@section('title', __('app.disciplinary_fines'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-gavel me-2 text-danger"></i>{{ __('app.disciplinary_fines') }}</h2>
    <a href="{{ route('disciplinary.create') }}" class="btn btn-danger">
        <i class="fas fa-plus me-1"></i> {{ __('app.issue_fine') }}
    </a>
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
                                @if($fine->fine_type === 'red_card')
                                    <span class="badge bg-danger">{{ $fine->fineTypeLabel() }}</span>
                                @elseif($fine->fine_type === 'yellow_accumulation')
                                    <span class="badge bg-warning text-dark">{{ $fine->fineTypeLabel() }}</span>
                                @else
                                    <span class="badge bg-info text-dark">{{ $fine->fineTypeLabel() }}</span>
                                @endif
                                @if($fine->description)
                                    <br><small class="text-muted">{{ $fine->description }}</small>
                                @endif
                            </td>
                            <td>{{ $fine->competition->name ?? '-' }}</td>
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
                            <td colspan="10" class="text-center py-4 text-muted">
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
