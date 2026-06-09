@extends('layouts.app')

@section('title', 'Registration Submitted')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body text-center py-5">
                @if(session('payment_status') === 'paid')
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h3 class="fw-bold text-success">Registration Successful!</h3>
                @elseif(session('payment_status') === 'failed')
                    <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                    <h3 class="fw-bold text-danger">Payment Failed</h3>
                @else
                    <i class="fas fa-clock fa-4x text-warning mb-3"></i>
                    <h3 class="fw-bold text-warning">Registration Recorded</h3>
                @endif

                @if(session('team_name'))
                    <p class="fs-5 mb-1">
                        <strong>{{ session('team_name') }}</strong>
                    </p>
                @endif

                @if(session('competition_name'))
                    <p class="text-muted mb-3">
                        Registered for <strong>{{ session('competition_name') }}</strong>
                    </p>
                @endif

                @if(session('payment_status') === 'paid')
                    @if(session('fee') !== null && session('fee') == 0)
                        <div class="alert alert-success">
                            <i class="fas fa-check me-1"></i>
                            No payment required for this competition. Your registration is complete.
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check me-1"></i>
                            Payment received successfully. Your registration is complete.
                        </div>
                    @endif
                @elseif(session('payment_status') === 'failed')
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Your payment was not successful. Please contact the JBFA admin to arrange payment or try again.
                    </div>
                @elseif(session('payment_status') === 'pending')
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-1"></i>
                        Your registration has been recorded.
                        @if(session('fee') && session('fee') > 0)
                            Payment of <strong>RM {{ number_format(session('fee'), 2) }}</strong> is pending.
                            The payment gateway is being configured — JBFA admin will contact you for payment arrangements.
                        @endif
                    </div>
                @endif

                <div class="alert alert-info">
                    <i class="fas fa-hourglass-half me-1"></i>
                    Your team registration is <strong>pending approval</strong> by the JBFA league administration.
                    You will be notified once your team has been reviewed and approved.
                </div>

                @if(session('warning'))
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        {{ session('warning') }}
                    </div>
                @endif

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="{{ route('registration.index') }}" class="btn btn-success">
                        <i class="fas fa-clipboard-list me-1"></i> Register Another Team
                    </a>
                    <a href="{{ route('competitions.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-trophy me-1"></i> View Competitions
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
