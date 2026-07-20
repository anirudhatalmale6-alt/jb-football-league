@extends('layouts.app')

@section('title', __('app.edit_competition'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-edit text-warning me-2"></i>{{ __('app.edit_competition') }}
    </h2>
    <a href="{{ route('competitions.show', $competition) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> {{ __('app.back_to_competition') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('competitions.update', $competition) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fw-semibold">{{ __('app.competition_name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                           value="{{ old('name', $competition->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="season" class="form-label fw-semibold">{{ __('app.season') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('season') is-invalid @enderror" id="season" name="season"
                           value="{{ old('season', $competition->season) }}" placeholder="e.g. 2026" required>
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
                        <option value="league" {{ old('type', $competition->type) === 'league' ? 'selected' : '' }}>{{ __('app.league') }}</option>
                        <option value="knockout" {{ old('type', $competition->type) === 'knockout' ? 'selected' : '' }}>{{ __('app.knockout') }}</option>
                        <option value="futsal" {{ old('type', $competition->type) === 'futsal' ? 'selected' : '' }}>{{ __('app.futsal') }}</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label fw-semibold">{{ __('app.status') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="">{{ __('app.select_status') }}</option>
                        <option value="upcoming" {{ old('status', $competition->status) === 'upcoming' ? 'selected' : '' }}>{{ __('app.upcoming') }}</option>
                        <option value="active" {{ old('status', $competition->status) === 'active' ? 'selected' : '' }}>{{ __('app.active') }}</option>
                        <option value="completed" {{ old('status', $competition->status) === 'completed' ? 'selected' : '' }}>{{ __('app.completed') }}</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="match_duration" class="form-label fw-semibold">{{ __('app.match_duration_label') }}</label>
                    <input type="number" class="form-control @error('match_duration') is-invalid @enderror" id="match_duration" name="match_duration" min="30" max="130" value="{{ old('match_duration', $competition->match_duration ?? 90) }}">
                    <div class="form-text">{{ __('app.match_duration_hint') }}</div>
                    @error('match_duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="start_date" class="form-label fw-semibold">{{ __('app.start_date') }} <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date"
                           value="{{ old('start_date', $competition->start_date?->format('Y-m-d')) }}" required>
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="end_date" class="form-label fw-semibold">{{ __('app.end_date') }} <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date"
                           value="{{ old('end_date', $competition->end_date?->format('Y-m-d')) }}" required>
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="max_players" class="form-label fw-semibold">{{ __('app.max_players') }}</label>
                    <input type="number" class="form-control @error('max_players') is-invalid @enderror" id="max_players" name="max_players"
                           value="{{ old('max_players', $competition->max_players ?? 25) }}" min="1">
                    @error('max_players')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="max_officials" class="form-label fw-semibold">{{ __('app.max_officials') }}</label>
                    <input type="number" class="form-control @error('max_officials') is-invalid @enderror" id="max_officials" name="max_officials"
                           value="{{ old('max_officials', $competition->max_officials ?? 7) }}" min="1">
                    @error('max_officials')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="logo" class="form-label fw-semibold">Competition Logo</label>
                @if($competition->logo)
                    <div class="mb-2">
                        <img src="{{ asset('storage/'.$competition->logo) }}" alt="Current logo" style="height:64px;width:64px;object-fit:contain;" class="border rounded p-1">
                    </div>
                @endif
                <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*">
                @error('logo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Optional. Max 2MB. Leave empty to keep the current logo.</div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label fw-semibold">{{ __('app.description') }}</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                          rows="4">{{ old('description', $competition->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> {{ __('app.update_competition') }}
                </button>
                <a href="{{ route('competitions.show', $competition) }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
