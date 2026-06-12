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
    <div class="col-md-4">
        <div class="card border-start border-warning border-4">
            <div class="card-body">
                <div class="text-muted small">{{ __('app.total_pending_fines') }}</div>
                <div class="h4 fw-bold text-warning">RM {{ number_format($totalPending, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-start border-success border-4">
            <div class="card-body">
                <div class="text-muted small">{{ __('app.total_paid_fines') }}</div>
                <div class="h4 fw-bold text-success">RM {{ number_format($totalPaid, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-start border-primary border-4">
            <div class="card-body">
                <div class="text-muted small">{{ __('app.total_fines_count') }}</div>
                <div class="h4 fw-bold text-primary">{{ $fines->total() }}</div>
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
                        <th>{{ __('app.match') }}</th>
                        <th class="text-end">{{ __('app.amount') }} (RM)</th>
                        <th>{{ __('app.status') }}</th>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fines as $fine)
                        <tr>
                            <td>{{ $fine->id }}</td>
                            <td>
                                <strong>{{ $fine->team->name ?? '-' }}</strong>
                            </td>
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
                            <td>
                                @if($fine->matchGame)
                                    {{ $fine->matchGame->match_code ?? '' }}
                                    <br><small class="text-muted">{{ $fine->matchGame->homeTeam->name ?? '' }} vs {{ $fine->matchGame->awayTeam->name ?? '' }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">{{ number_format($fine->amount, 2) }}</td>
                            <td>{!! $fine->statusBadge() !!}</td>
                            <td>{{ $fine->created_at->format('d/m/Y') }}</td>
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
