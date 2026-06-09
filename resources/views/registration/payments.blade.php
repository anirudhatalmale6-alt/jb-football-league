@extends('layouts.app')

@section('title', 'Registration Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-credit-card text-success me-2"></i>Registration Payments
    </h2>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
    </a>
</div>

@if($payments->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-credit-card fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">No payment records found</h5>
            <p class="text-muted">Registration payments will appear here once teams register.</p>
        </div>
    </div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Team</th>
                        <th>Competition</th>
                        <th>Registered By</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Status</th>
                        <th>Transaction ID</th>
                        <th>Date</th>
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
                                    <span class="badge bg-success">Paid</span>
                                @elseif($payment->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($payment->status === 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @elseif($payment->status === 'refunded')
                                    <span class="badge bg-secondary">Refunded</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $payment->transaction_id ?? '-' }}</small>
                            </td>
                            <td>
                                <small>{{ $payment->created_at->format('d M Y H:i') }}</small>
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
