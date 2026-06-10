@extends('layouts.app')

@section('title', __('app.pra_registration') . ' - ' . $competition->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-pen-to-square text-success me-2"></i>{{ __('app.pra_registration') }}
    </h2>
    <a href="{{ route('registration.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> {{ __('app.back_to_competitions') }}
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    @if($competition->logo)
                        <img src="{{ asset('storage/'.$competition->logo) }}" alt="" class="me-2" style="height:30px;width:30px;object-fit:contain;">
                    @endif
                    {{ $competition->name }} ({{ $competition->season }})
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('registration.store', $competition->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <h6 class="fw-bold text-muted mb-3"><i class="fas fa-shield-halved me-1"></i> {{ __('app.club_information') }}</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-semibold">{{ __('app.club_full_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                   value="{{ old('name') }}" required placeholder="e.g. Kelab Bola Sepak MBIP">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="contact_email" class="form-label fw-semibold">{{ __('app.club_email') }} <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('contact_email') is-invalid @enderror" id="contact_email" name="contact_email"
                                   value="{{ old('contact_email') }}" required placeholder="e.g. info@mbipfc.com">
                            @error('contact_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="short_name" class="form-label fw-semibold">{{ __('app.short_name') }}</label>
                            <input type="text" class="form-control @error('short_name') is-invalid @enderror" id="short_name" name="short_name"
                                   value="{{ old('short_name') }}" placeholder="e.g. MBIP FC" maxlength="10">
                            @error('short_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="logo" class="form-label fw-semibold">{{ __('app.club_logo') }}</label>
                            <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo"
                                   accept="image/*">
                            <div class="form-text">{{ __('app.logo_desc') }}</div>
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-3">
                    <h6 class="fw-bold text-muted mb-3"><i class="fas fa-user me-1"></i> {{ __('app.applicant_information') }}</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="applicant_name" class="form-label fw-semibold">{{ __('app.applicant_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('applicant_name') is-invalid @enderror" id="applicant_name" name="applicant_name"
                                   value="{{ old('applicant_name', Auth::user()->name) }}" required>
                            @error('applicant_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="applicant_position" class="form-label fw-semibold">{{ __('app.applicant_position') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('applicant_position') is-invalid @enderror" id="applicant_position" name="applicant_position" required>
                                <option value="">{{ __('app.select_position') }}</option>
                                <option value="President" {{ old('applicant_position') === 'President' ? 'selected' : '' }}>{{ __('app.president') }}</option>
                                <option value="Secretary" {{ old('applicant_position') === 'Secretary' ? 'selected' : '' }}>{{ __('app.secretary') }}</option>
                                <option value="Treasurer" {{ old('applicant_position') === 'Treasurer' ? 'selected' : '' }}>{{ __('app.treasurer') }}</option>
                                <option value="Team Manager" {{ old('applicant_position') === 'Team Manager' ? 'selected' : '' }}>{{ __('app.team_manager') }}</option>
                                <option value="Head Coach" {{ old('applicant_position') === 'Head Coach' ? 'selected' : '' }}>{{ __('app.head_coach') }}</option>
                                <option value="Other" {{ old('applicant_position') === 'Other' ? 'selected' : '' }}>{{ __('app.other') }}</option>
                            </select>
                            @error('applicant_position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="manager_name" class="form-label fw-semibold">{{ __('app.club_manager_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('manager_name') is-invalid @enderror" id="manager_name" name="manager_name"
                                   value="{{ old('manager_name') }}" required placeholder="Club manager name">
                            @error('manager_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="contact_phone" class="form-label fw-semibold">{{ __('app.manager_phone') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" id="contact_phone" name="contact_phone"
                                   value="{{ old('contact_phone') }}" required placeholder="e.g. 012-3456789">
                            @error('contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Group assignment is done by admin after registration --}}

                    @if(in_array($competition->name, ['JBFA SUPER LEAGUE', 'JBFA PREMIER LEAGUE']))
                    <hr class="my-3">
                    <h6 class="fw-bold text-muted mb-3"><i class="fas fa-map-marker-alt me-1"></i> {{ __('app.venue_field_info') }}</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="venue_name" class="form-label fw-semibold">{{ __('app.field_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('venue_name') is-invalid @enderror" id="venue_name" name="venue_name"
                                   value="{{ old('venue_name') }}" required placeholder="e.g. Padang MBIP">
                            @error('venue_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="venue_location" class="form-label fw-semibold">{{ __('app.field_location') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('venue_location') is-invalid @enderror" id="venue_location" name="venue_location"
                                   value="{{ old('venue_location') }}" required placeholder="e.g. Jalan Danga, Johor Bahru">
                            @error('venue_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="venue_coordinator_name" class="form-label fw-semibold">{{ __('app.venue_coordinator') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('venue_coordinator_name') is-invalid @enderror" id="venue_coordinator_name" name="venue_coordinator_name"
                                   value="{{ old('venue_coordinator_name') }}" required>
                            @error('venue_coordinator_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="venue_coordinator_phone" class="form-label fw-semibold">{{ __('app.coordinator_phone') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('venue_coordinator_phone') is-invalid @enderror" id="venue_coordinator_phone" name="venue_coordinator_phone"
                                   value="{{ old('venue_coordinator_phone') }}" required placeholder="e.g. 012-3456789">
                            @error('venue_coordinator_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @endif

                    <hr class="my-4">

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input @error('terms_agreed') is-invalid @enderror" type="checkbox"
                                   id="terms_agreed" name="terms_agreed" value="1" {{ old('terms_agreed') ? 'checked' : '' }}>
                            <label class="form-check-label" for="terms_agreed">
                                {{ __('app.terms_text') }} <strong>JBFA 2026 League Competition Committee</strong>.
                                <span class="text-danger">*</span>
                            </label>
                            @error('terms_agreed')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane me-1"></i>
                            @if($competition->registration_fee > 0)
                                {{ __('app.submit_proceed_payment') }}
                            @else
                                {{ __('app.submit_registration') }}
                            @endif
                        </button>
                        <a href="{{ route('registration.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-receipt me-1"></i> {{ __('app.registration_summary') }}</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted">{{ __('app.category') }}</td>
                        <td class="fw-semibold text-end">{{ $competition->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ __('app.season') }}</td>
                        <td class="fw-semibold text-end">{{ $competition->season }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ __('app.type') }}</td>
                        <td class="text-end">
                            @if($competition->type === 'league')
                                <span class="badge bg-primary">{{ __('app.league') }}</span>
                            @elseif($competition->type === 'knockout')
                                <span class="badge bg-warning text-dark">{{ __('app.knockout') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($competition->type) }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ __('app.dates') }}</td>
                        <td class="text-end small">
                            {{ $competition->start_date ? $competition->start_date->format('d M Y') : 'TBD' }}<br>
                            to {{ $competition->end_date ? $competition->end_date->format('d M Y') : 'TBD' }}
                        </td>
                    </tr>
                </table>

                <hr>

                <h6 class="fw-bold text-center mb-3">{{ __('app.yuran_pertandingan') }}</h6>
                <table class="table table-sm table-borderless mb-0">
                    @if($competition->registration_fee > 0)
                        <tr>
                            <td class="small">{{ __('app.yuran_penyertaan') }}</td>
                            <td class="fw-bold text-end text-success">RM {{ number_format($competition->registration_fee, 2) }}</td>
                        </tr>
                    @endif
                    @if($competition->security_deposit > 0)
                        <tr>
                            <td class="small">{{ __('app.deposit_keselamatan') }}</td>
                            <td class="fw-bold text-end text-success">RM {{ number_format($competition->security_deposit, 2) }}</td>
                        </tr>
                    @endif
                    @if($competition->matchday_fee > 0)
                        <tr>
                            <td class="small">{{ __('app.bayaran_hari_perlawanan') }}</td>
                            <td class="fw-bold text-end text-success">RM {{ number_format($competition->matchday_fee, 2) }}</td>
                        </tr>
                    @endif
                    @if($competition->registration_fee == 0 && $competition->security_deposit == 0 && $competition->matchday_fee == 0)
                        <tr>
                            <td colspan="2" class="text-center fw-bold text-success fs-4">{{ __('app.free') }}</td>
                        </tr>
                    @endif
                </table>

                @if($competition->registration_fee > 0 || $competition->security_deposit > 0 || $competition->matchday_fee > 0)
                    <p class="text-muted small text-center mt-2 mb-0">{{ __('app.payment_via_fpx') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
