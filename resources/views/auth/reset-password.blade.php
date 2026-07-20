@extends('layouts.app')

@section('title', __('app.reset_password'))

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-lock fa-2x text-success"></i>
                    <h4 class="mt-2 fw-bold">{{ __('app.set_new_password') }}</h4>
                    <p class="text-muted">{{ __('app.set_new_password_intro') }}</p>
                </div>

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            <i class="fas fa-envelope me-1 text-muted"></i> {{ __('app.email_address') }}
                        </label>
                        <input id="email" type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email', $email) }}"
                               required autocomplete="email" readonly>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            <i class="fas fa-lock me-1 text-muted"></i> {{ __('app.new_password') }}
                        </label>
                        <input id="password" type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               name="password" required autocomplete="new-password"
                               placeholder="{{ __('app.new_password') }}">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">{{ __('app.password_min_hint') }}</small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-semibold">
                            <i class="fas fa-lock me-1 text-muted"></i> {{ __('app.confirm_new_password') }}
                        </label>
                        <input id="password_confirmation" type="password"
                               class="form-control"
                               name="password_confirmation" required autocomplete="new-password"
                               placeholder="{{ __('app.confirm_new_password') }}">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-1"></i> {{ __('app.update_password') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
