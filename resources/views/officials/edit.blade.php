@extends('layouts.app')

@section('title', __('app.edit_official'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-edit text-warning me-2"></i>{{ __('app.edit_official') }}
    </h2>
    <a href="{{ route('teams.show', $official->team_id) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> {{ __('app.back_to_team') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('officials.update', $official) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fw-semibold">{{ __('app.official_name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                           value="{{ old('name', $official->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="role" class="form-label fw-semibold">{{ __('app.role') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                        <option value="">{{ __('app.select_role') }}</option>
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
                    <label for="nationality" class="form-label fw-semibold">{{ __('app.citizenship') }}</label>
                    <select class="form-select @error('nationality') is-invalid @enderror" id="nationality" name="nationality">
                        <option value="Malaysian" {{ old('nationality', $official->nationality) === 'Malaysian' ? 'selected' : '' }}>{{ __('app.malaysian') }}</option>
                        <option value="Non-Malaysian" {{ old('nationality', $official->nationality) === 'Non-Malaysian' ? 'selected' : '' }}>{{ __('app.non_malaysian') }}</option>
                    </select>
                    @error('nationality')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="ic_number" class="form-label fw-semibold">{{ __('app.ic_passport_number') }}</label>
                    <input type="text" class="form-control @error('ic_number') is-invalid @enderror" id="ic_number" name="ic_number"
                           value="{{ old('ic_number', $official->ic_number) }}" placeholder="e.g. 900101-01-1234">
                    @error('ic_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="contact_phone" class="form-label fw-semibold">{{ __('app.contact_phone') }}</label>
                    <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" id="contact_phone" name="contact_phone"
                           value="{{ old('contact_phone', $official->contact_phone) }}" placeholder="e.g. 012-3456789">
                    @error('contact_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ic_photo" class="form-label fw-semibold">{{ __('app.ic_passport_photo') }}</label>
                    @if($official->ic_photo)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $official->ic_photo) }}" alt="Current IC" class="img-thumbnail" style="max-height: 80px;">
                        </div>
                    @endif
                    <input type="file" class="form-control @error('ic_photo') is-invalid @enderror" id="ic_photo" name="ic_photo"
                           accept="image/*">
                    <div class="form-text">{{ __('app.ic_passport_photo_desc') }}</div>
                    @error('ic_photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="photo" class="form-label fw-semibold">{{ __('app.photo') }}</label>
                    @if($official->photo)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $official->photo) }}" alt="Current photo" class="img-thumbnail" style="max-height: 80px;">
                        </div>
                    @endif
                    <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo"
                           accept="image/*">
                    <div class="form-text">{{ __('app.photo_desc_edit') }}</div>
                    @error('photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="certificate" class="form-label fw-semibold">{{ __('app.coaching_certificate') }}</label>
                    @if($official->certificate)
                        <div class="mb-2">
                            <a href="{{ asset('storage/' . $official->certificate) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i> {{ __('app.view_current_certificate') }}
                            </a>
                        </div>
                    @endif
                    <input type="file" class="form-control @error('certificate') is-invalid @enderror" id="certificate" name="certificate"
                           accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">{{ __('app.coaching_cert_desc_edit') }}</div>
                    @error('certificate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> {{ __('app.update_official') }}
                </button>
                <a href="{{ route('teams.show', $official->team_id) }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
