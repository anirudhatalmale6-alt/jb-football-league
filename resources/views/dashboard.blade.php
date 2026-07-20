@extends('layouts.app')

@section('title', __('app.dashboard'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-tachometer-alt text-success me-2"></i>{{ __('app.dashboard') }}
    </h2>
    <span class="text-muted">{{ __('app.welcome_back') }}, {{ Auth::user()->name }}</span>
</div>

<!-- Match Commissioner assignment notification -->
@if(isset($mcAssignments) && $mcAssignments->isNotEmpty())
<div class="alert alert-info mb-4">
    <h6 class="fw-bold mb-2"><i class="fas fa-user-tag me-1"></i>{{ __('app.mc_assigned_notice_title') }}</h6>
    @foreach($mcAssignments as $am)
        <div class="d-flex justify-content-between align-items-center flex-wrap {{ !$loop->last ? 'border-bottom pb-2 mb-2' : '' }}">
            <div>
                <strong>{{ $am->homeTeam->name ?? 'Home' }} vs {{ $am->awayTeam->name ?? 'Away' }}</strong>
                <div class="small text-muted">
                    {{ $am->competition->name ?? '-' }} &bull;
                    <i class="fas fa-calendar me-1"></i>{{ $am->match_date ? $am->match_date->format('d M Y, g:i A') : '-' }}
                    @if($am->venue) &bull; <i class="fas fa-map-marker-alt me-1"></i>{{ $am->venue }} @endif
                </div>
            </div>
            <a href="{{ route('matches.show', $am) }}" class="btn btn-sm btn-primary"><i class="fas fa-gamepad me-1"></i>{{ __('app.match_control') }}</a>
        </div>
    @endforeach
</div>
@endif

<!-- Payment Confirmed (Team Manager) -->
@if(isset($paymentConfirmations) && $paymentConfirmations->isNotEmpty())
<div class="alert alert-success mb-4">
    <h6 class="fw-bold mb-2"><i class="fas fa-check-circle me-1"></i>{{ __('app.payment_confirmed_title') }}</h6>
    @foreach($paymentConfirmations as $pc)
        <div class="d-flex justify-content-between align-items-center flex-wrap {{ !$loop->last ? 'border-bottom pb-2 mb-2' : '' }}">
            <div>
                <strong>{{ $pc->team->name ?? '-' }}</strong>
                <span class="text-muted">&mdash; {{ $pc->competition->name ?? '-' }}</span>
                <br><small class="text-muted">{{ __('app.payment_confirmed_msg') }} &middot; RM {{ number_format($pc->amount, 2) }} &middot; {{ \App\Support\Tz::myt($pc->paid_at, 'd M Y H:i') }}</small>
            </div>
            <a href="{{ route('payments.receipt', $pc->id) }}" class="btn btn-sm btn-outline-success mt-2 mt-md-0">
                <i class="fas fa-file-pdf me-1"></i>{{ __('app.download_receipt') }}
            </a>
        </div>
    @endforeach
</div>
@endif

<!-- Line-Up Reminders (Team Manager) -->
@if(isset($lineupReminders) && $lineupReminders->isNotEmpty())
<div class="alert alert-warning mb-4">
    <h5 class="mb-3"><i class="fas fa-clipboard-check me-2"></i>{{ __("app.lineup_submissions") }}</h5>
    @foreach($lineupReminders as $reminder)
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white rounded">
            <div>
                <strong>{{ $reminder["match"]->homeTeam->name ?? "-" }} vs {{ $reminder["match"]->awayTeam->name ?? "-" }}</strong>
                <br><small class="text-muted">{{ $reminder["match"]->match_date ? $reminder["match"]->match_date->format("d M Y H:i") : "-" }} | {{ $reminder["match"]->venue ?? "-" }}</small>
                @if($reminder["isOverdue"] && (!$reminder["submission"] || $reminder["submission"]->status === "draft"))
                    <br><span class="badge bg-danger">{{ __("app.overdue") }}</span>
                @endif
            </div>
            <div class="text-end">
                @if($reminder["submission"])
                    @if($reminder["submission"]->status === "draft")
                        <span class="badge bg-secondary">{{ __("app.draft") }}</span>
                        <a href="{{ route("lineup-submissions.edit", [$reminder["match"]->id, $reminder["team_id"]]) }}" class="btn btn-sm btn-warning ms-2"><i class="fas fa-edit"></i></a>
                    @elseif($reminder["submission"]->status === "submitted")
                        <span class="badge bg-warning text-dark">{{ __("app.pending_approval") }}</span>
                    @elseif($reminder["submission"]->status === "rejected")
                        <span class="badge bg-danger">{{ __("app.amendment_required") }}</span>
                        <a href="{{ route("lineup-submissions.edit", [$reminder["match"]->id, $reminder["team_id"]]) }}" class="btn btn-sm btn-danger ms-2"><i class="fas fa-edit"></i></a>
                    @elseif($reminder["submission"]->status === "approved")
                        <span class="badge bg-success">{{ __("app.approved") }}</span>
                    @elseif($reminder["submission"]->status === "locked")
                        <span class="badge bg-dark"><i class="fas fa-lock me-1"></i>{{ __("app.locked") }}</span>
                    @endif
                @else
                    <a href="{{ route("lineup-submissions.edit", [$reminder["match"]->id, $reminder["team_id"]]) }}" class="btn btn-sm btn-success"><i class="fas fa-plus me-1"></i>{{ __("app.create_lineup") }}</a>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endif

<!-- Jersey Colour Submission Reminders (Team Manager) -->
@if(isset($jerseyReminders) && $jerseyReminders->isNotEmpty())
<div class="alert alert-warning mb-4">
    <h5 class="mb-3"><i class="fas fa-tshirt me-2"></i>Jersey Colour Submission Required</h5>
    @foreach($jerseyReminders as $r)
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white rounded">
            <div>
                <strong>{{ $r["match"]->homeTeam->name ?? "-" }} vs {{ $r["match"]->awayTeam->name ?? "-" }}</strong>
                <br><small class="text-muted">{{ $r["match"]->match_date ? $r["match"]->match_date->format("d M Y H:i") : "-" }}
                    @if($r["deadline"]) | Deadline: {{ $r["deadline"]->format("d M Y") }} @endif
                </small>
                @if($r["isOverdue"])
                    <br><span class="badge bg-danger">Deadline passed</span>
                @endif
            </div>
            <div class="text-end">
                <span class="badge bg-secondary">Not Submitted</span>
                <a href="{{ route("jerseys.edit", [$r["match"]->id, $r["team_id"]]) }}" class="btn btn-sm btn-success ms-2"><i class="fas fa-tshirt me-1"></i>Submit</a>
            </div>
        </div>
    @endforeach
</div>
@endif

<!-- Pending Line-Up Reviews (Admin) -->
@if(isset($pendingReviews) && $pendingReviews->isNotEmpty())
<div class="alert alert-info mb-4">
    <h5 class="mb-3"><i class="fas fa-clipboard-check me-2"></i>{{ __("app.lineup_submissions") }} - {{ __("app.pending_approval") }}</h5>
    @foreach($pendingReviews as $pr)
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white rounded">
            <div>
                <strong>{{ $pr->team->name ?? "-" }}</strong>
                <br><small class="text-muted">{{ $pr->matchGame->homeTeam->name ?? "" }} vs {{ $pr->matchGame->awayTeam->name ?? "" }} | {{ $pr->matchGame->match_date ? $pr->matchGame->match_date->format("d M Y H:i") : "-" }}</small>
            </div>
            <div>
                <a href="{{ route("lineup-submissions.review", $pr->match_game_id) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye me-1"></i>{{ __("app.review") }}</a>
            </div>
        </div>
    @endforeach
</div>
@endif
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <!-- Total Competitions -->
    <div class="col-md-3 col-sm-6">
        <div class="card h-100 border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem;">
                            {{ __('app.competitions') }}
                        </h6>
                        <h2 class="fw-bold mb-0">{{ $competitionCount }}</h2>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-trophy fa-lg text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Teams -->
    <div class="col-md-3 col-sm-6">
        <div class="card h-100 border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem;">
                            {{ __('app.teams') }}
                        </h6>
                        <h2 class="fw-bold mb-0">{{ $teamCount }}</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-shield-halved fa-lg text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Players -->
    <div class="col-md-3 col-sm-6">
        <div class="card h-100 border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem;">
                            {{ __('app.players') }}
                        </h6>
                        <h2 class="fw-bold mb-0">{{ $playerCount }}</h2>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-users fa-lg text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Matches -->
    <div class="col-md-3 col-sm-6">
        <div class="card h-100 border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem;">
                            {{ __('app.upcoming_matches') }}
                        </h6>
                        <h2 class="fw-bold mb-0">{{ $upcomingMatchCount }}</h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-calendar-days fa-lg text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Matches & Standings -->
<div class="row g-4">
    <!-- Recent Matches -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clock-rotate-left me-2"></i>{{ __('app.recent_matches') }}
                </h5>
                <a href="{{ route('matches.index') }}" class="btn btn-sm btn-outline-light">
                    {{ __('app.view_all') }}
                </a>
            </div>
            <div class="card-body p-0">
                @if($recentMatches->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-calendar-xmark fa-3x mb-3 d-block"></i>
                        No recent matches found.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('app.date') }}</th>
                                    <th>{{ __('app.home') }}</th>
                                    <th class="text-center">{{ __('app.score') }}</th>
                                    <th>{{ __('app.away') }}</th>
                                    <th>{{ __('app.competition') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentMatches as $match)
                                    <tr>
                                        <td class="text-muted">
                                            {{ \Carbon\Carbon::parse($match->match_date)->format('d M Y') }}
                                        </td>
                                        <td class="fw-semibold">{{ $match->homeTeam->name }}</td>
                                        <td class="text-center">
                                            @if($match->status === 'completed')
                                                <span class="badge bg-dark fs-6">
                                                    {{ $match->home_score }} - {{ $match->away_score }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($match->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-semibold">{{ $match->awayTeam->name }}</td>
                                        <td>
                                            <small class="text-muted">{{ $match->competition->name ?? '-' }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- League Standings Summary -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-ranking-star me-2"></i>{{ __('app.league_standings') }}
                </h5>
                <a href="{{ route('standings.index') }}" class="btn btn-sm btn-outline-light">
                    {{ __('app.full_table') }}
                </a>
            </div>
            <div class="card-body p-0">
                @if($standings->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-table fa-3x mb-3 d-block"></i>
                        No standings data available yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>{{ __('app.team') }}</th>
                                    <th class="text-center">{{ __('app.played') }}</th>
                                    <th class="text-center">{{ __('app.won') }}</th>
                                    <th class="text-center">{{ __('app.drawn') }}</th>
                                    <th class="text-center">{{ __('app.lost') }}</th>
                                    <th class="text-center fw-bold">{{ __('app.points') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($standings as $index => $standing)
                                    <tr>
                                        <td>
                                            @if($index < 3)
                                                <span class="badge bg-success">{{ $index + 1 }}</span>
                                            @else
                                                <span class="text-muted">{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-semibold">{{ $standing->team->name }}</td>
                                        <td class="text-center">{{ $standing->played }}</td>
                                        <td class="text-center">{{ $standing->won }}</td>
                                        <td class="text-center">{{ $standing->drawn }}</td>
                                        <td class="text-center">{{ $standing->lost }}</td>
                                        <td class="text-center fw-bold">{{ $standing->points }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
