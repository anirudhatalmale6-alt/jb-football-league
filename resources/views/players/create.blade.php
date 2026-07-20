@extends('layouts.app')

@section('title', __('app.add_player'))

@push('styles')
<style>
    .form-control[type="text"], .form-control[type="email"] {
        text-transform: uppercase;
    }
</style>
@endpush

@section('content')
@if(session('error'))
    <div class="alert alert-danger d-flex align-items-start mb-4">
        <i class="fas fa-exclamation-circle me-3 mt-1 fa-lg"></i>
        <div>
            <strong>{{ __('app.error_label') }}</strong><br>
            {{ session('error') }}
        </div>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-plus-circle text-success me-2"></i>{{ __('app.add_player') }}
    </h2>
    <a href="{{ route('players.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> {{ __('app.back_to_players') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('players.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="team_id" class="form-label fw-semibold">{{ __('app.team') }} <span class="text-danger">*</span></label>
                    @if(isset($lockedTeam) && $lockedTeam)
                        <input type="hidden" name="team_id" value="{{ $teams->first()->id }}">
                        <input type="text" class="form-control fw-bold" value="{{ $teams->first()->name }}" disabled style="background-color: #e9ecef;">
                        <small class="text-muted"><i class="fas fa-lock me-1"></i>Team locked to your assigned team</small>
                    @else
                        <select class="form-select @error('team_id') is-invalid @enderror" id="team_id" name="team_id" required>
                            <option value="">{{ __('app.select_team') }}</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('team_id', request('team_id')) == $team->id ? 'selected' : '' }}>
                                    {{ $team->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    @error('team_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fw-semibold">{{ __('app.player_name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                           value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="jersey_number" class="form-label fw-semibold">{{ __('app.jersey_number') }} <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('jersey_number') is-invalid @enderror" id="jersey_number" name="jersey_number"
                           value="{{ old('jersey_number') }}" min="1" max="99" required>
                    @error('jersey_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="position" class="form-label fw-semibold">{{ __('app.position') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('position') is-invalid @enderror" id="position" name="position" required>
                        <option value="">{{ __('app.select_position_player') }}</option>
                        <option value="goalkeeper" {{ old('position') === 'goalkeeper' ? 'selected' : '' }}>{{ __('app.goalkeeper') }}</option>
                        <option value="defender" {{ old('position') === 'defender' ? 'selected' : '' }}>{{ __('app.defender') }}</option>
                        <option value="midfielder" {{ old('position') === 'midfielder' ? 'selected' : '' }}>{{ __('app.midfielder') }}</option>
                        <option value="forward" {{ old('position') === 'forward' ? 'selected' : '' }}>{{ __('app.forward') }}</option>
                    </select>
                    @error('position')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="ic_number" class="form-label fw-semibold">{{ __('app.ic_number') }}</label>
                    <input type="text" class="form-control @error('ic_number') is-invalid @enderror" id="ic_number" name="ic_number"
                           value="{{ old('ic_number') }}" placeholder="XXXXXX-XX-XXXX" maxlength="14">
                    @error('ic_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="date_of_birth" class="form-label fw-semibold">{{ __('app.date_of_birth') }} <i class="fas fa-lock text-muted" style="font-size:0.7rem;" id="dobLockIcon"@if(!Auth::user()->isTeamManager()) style="display:none;" @endif></i></label>
                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth"
                           value="{{ old('date_of_birth') }}"@if(Auth::user()->isTeamManager()) readonly @endif>
                    <div id="dobInfo" class="form-text" style="display:none;">
                        <span id="dobAge" class="fw-semibold"></span>
                        <span id="dobU23Badge"></span>
                    </div>
                    <div id="icWarning" class="text-danger small" style="display:none;"></div>
                    @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>



            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ic_photo" class="form-label fw-semibold">{{ __('app.ic_photo') }}</label>
                    <input type="file" class="form-control @error('ic_photo') is-invalid @enderror" id="ic_photo" name="ic_photo"
                           accept="image/*">
                    <div class="form-text">{{ __('app.ic_photo_desc') }}</div>
                    @error('ic_photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="photo" class="form-label fw-semibold">{{ __('app.player_photo') }}</label>
                    <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo"
                           accept="image/*">
                    <div class="form-text">{{ __('app.photo_desc') }}</div>
                    @error('photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> {{ __('app.add_player') }}
                </button>
                <a href="{{ route('players.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Auto-uppercase all text inputs
    document.querySelectorAll('input[type="text"]').forEach(function(input) {
        input.addEventListener('input', function() {
            var start = this.selectionStart;
            var end = this.selectionEnd;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(start, end);
        });
    });

    var icInput = document.getElementById('ic_number');

    // Auto-format IC number with dashes: XXXXXX-XX-XXXX
    function formatIcNumber(value) {
        var digits = value.replace(/[^0-9]/g, '');
        if (digits.length <= 6) return digits;
        if (digits.length <= 8) return digits.substring(0, 6) + '-' + digits.substring(6);
        return digits.substring(0, 6) + '-' + digits.substring(6, 8) + '-' + digits.substring(8, 12);
    }

    icInput.addEventListener('input', function(e) {
        var pos = this.selectionStart;
        var oldVal = this.value;
        var formatted = formatIcNumber(this.value);
        if (formatted !== oldVal) {
            this.value = formatted;
            var diff = formatted.length - oldVal.length;
            this.setSelectionRange(pos + diff, pos + diff);
        }
    });


    var dobInput = document.getElementById('date_of_birth');
    var dobInfo = document.getElementById('dobInfo');
    var dobAge = document.getElementById('dobAge');
    var dobU23Badge = document.getElementById('dobU23Badge');
    var icWarning = document.getElementById('icWarning');

    function parseIcDate(ic) {
        var digits = ic.replace(/[^0-9]/g, '');
        if (digits.length < 6) return null;

        var yy = parseInt(digits.substring(0, 2), 10);
        var mm = parseInt(digits.substring(2, 4), 10);
        var dd = parseInt(digits.substring(4, 6), 10);

        if (mm < 1 || mm > 12) return null;

        var currentYear = new Date().getFullYear();
        var year = (2000 + yy <= currentYear) ? 2000 + yy : 1900 + yy;

        var testDate = new Date(year, mm - 1, dd);
        if (testDate.getFullYear() !== year || testDate.getMonth() !== mm - 1 || testDate.getDate() !== dd) {
            return null;
        }

        var today = new Date();
        today.setHours(0, 0, 0, 0);
        if (testDate >= today) return null;

        return { year: year, month: mm, day: dd, date: testDate };
    }

    function calculateAge(dob) {
        var today = new Date();
        var age = today.getFullYear() - dob.getFullYear();
        var m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
            age--;
        }
        return age;
    }

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function updateFromIc() {
        var ic = icInput.value.trim();
        icWarning.style.display = 'none';
        icWarning.textContent = '';

        if (!ic || ic.replace(/[^0-9]/g, '').length < 6) {
            dobInfo.style.display = 'none';
            return;
        }

        var parsed = parseIcDate(ic);
        if (!parsed) {
            icWarning.style.display = 'block';
            icWarning.textContent = 'Invalid IC number. Please check the first 6 digits because the date of birth cannot be detected.';
            dobInfo.style.display = 'none';
            return;
        }

        var dateStr = parsed.year + '-' + pad(parsed.month) + '-' + pad(parsed.day);
        dobInput.value = dateStr;

        var age = calculateAge(parsed.date);
        dobAge.textContent = 'Age: ' + age + ' years old';
        dobInfo.style.display = 'block';

        if (age <= 23) {
            dobU23Badge.innerHTML = ' <span class="badge bg-warning text-dark">U23</span>';
        } else {
            dobU23Badge.innerHTML = '';
        }
    }

    icInput.addEventListener('input', updateFromIc);
    icInput.addEventListener('change', updateFromIc);
    icInput.addEventListener('blur', updateFromIc);


    // Validate IC format on submit
    document.querySelector('form').addEventListener('submit', function(e) {
        var ic = icInput.value.trim();
        if (ic && !/^\d{6}-\d{2}-\d{4}$/.test(ic)) {
            e.preventDefault();
            alert('IC Number must be in the format XXXXXX-XX-XXXX (e.g. 900101-01-1234). Please include the dashes.');
            icInput.focus();
            return false;
        }
    });

    if (icInput.value.trim().length >= 6) {
        updateFromIc();
    }
});
</script>
@endpush

@endsection
