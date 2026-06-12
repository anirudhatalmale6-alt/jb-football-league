@extends('layouts.app')

@section('title', __('app.my_fines'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-gavel me-2 text-danger"></i>{{ __('app.my_fines') }}</h2>
</div>

@if($totalPending > 0)
<div class="alert alert-warning d-flex align-items-center mb-4">
    <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
    <div>
        <strong>{{ __('app.outstanding_fines') }}:</strong> RM {{ number_format($totalPending, 2) }}
        <br><small>{{ __('app.please_settle_fines') }}</small>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
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
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end fw-bold">{{ number_format($fine->amount, 2) }}</td>
                            <td>{!! $fine->statusBadge() !!}</td>
                            <td>{{ $fine->created_at->format('d/m/Y') }}</td>
                            <td>
                                @if($fine->status === 'paid')
                                    <a href="{{ route('disciplinary.receipt', $fine->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-file-pdf me-1"></i> {{ __('app.download_receipt') }}
                                    </a>
                                @elseif($fine->status === 'pending' && $fine->payment_url)
                                    <a href="{{ $fine->payment_url }}" target="_blank" class="btn btn-success btn-sm">
                                        <i class="fas fa-credit-card me-1"></i> {{ __('app.pay_now') }}
                                    </a>
                                @elseif($fine->status === 'pending')
                                    <span class="text-muted small">{{ __('app.contact_admin_to_pay') }}</span>
                                @elseif($fine->status === 'waived')
                                    <span class="text-muted small">{{ __('app.fine_waived') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>
                                {{ __('app.no_fines_team') }}
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
