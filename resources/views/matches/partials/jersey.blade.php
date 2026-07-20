@php
    $user = auth()->user();
    $isJerseyAdmin = $user && ($user->isSuper() || $user->isLeagueAdmin());
    $managesHome = $user && $user->isTeamManager() && $user->managesTeam($match->home_team_id);
    $managesAway = $user && $user->isTeamManager() && $user->managesTeam($match->away_team_id);
    $canViewJersey = $isJerseyAdmin || $managesHome || $managesAway;
    $jerseyDeadline = $match->match_date ? $match->match_date->copy()->subDays(3) : null;
@endphp

@if($canViewJersey)
<div class="card mb-3">
    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#jerseyPanel" role="button">
        <h6 class="mb-0"><i class="fas fa-tshirt me-2"></i>Team Jersey Colours</h6>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="collapse show" id="jerseyPanel">
        <div class="card-body">

            @if($jerseyDeadline)
                <div class="alert {{ now()->gt($jerseyDeadline) ? 'alert-secondary' : 'alert-info' }} py-2 small">
                    <i class="fas fa-clock me-1"></i>
                    <strong>Jersey Submission Deadline:</strong> {{ $jerseyDeadline->format('d M Y, h:i A') }}
                    (3 days before kick-off){{ now()->gt($jerseyDeadline) ? ' — passed' : '' }}
                </div>
            @endif

            @if(!empty($jerseyClashes))
                <div class="alert alert-warning py-2">
                    <strong><i class="fas fa-exclamation-triangle me-1"></i>Possible Jersey Colour Clash</strong>
                    <ul class="mb-0 mt-1 small">
                        @foreach($jerseyClashes as $clash)
                            <li>{{ $clash }}</li>
                        @endforeach
                    </ul>
                    <div class="small mt-1 mb-0 text-muted">Please review and request one team to use an alternative kit.</div>
                </div>
            @endif

            <div class="row g-3">
                @foreach([['team' => $match->homeTeam, 'jersey' => $homeJersey, 'label' => 'Home Team', 'manages' => $managesHome, 'teamId' => $match->home_team_id], ['team' => $match->awayTeam, 'jersey' => $awayJersey, 'label' => 'Away Team', 'manages' => $managesAway, 'teamId' => $match->away_team_id]] as $side)
                    @php $j = $side['jersey']; @endphp
                    <div class="col-md-6">
                        <div class="border rounded h-100">
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-light">
                                <div>
                                    <span class="badge bg-secondary">{{ $side['label'] }}</span>
                                    <span class="fw-bold ms-1">{{ $side['team']->name ?? '-' }}</span>
                                </div>
                                @if($j)
                                    <span class="badge bg-{{ $j->statusColour() }}">{{ $j->statusLabel() }}</span>
                                @else
                                    <span class="badge bg-secondary">Not Submitted</span>
                                @endif
                            </div>
                            <div class="p-3">
                                @if($j && !$j->isDraft())
                                    <div class="row g-3 align-items-start">
                                        {{-- LEFT: colour details --}}
                                        <div class="col-7">
                                            <div class="small text-muted mb-2">Kit: <strong>{{ ucfirst($j->kit_type) }}</strong></div>
                                            <div class="fw-semibold small text-uppercase text-muted mb-1">Player</div>
                                            @include('matches.partials.jersey-swatch', ['label' => 'Shirt', 'name' => $j->shirt_name, 'hex' => $j->shirt_hex])
                                            @include('matches.partials.jersey-swatch', ['label' => 'Shorts', 'name' => $j->shorts_name, 'hex' => $j->shorts_hex])
                                            @include('matches.partials.jersey-swatch', ['label' => 'Socks', 'name' => $j->socks_name, 'hex' => $j->socks_hex])
                                            <div class="fw-semibold small text-uppercase text-muted mb-1 mt-3">Goalkeeper</div>
                                            @include('matches.partials.jersey-swatch', ['label' => 'Shirt', 'name' => $j->gk_shirt_name, 'hex' => $j->gk_shirt_hex])
                                            @include('matches.partials.jersey-swatch', ['label' => 'Shorts', 'name' => $j->gk_shorts_name, 'hex' => $j->gk_shorts_hex])
                                            @include('matches.partials.jersey-swatch', ['label' => 'Socks', 'name' => $j->gk_socks_name, 'hex' => $j->gk_socks_hex])
                                        </div>
                                        {{-- RIGHT: generated visual kit preview --}}
                                        <div class="col-5 text-center border-start">
                                            @include('matches.partials.kit-svg', ['shirt' => $j->shirt_hex, 'shorts' => $j->shorts_hex, 'socks' => $j->socks_hex, 'w' => 100, 'caption' => 'Player'])
                                            <div class="mt-2 pt-2 border-top">
                                                @include('matches.partials.kit-svg', ['shirt' => $j->gk_shirt_hex, 'shorts' => $j->gk_shorts_hex, 'socks' => $j->gk_socks_hex, 'w' => 56, 'caption' => 'Goalkeeper'])
                                            </div>
                                        </div>
                                    </div>
                                    @if($j->photo)
                                        <div class="mt-3">
                                            <a href="{{ asset('storage/' . $j->photo) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $j->photo) }}" alt="Jersey photo" style="max-height:120px;border-radius:6px;border:1px solid #ddd;">
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <p class="text-muted small mb-2"><i class="fas fa-info-circle me-1"></i>Jersey colours not submitted yet.</p>
                                @endif

                                {{-- Team manager: submit / edit own team --}}
                                @if($side['manages'] && (!$j || $j->canEdit()))
                                    <a href="{{ route('jerseys.edit', [$match->id, $side['teamId']]) }}" class="btn btn-sm btn-success mt-2">
                                        <i class="fas fa-tshirt me-1"></i>{{ $j && !$j->isDraft() ? 'Update Jersey Colours' : 'Submit Jersey Colours' }}
                                    </a>
                                @endif

                                {{-- Admin / Commissioner controls --}}
                                @if($isJerseyAdmin && $j && !$j->isDraft())
                                    <div class="d-flex gap-2 mt-3 flex-wrap">
                                        @if(!$j->isConfirmed())
                                            <form action="{{ route('jerseys.confirm', [$match->id, $side['teamId']]) }}" method="POST">
                                                @csrf
                                                <button class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Confirm</button>
                                            </form>
                                        @endif
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="collapse" data-bs-target="#amendForm{{ $side['teamId'] }}">
                                            <i class="fas fa-edit me-1"></i>Request Amendment
                                        </button>
                                        @if($j->confirmed_at)
                                            <span class="small text-success align-self-center"><i class="fas fa-check-circle me-1"></i>Confirmed {{ $j->confirmed_at->format('d M, h:i A') }}</span>
                                        @endif
                                    </div>
                                    <div class="collapse mt-2" id="amendForm{{ $side['teamId'] }}">
                                        <form action="{{ route('jerseys.request-amendment', [$match->id, $side['teamId']]) }}" method="POST">
                                            @csrf
                                            <textarea name="amendment_note" class="form-control form-control-sm mb-2" rows="2" placeholder="Reason for amendment (e.g. colour clash, use alternative kit)" required></textarea>
                                            <button class="btn btn-sm btn-warning"><i class="fas fa-paper-plane me-1"></i>Send Amendment Request</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
