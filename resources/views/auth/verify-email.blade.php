@extends('layouts.app')

@section('title', __('app.verify_email'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-envelope-open-text text-success fa-4x mb-4"></i>
                    <h3 class="fw-bold mb-3">{{ __('app.verify_email') }}</h3>

                    @if (session('message'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>{{ session('message') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    <div class="alert alert-info text-start mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Registration successful!</strong><br>
                        Please check your email inbox (and spam/junk folder) to verify your account.<br><br>
                        If you did not receive the email, click the button below to resend it.
                    </div>

                    <p class="text-muted mb-3">
                        <i class="fas fa-at me-1"></i>
                        Verification email sent to: <strong>{{ auth()->user()->email }}</strong>
                    </p>

                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg mb-3">
                            <i class="fas fa-paper-plane me-2"></i>{{ __('app.resend_verification') }}
                        </button>
                    </form>

                    <div class="mt-3 p-3 bg-light rounded text-start">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb me-1 text-warning"></i> <strong>Tips:</strong><br>
                            &bull; Check your spam or junk folder<br>
                            &bull; Make sure <strong>noreply@myjbfa.com</strong> is not blocked<br>
                            &bull; Wait a few minutes before resending<br>
                            &bull; If the problem persists, contact JBFA Admin
                        </small>
                    </div>

                    <hr class="my-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-sign-out-alt me-1"></i>{{ __('app.logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection