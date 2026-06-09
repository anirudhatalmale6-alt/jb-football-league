@extends('layouts.app')

@section('title', 'Team Registration')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-clipboard-list text-success me-2"></i>Team Registration
    </h2>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle me-1"></i>
    Register your team for an upcoming competition. You must be logged in to complete registration.
</div>

@if($competitions->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-trophy fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">No competitions available for registration</h5>
            <p class="text-muted">Check back later for upcoming competitions.</p>
        </div>
    </div>
@else
    <div class="row g-4">
        @foreach($competitions as $competition)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="text-center mb-3">
                            @if($competition->logo)
                                <img src="{{ asset('storage/'.$competition->logo) }}" alt="{{ $competition->name }}"
                                     class="img-fluid" style="max-height:80px;object-fit:contain;">
                            @else
                                <i class="fas fa-trophy fa-3x text-success"></i>
                            @endif
                        </div>
                        <h5 class="card-title fw-bold text-center">{{ $competition->name }}</h5>
                        <p class="text-muted small text-center mb-2">{{ $competition->season }}</p>

                        @if($competition->description)
                            <p class="text-muted small">{{ Str::limit($competition->description, 100) }}</p>
                        @endif

                        <ul class="list-unstyled small mb-3">
                            <li class="mb-1">
                                <i class="fas fa-calendar text-success me-1"></i>
                                {{ $competition->start_date ? $competition->start_date->format('d M Y') : 'TBD' }}
                                - {{ $competition->end_date ? $competition->end_date->format('d M Y') : 'TBD' }}
                            </li>
                            <li class="mb-1">
                                <i class="fas fa-users text-success me-1"></i>
                                {{ $competition->teams_count }} team(s) registered
                            </li>
                            <li class="mb-1">
                                @if($competition->type === 'league')
                                    <span class="badge bg-primary">League</span>
                                @elseif($competition->type === 'knockout')
                                    <span class="badge bg-warning text-dark">Knockout</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($competition->type) }}</span>
                                @endif

                                @if($competition->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($competition->status === 'upcoming')
                                    <span class="badge bg-info">Upcoming</span>
                                @elseif($competition->status === 'completed')
                                    <span class="badge bg-secondary">Completed</span>
                                @endif
                            </li>
                        </ul>

                        <div class="mt-auto">
                            <div class="mb-3">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted small">Yuran Penyertaan</td>
                                        <td class="text-end fw-bold">
                                            @if($competition->registration_fee > 0)
                                                RM {{ number_format($competition->registration_fee, 2) }}
                                            @else
                                                <span class="text-success">FREE</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted small">Deposit Keselamatan</td>
                                        <td class="text-end fw-bold">
                                            @if($competition->security_deposit > 0)
                                                RM {{ number_format($competition->security_deposit, 2) }}
                                            @else
                                                <span class="text-muted">TIADA</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted small">Bayaran Hari Perlawanan</td>
                                        <td class="text-end fw-bold">
                                            @if($competition->matchday_fee > 0)
                                                RM {{ number_format($competition->matchday_fee, 2) }}
                                            @else
                                                <span class="text-muted">TIADA</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <a href="{{ route('registration.create', $competition->id) }}" class="btn btn-success w-100">
                                <i class="fas fa-pen-to-square me-1"></i> Register Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
