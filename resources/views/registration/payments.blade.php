@extends('layouts.app')

@section('title', __('app.registration_payments'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-credit-card text-success me-2"></i>{{ __('app.registration_payments') }}
    </h2>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.payments.sync') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success" title="{{ __('app.sync_payments_hint') }}">
                <i class="fas fa-sync-alt me-1"></i> {{ __('app.sync_payments_now') }}
            </button>
        </form>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> {{ __('app.back_to_dashboard') }}
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Summary cards: quick cross-check of who has / hasn't paid --}}
@isset($summary)
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.payments') }}" class="text-decoration-none">
            <div class="card border-primary h-100">
                <div class="card-body py-2 text-center">
                    <div class="fs-4 fw-bold text-primary">{{ $summary['total'] }}</div>
                    <div class="small text-muted">{{ __('app.pay_total') }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.payments', ['status' => 'paid']) }}" class="text-decoration-none">
            <div class="card border-success h-100">
                <div class="card-body py-2 text-center">
                    <div class="fs-4 fw-bold text-success">{{ $summary['paid'] }}</div>
                    <div class="small text-muted">{{ __('app.paid') }} &bull; MYR {{ number_format($summary['collected'], 0) }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.payments', ['status' => 'pending']) }}" class="text-decoration-none">
            <div class="card border-warning h-100">
                <div class="card-body py-2 text-center">
                    <div class="fs-4 fw-bold text-warning">{{ $summary['pending'] }}</div>
                    <div class="small text-muted">{{ __('app.pending') }} &bull; MYR {{ number_format($summary['outstanding'], 0) }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.payments', ['status' => 'failed']) }}" class="text-decoration-none">
            <div class="card border-danger h-100">
                <div class="card-body py-2 text-center">
                    <div class="fs-4 fw-bold text-danger">{{ $summary['failed'] }}</div>
                    <div class="small text-muted">{{ __('app.failed') }}</div>
                </div>
            </div>
        </a>
    </div>
</div>
@endisset

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.payments') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('app.status') }}</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('app.all') }}</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('app.paid') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('app.failed') }}</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>{{ __('app.refunded') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('app.competition') }}</label>
                <select name="competition_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('app.all') }}</option>
                    @isset($competitions)
                        @foreach($competitions as $c)
                            <option value="{{ $c->id }}" {{ (string) request('competition_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">{{ __('app.team') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="{{ __('app.search') }}...">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="fas fa-search me-1"></i>{{ __('app.filter') }}</button>
                <a href="{{ route('admin.payments') }}" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

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
                                <small>{{ \App\Support\Tz::myt($payment->status === 'paid' && $payment->paid_at ? $payment->paid_at : $payment->created_at, 'd M Y H:i') }}</small>
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
