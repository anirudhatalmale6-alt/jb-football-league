@extends('layouts.app')

@section('title', __('app.registration_payments'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-credit-card text-success me-2"></i>{{ __('app.registration_payments') }}
    </h2>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> {{ __('app.back_to_dashboard') }}
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($payments->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-credit-card fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">{{ __('app.no_payment_records') }}</h5>
            <p class="text-muted">{{ __('app.payments_will_appear') }}</p>
        </div>
    </div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>{{ __('app.team') }}</th>
                        <th>{{ __('app.competition') }}</th>
                        <th>{{ __('app.registered_by') }}</th>
                        <th class="text-end">{{ __('app.amount') }}</th>
                        <th class="text-center">{{ __('app.status') }}</th>
                        <th>{{ __('app.transaction_id') }}</th>
                        <th>{{ __('app.date') }}</th>
                        <th class="text-center">{{ __('app.actions') ?? 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td class="fw-semibold">{{ $payment->team->name ?? 'N/A' }}</td>
                            <td>{{ $payment->competition->name ?? 'N/A' }}</td>
                            <td>{{ $payment->user->name ?? 'N/A' }}</td>
                            <td class="text-end">
                                {{ $payment->currency }} {{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="text-center">
                                @if($payment->status === 'paid')
                                    <span class="badge bg-success">{{ __('app.paid') }}</span>
                                @elseif($payment->status === 'pending')
                                    <span class="badge bg-warning text-dark">{{ __('app.pending') }}</span>
                                @elseif($payment->status === 'failed')
                                    <span class="badge bg-danger">{{ __('app.failed') }}</span>
                                @elseif($payment->status === 'refunded')
                                    <span class="badge bg-secondary">{{ __('app.refunded') }}</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $payment->transaction_id ?? '-' }}</small>
                            </td>
                            <td>
                                <small>{{ $payment->created_at->format('d M Y H:i') }}</small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    @if($payment->status === 'paid')
                                        <a href="{{ route('payments.receipt', $payment->id) }}" class="btn btn-outline-primary" title="{{ __('app.download_receipt') }}">
                                            <i class="fas fa-file-pdf"></i> {{ __('app.download_receipt') }}
                                        </a>
                                    @endif
                                    @if($payment->status === 'pending' || $payment->status === 'failed')
                                        <form action="{{ route('admin.payments.mark-paid', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_mark_paid') }}')">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="{{ __('app.mark_as_paid') }}">
                                                <i class="fas fa-check"></i> {{ __('app.mark_as_paid') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $payments->links() }}
    </div>
@endif
@endsection
