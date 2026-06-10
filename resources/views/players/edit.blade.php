@extends('layouts.app')

@section('title', 'Edit Player')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-edit text-warning me-2"></i>Edit Player
    </h2>
    <a href="{{ route('players.show', $player) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Player
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('players.update', $player) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="team_id" class="form-label fw-semibold">Team <span class="text-danger">*</span></label>
                    <select class="form-select @error('team_id') is-invalid @enderror" id="team_id" name="team_id" required>
                        <option value="">-- Select Team --</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ old('team_id', $player->team_id) == $team->id ? 'selected' : '' }}>
                                {{ $team->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('team_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fw-semibold">Player Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                           value="{{ old('name', $player->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="jersey_number" class="form-label fw-semibold">Jersey Number <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('jersey_number') is-invalid @enderror" id="jersey_number" name="jersey_number"
                           value="{{ old('jersey_number', $player->jersey_number) }}" min="1" max="99" required>
                    @error('jersey_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="position" class="form-label fw-semibold">Position <span class="text-danger">*</span></label>
                    <select class="form-select @error('position') is-invalid @enderror" id="position" name="position" required>
                        <option value="">-- Select Position --</option>
                        <option value="goalkeeper" {{ old('position', $player->position) === 'goalkeeper' ? 'selected' : '' }}>Goalkeeper</option>
                        <option value="defender" {{ old('position', $player->position) === 'defender' ? 'selected' : '' }}>Defender</option>
                        <option value="midfielder" {{ old('position', $player->position) === 'midfielder' ? 'selected' : '' }}>Midfielder</option>
                        <option value="forward" {{ old('position', $player->position) === 'forward' ? 'selected' : '' }}>Forward</option>
                    </select>
                    @error('position')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="date_of_birth" class="form-label fw-semibold">Date of Birth</label>
                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth"
                           value="{{ old('date_of_birth', $player->date_of_birth?->format('Y-m-d')) }}">
                    @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nationality" class="form-label fw-semibold">Nationality</label>
                    <input type="text" class="form-control @error('nationality') is-invalid @enderror" id="nationality" name="nationality"
                           value="{{ old('nationality', $player->nationality) }}" placeholder="e.g. Malaysian">
                    @error('nationality')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="ic_number" class="form-label fw-semibold">IC Number</label>
                    <input type="text" class="form-control @error('ic_number') is-invalid @enderror" id="ic_number" name="ic_number"
                           value="{{ old('ic_number', $player->ic_number) }}" placeholder="e.g. 900101-01-1234">
                    @error('ic_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="photo" class="form-label fw-semibold">Player Photo</label>
                    @if($player->photo)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $player->photo) }}" alt="Current photo" class="img-thumbnail" style="max-height: 80px;">
                        </div>
                    @endif
                    <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo"
                           accept="image/*">
                    <div class="form-text">Max 2MB. Leave empty to keep current photo.</div>
                    @error('photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> Update Player
                </button>
                <a href="{{ route('players.show', $player) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
