@php
    $viewer = auth()->user();
    $canOp = $viewer && $match->canOperateBy($viewer);

    $subs = $match->lineupSubmissions;
    $homeSub = $subs->firstWhere('team_id', $match->home_team_id);
    $awaySub = $subs->firstWhere('team_id', $match->away_team_id);

    // Given a submission, return [statusLabel, badgeColour, isApproved, actionLabel|null].
    // actionLabel null means "Pending Submission" (nothing to review yet).
    $lineupState = function ($s) {
        $status = $s->status ?? null;
        return match ($status) {
            'approved', 'locked' => ['Approved', 'success', true, 'View Approved Line-Up'],
            'submitted' => ['Submitted', 'warning text-dark', false, 'Review Line-Up'],
            'rejected' => ['Rejected', 'danger', false, 'Review Line-Up'],
            'draft' => ['Not Submitted', 'secondary', false, null],
            default => ['Not Submitted', 'secondary', false, null],
        };
    };

    [$homeStatus, $homeColour, $homeOk, $homeAction] = $lineupState($homeSub);
    [$awayStatus, $awayColour, $awayOk, $awayAction] = $lineupState($awaySub);

    $homeJerseyOk = $homeJersey && $homeJersey->status === 'confirmed';
    $awayJerseyOk = $awayJersey && $awayJersey->status === 'confirmed';
    $jerseyOk = $homeJerseyOk && $awayJerseyOk;

    $photoCount = $match->matchDayPhotos->count();
    $photoOk = $photoCount >= 3;

    $reviewUrl = route('lineup-submissions.review', $match->id);
    $jerseyUrl = route('matches.show', $match->id) . '#jersey-section';
    $photoUrl = route('match-photos.index', $match->id);

    $allReady = $homeOk && $awayOk && $jerseyOk;
@endphp

<div class="card mb-3 border-{{ $allReady ? 'success' : 'warning' }}">
    <div class="card-header bg-{{ $allReady ? 'success' : 'warning' }} {{ $allReady ? 'text-white' : 'text-dark' }} py-2 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#prematchPanel" role="button">
        <h6 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Pre-Match Checklist</h6>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="collapse show" id="prematchPanel" @if($allReady) data-autocollapse="1" @endif>
        <div class="card-body py-2">
            <ul class="list-group list-group-flush">
                {{-- Home Team Line-Up --}}
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 flex-wrap gap-2">
                    <span>
                        <i class="fas fa-{{ $homeOk ? 'check-circle text-success' : 'hourglass-half text-warning' }} me-2"></i>
                        Home Team Line-Up ({{ $match->homeTeam->name ?? 'Home' }}):
                        <span class="badge bg-{{ $homeColour }} ms-1">{{ $homeStatus }}</span>
                    </span>
                    @if($canOp)
                        @if($homeAction)
                            <a href="{{ $reviewUrl }}" class="btn btn-sm {{ $homeOk ? 'btn-outline-success' : 'btn-primary' }}">
                                <i class="fas fa-clipboard-check me-1"></i>{{ $homeAction }}
                            </a>
                        @else
                            <span class="badge bg-light text-dark border"><i class="fas fa-clock me-1"></i>Pending Submission</span>
                        @endif
                    @endif
                </li>

                {{-- Away Team Line-Up --}}
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 flex-wrap gap-2">
                    <span>
                        <i class="fas fa-{{ $awayOk ? 'check-circle text-success' : 'hourglass-half text-warning' }} me-2"></i>
                        Away Team Line-Up ({{ $match->awayTeam->name ?? 'Away' }}):
                        <span class="badge bg-{{ $awayColour }} ms-1">{{ $awayStatus }}</span>
                    </span>
                    @if($canOp)
                        @if($awayAction)
                            <a href="{{ $reviewUrl }}" class="btn btn-sm {{ $awayOk ? 'btn-outline-success' : 'btn-primary' }}">
                                <i class="fas fa-clipboard-check me-1"></i>{{ $awayAction }}
                            </a>
                        @else
                            <span class="badge bg-light text-dark border"><i class="fas fa-clock me-1"></i>Pending Submission</span>
                        @endif
                    @endif
                </li>

                {{-- Jersey Colours --}}
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 flex-wrap gap-2">
                    <span>
                        <i class="fas fa-{{ $jerseyOk ? 'check-circle text-success' : 'times-circle text-muted' }} me-2"></i>
                        Jersey Colours:
                        <span class="badge bg-{{ $jerseyOk ? 'success' : 'secondary' }} ms-1">{{ $jerseyOk ? 'Confirmed' : 'Not Confirmed' }}</span>
                    </span>
                    @if($canOp)
                        <a href="{{ $jerseyUrl }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-tshirt me-1"></i>Check Jersey Colours
                        </a>
                    @endif
                </li>

                {{-- Match Day Photos --}}
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 flex-wrap gap-2">
                    <span>
                        <i class="fas fa-{{ $photoOk ? 'check-circle text-success' : 'camera text-muted' }} me-2"></i>
                        Match Day Photos:
                        <span class="badge bg-{{ $photoOk ? 'success' : 'secondary' }} ms-1">{{ $photoCount }}/3 Uploaded</span>
                    </span>
                    @if($canOp)
                        <a href="{{ $photoUrl }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-upload me-1"></i>Upload Photos
                        </a>
                    @endif
                </li>
            </ul>
            <div class="mt-2">
                @if($allReady)
                    <span class="badge bg-success py-2 px-3"><i class="fas fa-flag-checkered me-1"></i>Match Ready to Start</span>
                @else
                    <span class="badge bg-warning text-dark py-2 px-3"><i class="fas fa-hourglass-half me-1"></i>Verification Incomplete</span>
                @endif
            </div>
        </div>
    </div>
</div>
