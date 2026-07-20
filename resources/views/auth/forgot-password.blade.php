@extends('layouts.app')

@section('title', __('app.reset_password'))

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-key fa-2x text-success"></i>
                    <h4 class="mt-2 fw-bold">{{ __('app.reset_password') }}</h4>
                    <p class="text-muted">{{ __('app.reset_password_intro') }}</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-1"></i> {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            <i class="fas fa-envelope me-1 text-muted"></i> {{ __('app.email_address') }}
                        </label>
                        <input id="email" type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}"
                               required autocomplete="email" autofocus
                               placeholder="you@example.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-paper-plane me-1"></i> {{ __('app.send_reset_link') }}
                        </button>
                    </div>
                </form>

                <hr>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-muted text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i> {{ __('app.back_to_login') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
