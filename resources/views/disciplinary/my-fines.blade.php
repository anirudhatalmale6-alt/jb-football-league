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
                        <th class="text-end">{{ __('app.amount') }} (RM)</th>
                        <th>{{ __('app.status') }}</th>
                        <th>{{ __('app.suspension') }}</th>
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
                            <td class="text-end fw-bold">{{ number_format($fine->amount, 2) }}</td>
                            <td>{!! $fine->statusBadge() !!}</td>
                            <td>{!! $fine->suspensionBadge() !!}</td>
                            <td>
                                @if($fine->status === 'paid')
                                    <a href="{{ route('disciplinary.receipt', $fine->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-file-pdf me-1"></i> {{ __('app.download_receipt') }}
                                    </a>
                                @elseif($fine->status === 'pending')
                                    {{-- Upload proof of payment --}}
                                    @if($fine->proof_file)
                                        <span class="badge bg-info"><i class="fas fa-check me-1"></i>{{ __('app.proof_submitted') }}</span>
                                        <a href="{{ route('disciplinary.view-proof', $fine->id) }}" target="_blank" class="btn btn-outline-info btn-sm ms-1" title="{{ __('app.view_proof') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#proofModal{{ $fine->id }}">
                                            <i class="fas fa-upload me-1"></i> {{ __('app.upload_proof') }}
                                        </button>
                                    @endif
                                @elseif($fine->status === 'waived')
                                    <span class="text-muted small">{{ __('app.fine_waived') }}</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Proof Upload Modal --}}
                        @if($fine->status === 'pending' && !$fine->proof_file)
                        <div class="modal fade" id="proofModal{{ $fine->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('disciplinary.upload-proof', $fine->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-header bg-warning">
                                            <h5 class="modal-title"><i class="fas fa-upload me-2"></i>{{ __('app.upload_proof') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="mb-3">{{ __('app.upload_proof_help') }}</p>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('app.proof_file') }}</label>
                                                <input type="file" name="proof_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                                <small class="text-muted">{{ __('app.proof_file_types') }}</small>
                                            </div>
                                            <div class="alert alert-info small mb-0">
                                                <i class="fas fa-info-circle me-1"></i>
                                                {{ __('app.proof_info') }}
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                                            <button type="submit" class="btn btn-warning"><i class="fas fa-upload me-1"></i> {{ __('app.upload') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
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
