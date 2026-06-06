@extends('layouts.app')

@section('title', 'Edit Competition')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-edit text-warning me-2"></i>Edit Competition
    </h2>
    <a href="{{ route('competitions.show', $competition) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Competition
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('competitions.update', $competition) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fw-semibold">Competition Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                           value="{{ old('name', $competition->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="season" class="form-label fw-semibold">Season <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('season') is-invalid @enderror" id="season" name="season"
                           value="{{ old('season', $competition->season) }}" placeholder="e.g. 2026" required>
                    @error('season')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="">-- Select Type --</option>
                        <option value="league" {{ old('type', $competition->type) === 'league' ? 'selected' : '' }}>League</option>
                        <option value="knockout" {{ old('type', $competition->type) === 'knockout' ? 'selected' : '' }}>Knockout</option>
                        <option value="futsal" {{ old('type', $competition->type) === 'futsal' ? 'selected' : '' }}>Futsal</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="">-- Select Status --</option>
                        <option value="upcoming" {{ old('status', $competition->status) === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="active" {{ old('status', $competition->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ old('status', $competition->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="start_date" class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date"
                           value="{{ old('start_date', $competition->start_date?->format('Y-m-d')) }}" required>
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="end_date" class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date"
                           value="{{ old('end_date', $competition->end_date?->format('Y-m-d')) }}" required>
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="max_players" class="form-label fw-semibold">Max Players per Team</label>
                    <input type="number" class="form-control @error('max_players') is-invalid @enderror" id="max_players" name="max_players"
                           value="{{ old('max_players', $competition->max_players ?? 25) }}" min="1">
                    @error('max_players')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="max_officials" class="form-label fw-semibold">Max Officials per Team</label>
                    <input type="number" class="form-control @error('max_officials') is-invalid @enderror" id="max_officials" name="max_officials"
                           value="{{ old('max_officials', $competition->max_officials ?? 7) }}" min="1">
                    @error('max_officials')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label fw-semibold">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                          rows="4">{{ old('description', $competition->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> Update Competition
                </button>
                <a href="{{ route('competitions.show', $competition) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
