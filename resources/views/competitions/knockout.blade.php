{{-- Knockout Stage Tab Content --}}

@if(!$bracketInitialized)
    <div class="text-center py-5">
        <i class="fas fa-sitemap fa-3x mb-3 text-muted d-block"></i>
        <p class="text-muted mb-3">{{ __('app.no_bracket_initialized') }}</p>
        @auth
            @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                <form method="POST" action="{{ route('knockout.init', $competition) }}">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Initialize 16-team knockout bracket?')">
                        <i class="fas fa-plus me-1"></i> {{ __('app.init_bracket') }}
                    </button>
                </form>
            @endif
        @endauth
    </div>
@else

    {{-- Champion Display --}}
    @if($champion)
        <div class="alert alert-warning text-center py-3 mb-3">
            <i class="fas fa-trophy fa-2x text-warning mb-2 d-block"></i>
            <h4 class="fw-bold mb-0">{{ $champion->name }}</h4>
            <div class="text-muted">{{ __('app.champion') }}</div>
        </div>
    @endif

    {{-- Admin Reset --}}
    @auth
        @if(auth()->user()->isSuper())
            <div class="text-end mb-2">
                <form method="POST" action="{{ route('knockout.reset', $competition) }}" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Reset entire bracket? This cannot be undone.')">
                        <i class="fas fa-redo me-1"></i> {{ __('app.reset_bracket') }}
                    </button>
                </form>
            </div>
        @endif
    @endauth

    {{-- Bracket Display --}}
    <div class="bracket-container">
        <div class="bracket-scroll">
            @php
                $roundConfigs = [
                    'round_of_16' => ['label' => __('app.round_of_16'), 'count' => 8],
                    'quarter_final' => ['label' => __('app.quarter_final'), 'count' => 4],
                    'semi_final' => ['label' => __('app.semi_final'), 'count' => 2],
                    'final' => ['label' => __('app.final'), 'count' => 1],
                ];
            @endphp

            @foreach($roundConfigs as $roundKey => $config)
                <div class="bracket-round">
                    <div class="bracket-round-title">
                        <span class="badge bg-dark px-3 py-2">{{ $config['label'] }}</span>
                    </div>
                    @for($i = 1; $i <= $config['count']; $i++)
                        @php
                            $km = $bracket[$roundKey]->firstWhere('position', $i);
                            $isAdmin = auth()->check() && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin());
                        @endphp
                        <div class="bracket-match {{ $km && $km->winner_team_id ? 'completed' : '' }}">
                            <div class="bracket-match-num">M{{ $i }}</div>
                            {{-- Home Team --}}
                            <div class="bracket-team {{ $km && $km->winner_team_id == $km->home_team_id ? 'winner' : '' }}">
                                <span class="team-name">
                                    @if($km && $km->homeTeam)
                                        {{ $km->homeTeam->short_name ?? $km->homeTeam->name }}
                                    @else
                                        <span class="text-muted">---</span>
                                    @endif
                                </span>
                                <span class="team-score">
                                    @if($km && $km->matchGame && $km->matchGame->isFinished())
                                        {{ $km->matchGame->home_score ?? '-' }}
                                        @if($km->home_penalty_score !== null)
                                            <small>({{ $km->home_penalty_score }})</small>
                                        @endif
                                    @endif
                                </span>
                            </div>
                            {{-- Away Team --}}
                            <div class="bracket-team {{ $km && $km->winner_team_id == $km->away_team_id ? 'winner' : '' }}">
                                <span class="team-name">
                                    @if($km && $km->awayTeam)
                                        {{ $km->awayTeam->short_name ?? $km->awayTeam->name }}
                                    @else
                                        <span class="text-muted">---</span>
                                    @endif
                                </span>
                                <span class="team-score">
                                    @if($km && $km->matchGame && $km->matchGame->isFinished())
                                        {{ $km->matchGame->away_score ?? '-' }}
                                        @if($km->away_penalty_score !== null)
                                            <small>({{ $km->away_penalty_score }})</small>
                                        @endif
                                    @endif
                                </span>
                            </div>
                            {{-- Admin Controls --}}
                            @if($isAdmin && $km)
                                <div class="bracket-admin mt-1">
                                    @if(!$km->winner_team_id && $km->home_team_id && $km->away_team_id)
                                        <button class="btn btn-outline-success btn-sm py-0 px-1" style="font-size:10px;" onclick="showWinnerModal({{ $km->id }}, '{{ $km->homeTeam->name }}', {{ $km->home_team_id }}, '{{ $km->awayTeam->name }}', {{ $km->away_team_id }})">
                                            <i class="fas fa-check"></i> Set Winner
                                        </button>
                                    @elseif(!$km->home_team_id || !$km->away_team_id)
                                        <button class="btn btn-outline-primary btn-sm py-0 px-1" style="font-size:10px;" onclick="showSeedModal({{ $km->id }}, '{{ $roundKey }}', {{ $i }}, {{ $km->home_team_id ? 'true' : 'false' }}, {{ $km->away_team_id ? 'true' : 'false' }})">
                                            <i class="fas fa-user-plus"></i> Seed
                                        </button>
                                    @endif
                                    @if($km->matchGame)
                                        <a href="{{ route('matches.show', $km->matchGame) }}" class="btn btn-outline-dark btn-sm py-0 px-1" style="font-size:10px;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endfor
                </div>
            @endforeach

            {{-- Champion Column --}}
            <div class="bracket-round">
                <div class="bracket-round-title">
                    <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-trophy me-1"></i> {{ __('app.champion') }}</span>
                </div>
                <div class="bracket-match champion-slot">
                    <div class="bracket-team winner">
                        <span class="team-name fw-bold">
                            @if($champion)
                                {{ $champion->name }}
                            @else
                                <span class="text-muted">?</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Seed Team Modal --}}
    @auth
    @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
    <div class="modal fade" id="seedModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2 bg-primary text-white">
                    <h6 class="modal-title"><i class="fas fa-user-plus me-2"></i>{{ __('app.seed_team') }}</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('knockout.seed', $competition) }}">
                    @csrf
                    <input type="hidden" name="knockout_match_id" id="seed_km_id">
                    <div class="modal-body">
                        <div class="mb-2" id="seed_side_group">
                            <label class="form-label small fw-bold">{{ __('app.side') }}</label>
                            <select name="side" id="seed_side" class="form-select form-select-sm">
                                <option value="home">{{ __('app.home') }}</option>
                                <option value="away">{{ __('app.away') }}</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold">{{ __('app.team') }}</label>
                            <select name="team_id" class="form-select form-select-sm" required>
                                <option value="">-- {{ __('app.select_team') }} --</option>
                                @foreach($competition->teams->where('status', 'approved')->sortBy('name') as $team)
                                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer py-1">
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('app.seed_team') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Set Winner Modal --}}
    <div class="modal fade" id="winnerModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2 bg-success text-white">
                    <h6 class="modal-title"><i class="fas fa-trophy me-2"></i>{{ __('app.set_winner') }}</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="winnerForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label small fw-bold">{{ __('app.winner') }}</label>
                            <select name="winner_team_id" class="form-select form-select-sm" required id="winner_select">
                            </select>
                        </div>
                        <hr class="my-2">
                        <p class="small text-muted mb-1">{{ __('app.penalty_optional') }}</p>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small" id="pen_home_label">Home Pen</label>
                                <input type="number" name="home_penalty_score" class="form-control form-control-sm" min="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label small" id="pen_away_label">Away Pen</label>
                                <input type="number" name="away_penalty_score" class="form-control form-control-sm" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-1">
                        <button type="submit" class="btn btn-success btn-sm">{{ __('app.confirm_winner') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endauth

@endif
