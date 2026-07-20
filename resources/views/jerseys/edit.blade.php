@extends('layouts.app')

@section('title', 'Submit Jersey Colours')

@php
    $deadline = $match->match_date ? $match->match_date->copy()->subDays(3) : null;
    $isHome = $team->id === $match->home_team_id;
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-tshirt text-success me-2"></i>Submit Jersey Colours
    </h2>
    <a href="{{ route('matches.show', $match->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back to Match
    </a>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap">
            <div>
                <div class="fw-bold fs-5">{{ $team->name }}</div>
                <div class="text-muted small">
                    {{ $match->homeTeam->name ?? 'Home' }} vs {{ $match->awayTeam->name ?? 'Away' }}
                    &mdash; {{ $isHome ? 'Home Team' : 'Away Team' }}
                </div>
                <div class="text-muted small">
                    <i class="fas fa-calendar me-1"></i>{{ $match->match_date ? $match->match_date->format('d M Y, h:i A') : 'TBC' }}
                </div>
            </div>
            @if($deadline)
                <div class="text-end">
                    <div class="text-muted small">Submission Deadline</div>
                    <div class="fw-bold text-danger">{{ $deadline->format('d M Y, h:i A') }}</div>
                    <div class="small {{ now()->gt($deadline) ? 'text-danger' : 'text-muted' }}">
                        {{ now()->gt($deadline) ? 'Deadline passed' : $deadline->diffForHumans() }}
                    </div>
                </div>
            @endif
        </div>

        @if($jersey->exists && $jersey->isAmendmentRequested() && $jersey->amendment_note)
            <div class="alert alert-warning mt-3 mb-0">
                <strong><i class="fas fa-exclamation-triangle me-1"></i>Amendment Requested:</strong>
                {{ $jersey->amendment_note }}
            </div>
        @endif
    </div>
</div>

<form action="{{ route('jerseys.store', [$match->id, $team->id]) }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="card mb-3">
        <div class="card-header bg-light"><i class="fas fa-layer-group me-2"></i>Kit Selection</div>
        <div class="card-body">
            <label class="form-label fw-semibold">Which kit will you use for this match?</label>
            <div class="d-flex gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="kit_type" id="kit_primary" value="primary"
                        {{ old('kit_type', $jersey->kit_type ?? 'primary') === 'primary' ? 'checked' : '' }}>
                    <label class="form-check-label" for="kit_primary">Primary Kit</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="kit_type" id="kit_alternative" value="alternative"
                        {{ old('kit_type', $jersey->kit_type ?? '') === 'alternative' ? 'checked' : '' }}>
                    <label class="form-check-label" for="kit_alternative">Alternative Kit</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light"><i class="fas fa-running me-2"></i>Outfield Players</div>
        <div class="card-body">
            <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i>Just click each box and pick the colour — no need to type a colour name.</p>
            <div class="row g-3">
                @php
                    $outfield = [
                        ['shirt', 'Shirt Colour', '#1e40af'],
                        ['shorts', 'Shorts Colour', '#ffffff'],
                        ['socks', 'Socks Colour', '#1e40af'],
                    ];
                @endphp
                @foreach($outfield as [$key, $label, $default])
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ $label }} <span class="text-danger">*</span></label>
                        <input type="color" class="form-control form-control-color w-100" style="height:48px;" name="{{ $key }}_hex"
                            value="{{ old($key.'_hex', $jersey->{$key.'_hex'} ?? $default) }}" title="Pick colour">
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light"><i class="fas fa-hand-paper me-2"></i>Goalkeeper</div>
        <div class="card-body">
            <div class="row g-3">
                @php
                    $gk = [
                        ['gk_shirt', 'GK Shirt Colour', '#22c55e'],
                        ['gk_shorts', 'GK Shorts Colour', '#000000'],
                        ['gk_socks', 'GK Socks Colour', '#22c55e'],
                    ];
                @endphp
                @foreach($gk as [$key, $label, $default])
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ $label }} <span class="text-danger">*</span></label>
                        <input type="color" class="form-control form-control-color w-100" style="height:48px;" name="{{ $key }}_hex"
                            value="{{ old($key.'_hex', $jersey->{$key.'_hex'} ?? $default) }}" title="Pick colour">
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light"><i class="fas fa-camera me-2"></i>Jersey Photo (optional)</div>
        <div class="card-body">
            @if($jersey->exists && $jersey->photo)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $jersey->photo) }}" alt="Jersey" style="max-height:160px;border-radius:6px;border:1px solid #ddd;">
                    <div class="small text-muted">Current photo. Upload a new one to replace it.</div>
                </div>
            @endif
            <input type="file" class="form-control" name="photo" accept="image/*">
            <div class="form-text">Helps the Match Commissioner check the actual design. Max 10MB.</div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" name="action" value="submit" class="btn btn-success">
            <i class="fas fa-paper-plane me-1"></i>Submit Jersey Colours
        </button>
        <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">
            <i class="fas fa-save me-1"></i>Save as Draft
        </button>
    </div>
</form>
@endsection
