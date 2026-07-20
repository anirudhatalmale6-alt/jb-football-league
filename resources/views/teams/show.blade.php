@extends('layouts.app')

@section('title', $team->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-shield-halved text-success me-2"></i>{{ $team->name }}
    </h2>
    <div class="d-flex gap-2 flex-wrap">
        @auth
            @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                <a href="{{ route('teams.edit', $team) }}" class="btn btn-outline-warning">
                    <i class="fas fa-edit me-1"></i> {{ __('app.edit') }}
                </a>
                @if($team->status !== 'approved')
                    <form action="{{ route('teams.approve', $team) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> {{ __('app.approve') }}
                        </button>
                    </form>
                @endif
                @if($team->status !== 'rejected')
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-undo me-1"></i> {{ __('app.reject') }}
                    </button>
                @endif
                @if($team->status !== 'withdrawn')
                    <form action="{{ route('teams.withdraw', $team) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('{{ __('app.confirm_withdraw') }}');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-ban me-1"></i> {{ __('app.withdraw') }}
                        </button>
                    </form>
                @endif
                @if(in_array($team->competition_id, [3, 4], true) && $team->status === 'approved' && (!$promotionOffer || $promotionOffer->status !== 'pending' || $promotionOffer->isExpired()))
                    <a href="{{ route('promotions.create', $team) }}" class="btn btn-success">
                        <i class="fas fa-trophy me-1"></i> Promote
                    </a>
                @endif
                @if($team->competition_id === 2 && $team->status === 'approved')
                    <a href="{{ route('relegations.create', $team) }}" class="btn btn-danger">
                        <i class="fas fa-arrow-down me-1"></i> Relegate
                    </a>
                @endif
            @elseif(auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))
                @if($team->status === 'pending' || $team->status === 'rejected')
                    <a href="{{ route('teams.edit', $team) }}" class="btn btn-outline-warning">
                        <i class="fas fa-edit me-1"></i> {{ __('app.edit') }}
                    </a>
                @endif
                @if($team->status !== 'withdrawn')
                    <form action="{{ route('teams.withdraw', $team) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('{{ __('app.confirm_withdraw') }}');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-ban me-1"></i> {{ __('app.withdraw') }}
                        </button>
                    </form>
                @endif
            @endif
        @endauth
        @auth
            @if($team->status === 'approved' && $team->competition_id != 1 && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))))
                <a href="{{ route('teams.eligibility-letter', $team) }}" class="btn btn-primary" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> {{ __('app.eligibility_letter') }}
                </a>
            @endif
        @endauth
            <a href="{{ route('teams.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> {{ __('app.back') }}
        </a>
    </div>
</div>

@if(isset($participations) && $participations->count())
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white py-2">
        <i class="fas fa-trophy me-2"></i>{{ __('app.competition_participation') }}
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('app.competition') }}</th>
                        <th class="text-center">{{ __('app.status') }}</th>
                        <th class="text-center">{{ __('app.players') }}</th>
                        <th class="text-center">{{ __('app.officials') }}</th>
                        <th class="text-center">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($participations as $part)
                        <tr @if($part->id === $team->id) class="table-active" @endif>
                            <td class="fw-semibold">
                                {{ $part->competition->name ?? '-' }}
                                @if($part->id === $team->id)<span class="badge bg-primary ms-1">{{ __('app.viewing') }}</span>@endif
                            </td>
                            <td class="text-center">
                                @php $st = $part->status; @endphp
                                <span class="badge bg-{{ $st==='approved'?'success':($st==='pending'?'warning text-dark':($st==='rejected'?'danger':'secondary')) }}">{{ ucfirst($st) }}</span>
                            </td>
                            <td class="text-center"><span class="badge bg-secondary">{{ $part->players_count }}</span></td>
                            <td class="text-center"><span class="badge bg-info text-dark">{{ $part->officials_count }}</span></td>
                            <td class="text-center">
                                @if($part->id === $team->id)
                                    <span class="text-muted small">&mdash;</span>
                                @else
                                    <a href="{{ route('teams.show', $part->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye me-1"></i>{{ __('app.view') }}</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Team Managers: link/unlink manager accounts so they can register players. Super Admin / League Admin only. --}}
@auth
@if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
<div class="card mb-4 border-info">
    <div class="card-header bg-info text-white py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-user-tie me-2"></i>{{ __('app.team_managers') }}</h6>
        <span class="badge bg-light text-dark">{{ $team->managers->count() }}</span>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i>{{ __('app.team_managers_help') }}</p>

        @if($team->managers->isEmpty())
            <div class="alert alert-warning py-2 mb-3">
                <i class="fas fa-exclamation-triangle me-1"></i>{{ __('app.no_manager_linked') }}
            </div>
        @else
            <div class="table-responsive mb-3">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.email_address') }}</th>
                        <th class="text-end">{{ __('app.action') }}</th>
                    </tr></thead>
                    <tbody>
                    @foreach($team->managers as $mgr)
                        <tr>
                            <td>{{ $mgr->name }}</td>
                            <td class="small text-muted">{{ $mgr->email }}</td>
                            <td class="text-end">
                                <form action="{{ route('teams.remove-manager', [$team->id, $mgr->id]) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('{{ __('app.confirm_remove_manager') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm"><i class="fas fa-unlink me-1"></i>{{ __('app.unlink') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <form action="{{ route('teams.assign-manager', $team->id) }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-8">
                <label class="form-label small fw-bold mb-1">{{ __('app.assign_manager') }}</label>
                <select name="user_id" class="form-select form-select-sm" required>
                    <option value="">{{ __('app.select_manager_account') }}</option>
                    @foreach($assignableManagers as $am)
                        <option value="{{ $am->id }}">{{ $am->name }} ({{ $am->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-info text-white btn-sm w-100" @disabled($assignableManagers->isEmpty())>
                    <i class="fas fa-link me-1"></i>{{ __('app.link_manager') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endauth

@if($team->status === 'rejected')
    <div class="alert alert-warning d-flex align-items-start mb-4">
        <i class="fas fa-exclamation-triangle me-3 mt-1 fa-lg"></i>
        <div>
            @auth
                @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id)))
                    <strong>{{ __('app.rejection_reason') }}:</strong>
                    <p class="mb-0 mt-1">{{ $team->rejection_reason }}</p>
                    @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                        <button type="button" class="btn btn-sm btn-outline-warning mt-2" data-bs-toggle="modal" data-bs-target="#editRejectionModal">
                            <i class="fas fa-edit me-1"></i> Edit Reason & Resend
                        </button>
                    @endif
                    @if(auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))
                        <a href="{{ route('teams.edit', $team) }}" class="btn btn-sm btn-warning mt-2">
                            <i class="fas fa-edit me-1"></i> {{ __('app.edit_and_resubmit') }}
                        </a>
                    @endif
                @else
                    <strong>{{ __('app.rejected') }}</strong>
                @endif
            @else
                <strong>{{ __('app.rejected') }}</strong>
            @endauth
        </div>
    </div>
@endif

@if($team->status === 'withdrawn')
    <div class="alert alert-secondary d-flex align-items-center mb-4">
        <i class="fas fa-ban me-3 fa-lg"></i>
        <div>
            <strong>{{ __('app.team_has_withdrawn') }}</strong>
        </div>
    </div>
@endif

@if(isset($promotionOffer) && $promotionOffer && $promotionOffer->status === 'pending' && !$promotionOffer->isExpired())
    <div class="alert alert-success d-flex align-items-start mb-4 border-success">
        <i class="fas fa-trophy me-3 mt-1 fa-2x text-success"></i>
        <div class="flex-grow-1">
            <h5 class="mb-1 text-success">Tawaran Kenaikan Pangkat ke {{ $promotionOffer->toCompetition->malayName() }} / {{ $promotionOffer->toCompetition->name }} Promotion Offer</h5>
            <p class="mb-2">Pasukan anda telah ditawarkan untuk bertanding dalam {{ $promotionOffer->toCompetition->malayName() }} 2026. Sila respon dalam tempoh yang ditetapkan.</p>
            <small class="text-danger"><i class="fas fa-clock me-1"></i>Tamat: {{ $promotionOffer->expires_at->format('d M Y, h:i A') }}</small>
        </div>
        <a href="{{ route('promotions.respond', $promotionOffer) }}" class="btn btn-success ms-3">
            <i class="fas fa-reply me-1"></i> Respon
        </a>
    </div>
@endif

@if(isset($promotionOffer) && $promotionOffer && $promotionOffer->status === 'accepted')
    <div class="alert alert-info d-flex align-items-center mb-4">
        <i class="fas fa-check-circle me-3 fa-lg text-info"></i>
        <div>
            <strong>Pasukan ini telah dipromosikan dari {{ $promotionOffer->fromCompetition->malayName() }} ke {{ $promotionOffer->toCompetition->malayName() }}.</strong>
            <br><small>Promotion accepted on {{ $promotionOffer->responded_at->format('d M Y') }}</small>
        </div>
        <a href="{{ route('promotions.letter', $promotionOffer) }}" class="btn btn-outline-info btn-sm ms-auto" target="_blank">
            <i class="fas fa-file-pdf me-1"></i> Letter
        </a>
    </div>
@endif

@auth
    @if($team->status === 'approved' && $team->competition_id != 1 && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))))
        <div class="alert alert-success d-flex align-items-center mb-4">
            <i class="fas fa-certificate me-3 fa-lg"></i>
            <div class="flex-grow-1">
                <strong>{{ __('app.team_approved_banner') }}</strong> &mdash;
                {{ __('app.eligibility_available') }}
            </div>
            <a href="{{ route('teams.eligibility-letter', $team) }}" class="btn btn-success btn-sm ms-3" target="_blank">
                <i class="fas fa-download me-1"></i> {{ __('app.download') }}
            </a>
        </div>
    @endif
@endauth



@auth
@if($team->status === 'approved' && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))))
<!-- Next Steps Action Panel -->
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-tasks me-2"></i>{{ __('app.next_steps') }}
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <!-- Pay League Fee -->
            <div class="{{ $team->competition && $team->competition->type === 'knockout' ? 'col-md-12' : 'col-md-6' }}">
                <div class="card h-100 {{ ($payment && $payment->status === 'paid') ? 'border-success' : 'border-warning' }}">
                    <div class="card-body text-center">
                        @if($payment && $payment->status === 'paid')
                            <div class="mb-3">
                                <span class="d-inline-flex align-items-center justify-content-center bg-success rounded-circle" style="width: 60px; height: 60px;">
                                    <i class="fas fa-check-circle text-white fa-2x"></i>
                                </span>
                            </div>
                            <h5 class="card-title text-success">{{ __('app.fee_paid') }}</h5>
                            <p class="text-muted mb-1">RM {{ number_format($payment->amount, 2) }}</p>
                            <p class="text-muted small">{{ \App\Support\Tz::myt($payment->paid_at, 'd M Y') }}</p>
                            <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-outline-success btn-sm" target="_blank">
                                <i class="fas fa-file-pdf me-1"></i> {{ __('app.receipt_label') }}
                            </a>
                        @else
                            <div class="mb-3">
                                <span class="d-inline-flex align-items-center justify-content-center bg-warning rounded-circle" style="width: 60px; height: 60px;">
                                    <i class="fas fa-money-bill-wave text-white fa-2x"></i>
                                </span>
                            </div>
                            <h5 class="card-title">{{ __('app.pay_league_fee') }}</h5>
                            @if($payment)
                                <p class="fs-4 fw-bold text-primary mb-1">RM {{ number_format($payment->amount, 2) }}</p>
                            @endif
                            <p class="text-danger small mb-3">
                                <i class="fas fa-clock me-1"></i>{{ __('app.deadline_label') }}: 12 July 2026
                            </p>
                            @php
                                // Prefer the team's own toyyibpay bill (exact amount, real-time
                                // status) when one exists; otherwise fall back to the shared link.
                                $payUrl = ($payment && !empty($payment->billcode))
                                    ? 'https://toyyibpay.com/' . $payment->billcode
                                    : ($team->competition->payment_url ?? null);
                            @endphp
                            @if($payUrl)
                                <a href="{{ $payUrl }}" class="btn btn-warning btn-lg w-100" target="_blank">
                                    <i class="fas fa-credit-card me-2"></i>{{ __('app.pay_now') }}
                                </a>
                            @else
                                <p class="text-muted small">{{ __('app.contact_admin_payment') }}</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <!-- Register Players & Officials (league only, not knockout/cup) -->
            @if(!($team->competition && $team->competition->type === 'knockout'))
            <div class="col-md-6">
                <div class="card h-100 border-info">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center bg-info rounded-circle" style="width: 60px; height: 60px;">
                                <i class="fas fa-users text-white fa-2x"></i>
                            </span>
                        </div>
                        <h5 class="card-title">{{ __('app.register_players_officials') }}</h5>
                        <div class="d-flex justify-content-center gap-4 mb-2">
                            <div>
                                <span class="fs-3 fw-bold text-primary">{{ $team->players->count() }}</span>
                                <p class="text-muted small mb-0">{{ __('app.players') }}</p>
                            </div>
                            <div>
                                <span class="fs-3 fw-bold text-primary">{{ $team->officials->count() }}</span>
                                <p class="text-muted small mb-0">{{ __('app.officials') }}</p>
                            </div>
                        </div>
                        <p class="text-danger small mb-3">
                            <i class="fas fa-clock me-1"></i>{{ __('app.deadline_label') }}: 20 July 2026
                        </p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('players.create') }}" class="btn btn-info text-white">
                                <i class="fas fa-user-plus me-1"></i>{{ __('app.add_player') }}
                            </a>
                            <a href="{{ route('officials.create', $team) }}" class="btn btn-outline-info">
                                <i class="fas fa-user-tie me-1"></i>{{ __('app.add_official') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
@endauth

<!-- Team Details Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            @if($team->logo)
                <div class="col-md-2 text-center mb-3">
                    <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }} logo" class="img-fluid rounded" style="max-height: 150px;">
                </div>
            @endif
            <div class="{{ $team->logo ? 'col-md-5' : 'col-md-6' }}">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width: 150px;">{{ __('app.team_name') }}</th>
                        <td class="fw-semibold">{{ $team->name }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('app.short_name') }}</th>
                        <td>{{ $team->short_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('app.competition') }}</th>
                        <td>
                            @if($team->competition)
                                <a href="{{ route('competitions.show', $team->competition) }}">
                                    {{ $team->competition->name }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="{{ $team->logo ? 'col-md-5' : 'col-md-6' }}">
                <table class="table table-borderless mb-0">
                    @if(auth()->check() && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))))
                    <tr>
                        <th class="text-muted" style="width: 150px;">{{ __('app.status') }}</th>
                        <td>
                            @if($team->status === 'approved')
                                <span class="badge bg-success">{{ __('app.approved') }}</span>
                            @elseif($team->status === 'pending')
                                <span class="badge bg-warning text-dark">{{ __('app.pending') }}</span>
                            @elseif($team->status === 'rejected')
                                <span class="badge bg-danger">{{ __('app.rejected') }}</span>
                            @elseif($team->status === 'withdrawn')
                                <span class="badge bg-secondary">{{ __('app.withdrawn') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($team->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @if(optional($team->competition)->type === 'league')
                    <tr>
                        <th class="text-muted">{{ __('app.affiliate_fee') }}</th>
                        <td>
                            @if(!$team->affiliate_fee_required)
                                <span class="badge bg-secondary"><i class="fas fa-ban me-1"></i>{{ __('app.exempt') }}</span>
                                <a href="{{ route('affiliate-fees.index') }}" class="small ms-1">{{ __('app.manage') }}</a>
                            @elseif($team->affiliate_fee_paid)
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>{{ __('app.paid') }}</span>
                            @else
                                <span class="badge bg-danger"><i class="fas fa-exclamation-circle me-1"></i>{{ __('app.unpaid') }}</span>
                                <a href="{{ route('affiliate-fees.index') }}" class="small ms-1">{{ __('app.manage') }}</a>
                            @endif
                        </td>
                    </tr>
                    @endif
                    @endif
                                        <tr>
                        <th class="text-muted">{{ __('app.manager') }}</th>
                        <td>{{ $team->manager_name ?? '-' }}</td>
                    </tr>
                    @if(auth()->check() && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))))
                    <tr>
                        <th class="text-muted">{{ __('app.contact_email') }}</th>
                        <td>{{ $team->contact_email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('app.contact_phone') }}</th>
                        <td>{{ $team->contact_phone ?? '-' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th class="text-muted">{{ $team->resubmitted_at ? 'First Submitted' : 'Submitted' }}</th>
                        <td>{{ $team->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @if($team->resubmitted_at)
                    <tr>
                        <th class="text-muted"><span class="text-warning"><i class="fas fa-redo me-1"></i>Resubmitted</span></th>
                        <td><span class="text-warning fw-semibold">{{ $team->resubmitted_at->format('d M Y, h:i A') }}</span></td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Home Venue / Field Section -->
@if(auth()->check() && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))))
<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">
            <i class="fas fa-map-marker-alt me-2"></i>{{ __('app.venue_field_info') }}
        </h5>
    </div>
    <div class="card-body">
        @if($team->venue_name || $team->venue_location || $team->venue_coordinator_name || $team->venue_coordinator_phone)
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width: 200px;"><i class="fas fa-futbol me-1"></i>{{ __('app.field_name') }}</th>
                        <td class="fw-semibold">{{ $team->venue_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted"><i class="fas fa-location-dot me-1"></i>{{ __('app.field_location') }}</th>
                        <td>{{ $team->venue_location ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width: 200px;"><i class="fas fa-user me-1"></i>{{ __('app.venue_coordinator') }}</th>
                        <td>{{ $team->venue_coordinator_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted"><i class="fas fa-phone me-1"></i>{{ __('app.coordinator_phone') }}</th>
                        <td>{{ $team->venue_coordinator_phone ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
        @else
        <div class="text-center text-muted py-3">
            <i class="fas fa-map-marker-alt fa-2x mb-2 d-block"></i>
            <p class="mb-0">{{ __('app.not_provided') ?? 'No venue / field details were provided during registration.' }}</p>
        </div>
        @endif
    </div>
</div>
@endif

<!-- Players Section -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i>{{ __('app.players') }}
            <span class="badge bg-secondary ms-1">{{ $team->players->count() }}</span>
        </h5>
        @auth
            @if($team->status === 'approved' && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))))
                <a href="{{ route('players.create') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus me-1"></i> {{ __('app.add_player') }}
                </a>
            @endif
        @endauth
    </div>
    <div class="card-body p-0">
        @if($team->players->isEmpty())
            <div class="text-center text-muted py-4">
                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                <p class="mb-0">{{ __('app.no_players_registered') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">{{ __('app.photo') }}</th>
                            <th style="width: 80px;" class="text-center">{{ __('app.jersey_number') }}</th>
                            <th>{{ __('app.name') }}</th>
                            @if(auth()->check() && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))))
                            <th>{{ __('app.ic_number') }}</th>
                            @endif
                            <th class="text-center">Age</th>
                            <th>{{ __('app.position') }}</th>
                            <th>{{ __('app.status') }}</th>
                            <th class="text-center">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($team->players->sortBy('jersey_number') as $player)
                            <tr>
                                <td class="text-center">
                                    @if($player->photo)
                                        <img src="{{ asset('storage/' . $player->photo) }}" alt="{{ $player->name }}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <span class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 40px; height: 40px;">
                                            <i class="fas fa-user text-muted"></i>
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ $player->jersey_number ?? '-' }}</td>
                                <td class="fw-semibold">{{ $player->name }} @if($player->is_u23)<span class="badge bg-warning text-dark" style="font-size:0.6rem;">U23</span>@endif</td>
                                @if(auth()->check() && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))))
                                <td>{{ $player->ic_number ?? '-' }}</td>
                                @endif
                                <td class="text-center">{{ $player->age ?? '-' }}</td>
                                <td>{{ ucfirst($player->position ?? '-') }}</td>
                                <td>
                                    @if($player->status === 'active')
                                        <span class="badge bg-success">{{ __('app.active') }}</span>
                                    @elseif($player->status === 'suspended')
                                        <span class="badge bg-danger">{{ __('app.suspended') }}</span>
                                    @elseif($player->status === 'injured')
                                        <span class="badge bg-warning text-dark">{{ __('app.injured') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($player->status ?? 'unknown') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('players.show', $player) }}" class="btn btn-sm btn-outline-primary" title="{{ __('app.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @auth
                                        @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id)))
                                            <a href="{{ route('players.edit', $player) }}" class="btn btn-sm btn-outline-warning" title="{{ __('app.edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    @endauth
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Officials Section -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-user-tie me-2"></i>{{ __('app.officials') }}
            <span class="badge bg-secondary ms-1">{{ $team->officials->count() }}</span>
        </h5>
        @auth
            @if($team->status === 'approved' && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))))
                <a href="{{ route('officials.create', $team) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus me-1"></i> {{ __('app.add_official') }}
                </a>
            @endif
        @endauth
    </div>
    <div class="card-body p-0">
        @if($team->officials->isEmpty())
            <div class="text-center text-muted py-4">
                <i class="fas fa-user-tie fa-2x mb-2 d-block"></i>
                <p class="mb-0">{{ __('app.no_officials_registered') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">{{ __('app.photo') }}</th>
                            <th>{{ __('app.name') }}</th>
                            @if(auth()->check() && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))))
                            <th>{{ __('app.ic_number') }}</th>
                            @endif
                            <th>{{ __('app.role') }}</th>
                            <th>{{ __('app.contact_phone') }}</th>
                            <th class="text-center">{{ __('app.certificate') }}</th>
                            <th class="text-center">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($team->officials as $official)
                            <tr>
                                <td class="text-center">
                                    @if($official->photo)
                                        <img src="{{ asset('storage/' . $official->photo) }}" alt="{{ $official->name }}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <span class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 40px; height: 40px;">
                                            <i class="fas fa-user-tie text-muted"></i>
                                        </span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $official->name }}</td>
                                @if(auth()->check() && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id))))
                                <td>{{ $official->ic_number ?? '-' }}</td>
                                @endif
                                <td>{{ ucfirst($official->role ?? '-') }}</td>
                                <td>{{ $official->contact_phone ?? '-' }}</td>
                                <td class="text-center">
                                    @if($official->certificate)
                                        <a href="{{ asset('storage/' . $official->certificate) }}" target="_blank" title="View Certificate">
                                            <i class="fas fa-check-circle text-success fa-lg"></i>
                                        </a>
                                    @else
                                        <i class="fas fa-times-circle text-danger fa-lg" title="No certificate uploaded"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @auth
                                        @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($team->id)))
                                            <a href="{{ route('officials.edit', $official) }}" class="btn btn-sm btn-outline-warning" title="{{ __('app.edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('officials.destroy', $official) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('{{ __('app.confirm_remove_official') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('app.delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endauth
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>


@auth
@if(auth()->user()->isSuper())
<!-- Super Admin Status Override -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">
            <i class="fas fa-shield-alt me-2"></i>Super Admin - Status Override
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('teams.changeStatus', $team) }}" method="POST" class="row g-3 align-items-end">
            @csrf
            @method('PATCH')
            <div class="col-md-3">
                <label class="form-label fw-semibold">Current Status</label>
                <div class="mt-1">
                    @if($team->status === 'approved')
                        <span class="badge bg-success fs-6">Approved</span>
                    @elseif($team->status === 'pending')
                        <span class="badge bg-warning text-dark fs-6">Pending</span>
                    @elseif($team->status === 'rejected')
                        <span class="badge bg-danger fs-6">Rejected</span>
                    @else
                        <span class="badge bg-secondary fs-6">{{ ucfirst($team->status) }}</span>
                    @endif
                </div>
            </div>
            <div class="col-md-3">
                <label for="changeStatus" class="form-label fw-semibold">Change To</label>
                <select class="form-select" id="changeStatus" name="status" required>
                    <option value="">-- Select Status --</option>
                    @if($team->status !== 'pending')<option value="pending">Pending</option>@endif
                    @if($team->status !== 'approved')<option value="approved">Approved</option>@endif
                    @if($team->status !== 'rejected')<option value="rejected">Rejected</option>@endif
                    @if($team->status !== 'withdrawn')<option value="withdrawn">Withdrawn</option>@endif
                </select>
            </div>
            <div class="col-md-4">
                <label for="changeReason" class="form-label fw-semibold">Reason (optional)</label>
                <input type="text" class="form-control" id="changeReason" name="reason" placeholder="e.g. Team completed amendments">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Are you sure you want to change this team status?')">
                    <i class="fas fa-sync me-1"></i> Change
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endauth

<!-- Status History Log -->
@auth
@if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
@if($team->statusLogs && $team->statusLogs->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">
            <i class="fas fa-history me-2"></i>Status Change History
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date / Time</th>
                        <th>Previous Status</th>
                        <th></th>
                        <th>New Status</th>
                        <th>Changed By</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($team->statusLogs as $log)
                    <tr>
                        <td class="text-muted">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            <span class="badge bg-{{ $log->old_status === 'approved' ? 'success' : ($log->old_status === 'rejected' ? 'danger' : ($log->old_status === 'pending' ? 'warning text-dark' : 'secondary')) }}">
                                {{ ucfirst($log->old_status) }}
                            </span>
                        </td>
                        <td class="text-center"><i class="fas fa-arrow-right text-muted"></i></td>
                        <td>
                            <span class="badge bg-{{ $log->new_status === 'approved' ? 'success' : ($log->new_status === 'rejected' ? 'danger' : ($log->new_status === 'pending' ? 'warning text-dark' : 'secondary')) }}">
                                {{ ucfirst($log->new_status) }}
                            </span>
                        </td>
                        <td class="fw-semibold">{{ $log->changedByUser->name ?? 'System' }}</td>
                        <td class="text-muted">{{ $log->reason ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endif
@endauth

<!-- Reject Modal -->
@auth
@if((auth()->user()->isSuper() || auth()->user()->isLeagueAdmin()) && $team->status !== 'rejected')
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('teams.reject', $team) }}" method="POST">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-undo me-2"></i>{{ __('app.reject_team') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('app.reject_team_info') }}</p>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label fw-semibold">{{ __('app.rejection_reason') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required
                                  placeholder="{{ __('app.rejection_reason_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-undo me-1"></i> {{ __('app.reject') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endauth

@if($team->status === 'rejected' && auth()->check() && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin()))
<div class="modal fade" id="editRejectionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('teams.update-rejection-reason', $team) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Update Rejection Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Team</label>
                        <p class="mb-0">{{ $team->name }}</p>
                    </div>
                    <div class="mb-3">
                        <label for="rejection_reason_edit" class="form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason_edit" name="rejection_reason" rows="6" required>{{ $team->rejection_reason }}</textarea>
                        <small class="text-muted">You can write a more detailed reason here. Maximum 2000 characters.</small>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="resend_email" name="resend_email" value="1" checked>
                        <label class="form-check-label" for="resend_email">
                            <i class="fas fa-envelope me-1"></i> Resend rejection email to team with updated reason
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i> Update & Send</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
