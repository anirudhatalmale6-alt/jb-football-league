@extends('layouts.app')

@section('title', __('app.my_payments'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-receipt text-primary me-2"></i>{{ __('app.my_payments') }}
    </h2>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> {{ __('app.back_to_dashboard') }}
    </a>
</div>

@if($payments->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-receipt fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">{{ __('app.no_payment_records') }}</h5>
            <p class="text-muted">{{ __('app.my_payments_empty') }}</p>
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
                        <th class="text-end">{{ __('app.amount') }}</th>
                        <th class="text-center">{{ __('app.status') }}</th>
                        <th>{{ __('app.date') }}</th>
                        <th class="text-center">{{ __('app.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td class="fw-semibold">{{ $payment->team->name ?? 'N/A' }}</td>
                            <td>{{ $payment->competition->name ?? 'N/A' }}</td>
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
                                @endif
                            </td>
                            <td>
                                <small>{{ \App\Support\Tz::myt($payment->status === 'paid' && $payment->paid_at ? $payment->paid_at : $payment->created_at, 'd M Y H:i') }}</small>
                            </td>
                            <td class="text-center">
                                @if($payment->status === 'paid')
                                    <a href="{{ route('payments.receipt', $payment->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file-pdf me-1"></i> {{ __('app.download_receipt') }}
                                    </a>
                                @else
                                    @php
                                        // Prefer the team's own toyyibpay bill (exact amount, real-time
                                        // status); otherwise fall back to the shared competition link.
                                        $payUrl = !empty($payment->billcode)
                                            ? 'https://toyyibpay.com/' . $payment->billcode
                                            : (optional($payment->competition)->payment_url);
                                    @endphp
                                    @if($payUrl)
                                        <a href="{{ $payUrl }}" class="btn btn-sm btn-warning" target="_blank">
                                            <i class="fas fa-credit-card me-1"></i> {{ __('app.pay_now') }}
                                        </a>
                                    @else
                                        <span class="text-muted small">{{ __('app.receipt_not_available') }}</span>
                                    @endif
                                @endif
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
