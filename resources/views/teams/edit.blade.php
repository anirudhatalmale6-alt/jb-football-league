@extends('layouts.app')

@section('title', __('app.edit_team'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-edit text-warning me-2"></i>{{ __('app.edit_team') }}
    </h2>
    <a href="{{ route('teams.show', $team) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> {{ __('app.back_to_team') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('teams.update', $team) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="competition_id" class="form-label fw-semibold">{{ __('app.competition') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('competition_id') is-invalid @enderror" id="competition_id" name="competition_id" required>
                        <option value="">{{ __('app.select_competition_dropdown') }}</option>
                        @foreach($competitions as $competition)
                            <option value="{{ $competition->id }}" {{ old('competition_id', $team->competition_id) == $competition->id ? 'selected' : '' }}>
                                {{ $competition->name }} ({{ $competition->season }})
                            </option>
                        @endforeach
                    </select>
                    @error('competition_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fw-semibold">{{ __('app.team_name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                           value="{{ old('name', $team->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="short_name" class="form-label fw-semibold">{{ __('app.short_name') }}</label>
                    <input type="text" class="form-control @error('short_name') is-invalid @enderror" id="short_name" name="short_name"
                           value="{{ old('short_name', $team->short_name) }}" placeholder="e.g. JDT">
                    @error('short_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="manager_name" class="form-label fw-semibold">{{ __('app.manager_name') }}</label>
                    <input type="text" class="form-control @error('manager_name') is-invalid @enderror" id="manager_name" name="manager_name"
                           value="{{ old('manager_name', $team->manager_name) }}">
                    @error('manager_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="contact_email" class="form-label fw-semibold">{{ __('app.contact_email') }}</label>
                    <input type="email" class="form-control @error('contact_email') is-invalid @enderror" id="contact_email" name="contact_email"
                           value="{{ old('contact_email', $team->contact_email) }}">
                    @error('contact_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="contact_phone" class="form-label fw-semibold">{{ __('app.contact_phone') }}</label>
                    <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" id="contact_phone" name="contact_phone"
                           value="{{ old('contact_phone', $team->contact_phone) }}">
                    @error('contact_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="group_id" class="form-label fw-semibold">{{ __('app.group') }}</label>
                    <select class="form-select @error('group_id') is-invalid @enderror" id="group_id" name="group_id">
                        <option value="">{{ __('app.no_group') }}</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ old('group_id', $team->group_id) == $group->id ? 'selected' : '' }}>
                                {{ $group->competition->name ?? '' }} - {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('group_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="logo" class="form-label fw-semibold">{{ __('app.team_logo') }}</label>
                    @if($team->logo)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $team->logo) }}" alt="Current logo" class="img-thumbnail" style="max-height: 80px;">
                        </div>
                    @endif
                    <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo"
                           accept="image/*">
                    <div class="form-text">{{ __('app.logo_desc_edit') }}</div>
                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label fw-semibold">{{ __('app.status') }}</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                            <option value="pending" {{ old('status', $team->status) === 'pending' ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                            <option value="approved" {{ old('status', $team->status) === 'approved' ? 'selected' : '' }}>{{ __('app.approved') }}</option>
                            <option value="rejected" {{ old('status', $team->status) === 'rejected' ? 'selected' : '' }}>{{ __('app.rejected') }}</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endif

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> {{ __('app.update_team') }}
                </button>
                <a href="{{ route('teams.show', $team) }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
