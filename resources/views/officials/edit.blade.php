@extends('layouts.app')

@section('title', 'Edit Official')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-edit text-warning me-2"></i>Edit Official
    </h2>
    <a href="{{ route('teams.show', $official->team_id) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Team
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('officials.update', $official) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fw-semibold">Official Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                           value="{{ old('name', $official->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="role" class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                        <option value="">-- Select Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ old('role', $official->role) === $role ? 'selected' : '' }}>{{ $role }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ic_number" class="form-label fw-semibold">IC Number</label>
                    <input type="text" class="form-control @error('ic_number') is-invalid @enderror" id="ic_number" name="ic_number"
                           value="{{ old('ic_number', $official->ic_number) }}" placeholder="e.g. 900101-01-1234">
                    @error('ic_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="contact_phone" class="form-label fw-semibold">Contact Phone</label>
                    <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" id="contact_phone" name="contact_phone"
                           value="{{ old('contact_phone', $official->contact_phone) }}" placeholder="e.g. 012-3456789">
                    @error('contact_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="photo" class="form-label fw-semibold">Photo</label>
                    @if($official->photo)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $official->photo) }}" alt="Current photo" class="img-thumbnail" style="max-height: 80px;">
                        </div>
                    @endif
                    <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo"
                           accept="image/*">
                    <div class="form-text">Max 2MB. Leave empty to keep current photo.</div>
                    @error('photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="certificate" class="form-label fw-semibold">Coaching Certificate</label>
                    @if($official->certificate)
                        <div class="mb-2">
                            <a href="{{ asset('storage/' . $official->certificate) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i> View Current Certificate
                            </a>
                        </div>
                    @endif
                    <input type="file" class="form-control @error('certificate') is-invalid @enderror" id="certificate" name="certificate"
                           accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">Upload coaching certificate - PDF or image, max 5MB. Leave empty to keep current file.</div>
                    @error('certificate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> Update Official
                </button>
                <a href="{{ route('teams.show', $official->team_id) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
