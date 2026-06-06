@extends('layouts.app')

@section('title', __('app.register'))

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-futbol fa-2x text-success"></i>
                    <h4 class="mt-2 fw-bold">{{ __('app.register') }}</h4>
                    <p class="text-muted">Join the JB Football League</p>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">
                            <i class="fas fa-user me-1 text-muted"></i> {{ __('app.name') }}
                        </label>
                        <input id="name" type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               name="name" value="{{ old('name') }}"
                               required autocomplete="name" autofocus
                               placeholder="Enter your full name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            <i class="fas fa-envelope me-1 text-muted"></i> {{ __('app.email') }}
                        </label>
                        <input id="email" type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}"
                               required autocomplete="email"
                               placeholder="you@example.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            <i class="fas fa-lock me-1 text-muted"></i> {{ __('app.password') }}
                        </label>
                        <input id="password" type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               name="password" required autocomplete="new-password"
                               placeholder="Minimum 8 characters">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="password-confirm" class="form-label fw-semibold">
                            <i class="fas fa-lock me-1 text-muted"></i> {{ __('app.confirm_password') }}
                        </label>
                        <input id="password-confirm" type="password"
                               class="form-control"
                               name="password_confirmation" required autocomplete="new-password"
                               placeholder="Repeat your password">
                    </div>

                    <!-- Role Selector -->
                    <div class="mb-4">
                        <label for="role" class="form-label fw-semibold">
                            <i class="fas fa-user-tag me-1 text-muted"></i> {{ __('app.role') }}
                        </label>
                        <select id="role" name="role"
                                class="form-select @error('role') is-invalid @enderror">
                            <option value="viewer" {{ old('role') == 'viewer' ? 'selected' : '' }}>Viewer</option>
                            <option value="team_manager" {{ old('role') == 'team_manager' ? 'selected' : '' }}>Team Manager</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Select "Team Manager" if you will be managing a team.
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-user-plus me-1"></i> {{ __('app.register') }}
                        </button>
                    </div>
                </form>

                <hr>

                <div class="text-center">
                    <span class="text-muted">{{ __('app.already_have_account') }}?</span>
                    <a href="{{ route('login') }}" class="text-success text-decoration-none fw-semibold ms-1">
                        {{ __('app.login') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
