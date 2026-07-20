@extends('layouts.app')
@section('title', __('app.match_events'))

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold mb-0"><i class="fas fa-futbol text-success me-2"></i>{{ __('app.match_events') }}</h2>
        <a href="{{ route('matches.show', $match) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>{{ __('app.back') }}</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <!-- Live Score Header -->
    <div class="card mb-4 {{ $match->isLive() ? 'border-success border-2' : '' }}">
        <div class="card-body text-center py-3">
            <div class="row align-items-center">
                <div class="col-4 text-end"><h4 class="fw-bold mb-0">{{ $match->homeTeam->name ?? '-' }}</h4></div>
                <div class="col-4 text-center">
                    <div class="bg-dark text-white rounded px-3 py-2 d-inline-block">
                        <h3 class="fw-bold mb-0">{{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}</h3>
                    </div>
                    @if($match->isLive())
                        <br><span class="badge bg-danger mt-1"><i class="fas fa-circle me-1" style="font-size:6px;"></i>LIVE {{ $match->match_minute }}</span>
                    @elseif($match->status === 'half_time')
                        <br><span class="badge bg-warning text-dark mt-1">HT</span>
                    @elseif($match->isFinished())
                        <br><span class="badge bg-secondary mt-1">FT</span>
                    @endif
                </div>
                <div class="col-4 text-start"><h4 class="fw-bold mb-0">{{ $match->awayTeam->name ?? '-' }}</h4></div>
            </div>
        </div>
    </div>

    <!-- Side-by-Side Event Forms -->
    @if($match->status !== 'closed')
    <div class="row mb-4">
        <!-- Home Team Panel -->
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="card h-100 border-start border-success border-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-home me-2"></i>{{ $match->homeTeam->name ?? 'Home' }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('matches.events.store', $match) }}" id="homeForm">
                        @csrf
                        <input type="hidden" name="team_id" value="{{ $match->home_team_id }}">

                        <!-- Quick Event Buttons -->
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="btn btn-sm btn-outline-success quick-event" data-type="goal" data-form="homeForm"><i class="fas fa-futbol me-1"></i>Goal</button>
                            <button type="button" class="btn btn-sm btn-outline-warning quick-event" data-type="yellow_card" data-form="homeForm"><span style="display:inline-block;width:10px;height:14px;background:#ffc107;border-radius:1px;margin-right:4px;"></span>Yellow</button>
                            <button type="button" class="btn btn-sm btn-outline-danger quick-event" data-type="red_card" data-form="homeForm"><span style="display:inline-block;width:10px;height:14px;background:#dc3545;border-radius:1px;margin-right:4px;"></span>Red</button>
                            <button type="button" class="btn btn-sm btn-outline-primary quick-event" data-type="substitution" data-form="homeForm"><i class="fas fa-exchange-alt me-1"></i>Sub</button>
                            <button type="button" class="btn btn-sm btn-outline-dark quick-event" data-type="penalty_scored" data-form="homeForm"><i class="fas fa-futbol me-1"></i>Penalty</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-event" data-type="own_goal" data-form="homeForm">OG</button>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">{{ __('app.event_type') }}</label>
                            <select name="event_type" class="form-select form-select-sm" id="homeEventType" required>
                                <option value="goal">Goal</option>
                                <option value="penalty_scored">Penalty Goal</option>
                                <option value="own_goal">Own Goal</option>
                                <option value="yellow_card">Yellow Card</option>
                                <option value="red_card">Red Card</option>
                                <option value="substitution">Substitution</option>
                                <option value="penalty_missed">Penalty Missed</option>
                                <option value="injury">Injury</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold">{{ __('app.player') }}</label>
                            <select name="player_id" class="form-select form-select-sm" required>
                                <option value="">-- Select Player --</option>
                                @foreach($homePlayers as $p)
                                    <option value="{{ $p->id }}">{{ $p->jersey_number }} - {{ $p->name }} ({{ ucfirst(substr($p->position,0,2)) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold">{{ __('app.minute') }}</label>
                                <input type="number" name="minute" class="form-control form-control-sm" min="1" max="120" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">+{{ __('app.extra_time') }}</label>
                                <input type="number" name="extra_time_minute" class="form-control form-control-sm" min="1">
                            </div>
                        </div>
                        <div class="mb-2 home-sub-field" style="display:none;">
                            <label class="form-label small fw-bold">{{ __('app.related_player') }}</label>
                            <select name="related_player_id" class="form-select form-select-sm">
                                <option value="">-- Player In --</option>
                                @foreach($homePlayers as $p)
                                    <option value="{{ $p->id }}">{{ $p->jersey_number }} - {{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="{{ __('app.notes') }}">
                        </div>
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-plus me-1"></i>{{ __('app.add_event') }}</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Away Team Panel -->
        <div class="col-md-6">
            <div class="card h-100 border-start border-primary border-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plane me-2"></i>{{ $match->awayTeam->name ?? 'Away' }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('matches.events.store', $match) }}" id="awayForm">
                        @csrf
                        <input type="hidden" name="team_id" value="{{ $match->away_team_id }}">

                        <!-- Quick Event Buttons -->
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="btn btn-sm btn-outline-success quick-event" data-type="goal" data-form="awayForm"><i class="fas fa-futbol me-1"></i>Goal</button>
                            <button type="button" class="btn btn-sm btn-outline-warning quick-event" data-type="yellow_card" data-form="awayForm"><span style="display:inline-block;width:10px;height:14px;background:#ffc107;border-radius:1px;margin-right:4px;"></span>Yellow</button>
                            <button type="button" class="btn btn-sm btn-outline-danger quick-event" data-type="red_card" data-form="awayForm"><span style="display:inline-block;width:10px;height:14px;background:#dc3545;border-radius:1px;margin-right:4px;"></span>Red</button>
                            <button type="button" class="btn btn-sm btn-outline-primary quick-event" data-type="substitution" data-form="awayForm"><i class="fas fa-exchange-alt me-1"></i>Sub</button>
                            <button type="button" class="btn btn-sm btn-outline-dark quick-event" data-type="penalty_scored" data-form="awayForm"><i class="fas fa-futbol me-1"></i>Penalty</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-event" data-type="own_goal" data-form="awayForm">OG</button>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">{{ __('app.event_type') }}</label>
                            <select name="event_type" class="form-select form-select-sm" id="awayEventType" required>
                                <option value="goal">Goal</option>
                                <option value="penalty_scored">Penalty Goal</option>
                                <option value="own_goal">Own Goal</option>
                                <option value="yellow_card">Yellow Card</option>
                                <option value="red_card">Red Card</option>
                                <option value="substitution">Substitution</option>
                                <option value="penalty_missed">Penalty Missed</option>
                                <option value="injury">Injury</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold">{{ __('app.player') }}</label>
                            <select name="player_id" class="form-select form-select-sm" required>
                                <option value="">-- Select Player --</option>
                                @foreach($awayPlayers as $p)
                                    <option value="{{ $p->id }}">{{ $p->jersey_number }} - {{ $p->name }} ({{ ucfirst(substr($p->position,0,2)) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold">{{ __('app.minute') }}</label>
                                <input type="number" name="minute" class="form-control form-control-sm" min="1" max="120" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">+{{ __('app.extra_time') }}</label>
                                <input type="number" name="extra_time_minute" class="form-control form-control-sm" min="1">
                            </div>
                        </div>
                        <div class="mb-2 away-sub-field" style="display:none;">
                            <label class="form-label small fw-bold">{{ __('app.related_player') }}</label>
                            <select name="related_player_id" class="form-select form-select-sm">
                                <option value="">-- Player In --</option>
                                @foreach($awayPlayers as $p)
                                    <option value="{{ $p->id }}">{{ $p->jersey_number }} - {{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="{{ __('app.notes') }}">
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i>{{ __('app.add_event') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Combined Match Timeline -->
    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
            <h5 class="mb-0"><i class="fas fa-stream me-2"></i>{{ __('app.match_events') }}</h5>
            <span class="badge bg-light text-dark">{{ $events->count() }} {{ __('app.events') }}</span>
        </div>
        <div class="card-body p-0">
            @forelse($events as $event)
                <div class="d-flex align-items-center p-3 border-bottom {{ $event->team_id === $match->home_team_id ? '' : 'bg-light' }}">
                    <div class="me-3 text-center" style="min-width:50px;">
                        <span class="badge bg-dark fs-6">{{ $event->minute }}'</span>
                        @if($event->extra_time_minute)
                            <small class="text-danger d-block">+{{ $event->extra_time_minute }}</small>
                        @endif
                    </div>
                    <div class="me-3">
                        @if(in_array($event->event_type, ['goal', 'penalty_scored']))
                            <i class="fas fa-futbol text-success fa-lg"></i>
                        @elseif($event->event_type === 'own_goal')
                            <i class="fas fa-futbol text-danger fa-lg"></i>
                        @elseif($event->event_type === 'yellow_card')
                            <span style="display:inline-block;width:16px;height:22px;background:#ffc107;border-radius:2px;"></span>
                        @elseif($event->event_type === 'red_card')
                            <span style="display:inline-block;width:16px;height:22px;background:#dc3545;border-radius:2px;"></span>
                        @elseif(str_contains($event->event_type, 'substitution'))
                            <i class="fas fa-exchange-alt text-primary fa-lg"></i>
                        @elseif($event->event_type === 'injury')
                            <i class="fas fa-medkit text-warning fa-lg"></i>
                        @elseif($event->event_type === 'penalty_missed')
                            <i class="fas fa-times-circle text-danger fa-lg"></i>
                        @else
                            <i class="fas fa-flag text-muted fa-lg"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <strong>{{ $event->player->name ?? '-' }}</strong>
                        <small class="text-muted">({{ $event->team->short_name ?? $event->team->name ?? '-' }})</small>
                        <br>
                        <small class="text-muted">
                            {{ str_replace('_', ' ', ucfirst($event->event_type)) }}
                            @if($event->related_player_id && $event->relatedPlayer)
                                &rarr; {{ $event->relatedPlayer->name }}
                            @endif
                            @if($event->notes) - {{ $event->notes }} @endif
                        </small>
                    </div>
                    @if($match->status !== 'closed')
                        <form method="POST" action="{{ route('matches.events.destroy', [$match->id, $event->id]) }}" onsubmit="return confirm('Delete this event?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-stream fa-2x mb-2 d-block"></i>
                    {{ __('app.no_events') }}
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.quick-event').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var formId = this.dataset.form;
            var eventType = this.dataset.type;
            var form = document.getElementById(formId);
            var select = form.querySelector('select[name=event_type]');
            select.value = eventType;
            select.dispatchEvent(new Event('change'));
            form.querySelector('select[name=player_id]').focus();
        });
    });

    document.getElementById('homeEventType').addEventListener('change', function() {
        document.querySelector('.home-sub-field').style.display = this.value === 'substitution' ? 'block' : 'none';
    });
    document.getElementById('awayEventType').addEventListener('change', function() {
        document.querySelector('.away-sub-field').style.display = this.value === 'substitution' ? 'block' : 'none';
    });
});
</script>
@endpush
@endsection
