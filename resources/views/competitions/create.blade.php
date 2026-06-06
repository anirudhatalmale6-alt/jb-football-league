@extends('layouts.app')

@section('title', __('app.create_competition'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-plus-circle text-success me-2"></i>{{ __('app.create_competition') }}
    </h2>
    <a href="{{ route('competitions.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> {{ __('app.back_to_competitions') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('competitions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fw-semibold">{{ __('app.competition_name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                           value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="season" class="form-label fw-semibold">{{ __('app.season') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('season') is-invalid @enderror" id="season" name="season"
                           value="{{ old('season') }}" placeholder="e.g. 2026" required>
                    @error('season')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="type" class="form-label fw-semibold">{{ __('app.type') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="">{{ __('app.select_type') }}</option>
                        <option value="league" {{ old('type') === 'league' ? 'selected' : '' }}>{{ __('app.league') }}</option>
                        <option value="knockout" {{ old('type') === 'knockout' ? 'selected' : '' }}>{{ __('app.knockout') }}</option>
                        <option value="futsal" {{ old('type') === 'futsal' ? 'selected' : '' }}>{{ __('app.futsal') }}</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label fw-semibold">{{ __('app.status') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="">{{ __('app.select_status') }}</option>
                        <option value="upcoming" {{ old('status') === 'upcoming' ? 'selected' : '' }}>{{ __('app.upcoming') }}</option>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>{{ __('app.active') }}</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>{{ __('app.completed') }}</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="start_date" class="form-label fw-semibold">{{ __('app.start_date') }} <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date"
                           value="{{ old('start_date') }}" required>
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="end_date" class="form-label fw-semibold">{{ __('app.end_date') }} <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date"
                           value="{{ old('end_date') }}" required>
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="max_players" class="form-label fw-semibold">{{ __('app.max_players') }}</label>
                    <input type="number" class="form-control @error('max_players') is-invalid @enderror" id="max_players" name="max_players"
                           value="{{ old('max_players', 25) }}" min="1">
                    @error('max_players')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="max_officials" class="form-label fw-semibold">{{ __('app.max_officials') }}</label>
                    <input type="number" class="form-control @error('max_officials') is-invalid @enderror" id="max_officials" name="max_officials"
                           value="{{ old('max_officials', 7) }}" min="1">
                    @error('max_officials')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="logo" class="form-label fw-semibold">Competition Logo</label>
                <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*">
                @error('logo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Optional. Max 2MB. Recommended: PNG with transparent background.</div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label fw-semibold">{{ __('app.description') }}</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                          rows="4">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> {{ __('app.create_competition') }}
                </button>
                <a href="{{ route('competitions.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
