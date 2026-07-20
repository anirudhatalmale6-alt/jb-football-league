@extends('layouts.app')
@section('title', __('app.select_lineup'))

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('lineup-submissions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>{{ __('app.back') }}
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-futbol me-2"></i>{{ __('app.match_details') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>{{ __('app.competition') }}:</strong> {{ $match->competition->name ?? '-' }}
                </div>
                <div class="col-md-4">
                    <strong>{{ __('app.match') }}:</strong> {{ $match->homeTeam->name ?? '-' }} vs {{ $match->awayTeam->name ?? '-' }}
                </div>
                <div class="col-md-4">
                    <strong>{{ __('app.date') }}:</strong> {{ $match->match_date ? $match->match_date->format('d M Y H:i') : '-' }}
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <strong>{{ __('app.venue') }}:</strong> {{ $match->venue ?? '-' }}
                </div>
                <div class="col-md-4">
                    <strong>{{ __('app.team') }}:</strong> {{ $team->name }}
                </div>
                <div class="col-md-4">
                    <strong>{{ __('app.match_code_label') }}:</strong> {{ $match->match_code ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    @if($submission && $submission->isRejected())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>{{ __('app.lineup_rejected_reason') }}:</strong> {{ $submission->rejection_reason }}
            <br><small>{{ __('app.lineup_amend_resubmit') }}</small>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('lineup-submissions.store', [$match->id, $team->id]) }}" id="lineupForm">
        @csrf

        <div class="card mb-4">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i>{{ __('app.starting_eleven') }}</h5>
                <span class="badge bg-light text-dark" id="startingCount">{{ count($selectedStarting) }}/11</span>
                <span class="badge bg-success" id="u23Counter">U23: 0</span>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">{{ __('app.select_11_players') }}</p>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th style="width:50px"></th>
                                <th>#</th>
                                <th>{{ __('app.name') }}</th>
                                <th>{{ __('app.position') }}</th>
                                <th>{{ __('app.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($players as $player)
                                @php $isSuspended = in_array($player->id, $suspendedPlayerIds); @endphp
                                <tr class="{{ $isSuspended ? 'table-danger' : '' }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input starting-check"
                                               name="starting[]" value="{{ $player->id }}" data-u23="{{ $player->is_u23 ? '1' : '0' }}"
                                               {{ in_array($player->id, $selectedStarting) ? 'checked' : '' }}
                                               {{ $isSuspended ? 'disabled' : '' }}
                                               data-player-id="{{ $player->id }}">
                                    </td>
                                    <td>{{ $player->jersey_number }}</td>
                                    <td>{{ $player->name }} @if($player->is_u23)<span class="badge bg-warning text-dark" style="font-size:0.6rem;">U23</span>@endif</td>
                                    <td><span class="badge bg-info text-dark">{{ ucfirst($player->position) }}</span></td>
                                    <td>
                                        @if($isSuspended)
                                            <span class="badge bg-danger">{{ __('app.suspended') }}</span>
                                        @elseif($player->status === 'injured')
                                            <span class="badge bg-warning text-dark">{{ __('app.injured') }}</span>
                                        @else
                                            <span class="badge bg-success">{{ __('app.active') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>{{ __('app.substitutes') }}</h5>
                <span class="badge bg-dark" id="subCount">{{ count($selectedSubs) }}/9</span>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">{{ __('app.select_subs') }}</p>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th style="width:50px"></th>
                                <th>#</th>
                                <th>{{ __('app.name') }}</th>
                                <th>{{ __('app.position') }}</th>
                                <th>{{ __('app.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($players as $player)
                                @php $isSuspended = in_array($player->id, $suspendedPlayerIds); @endphp
                                <tr class="{{ $isSuspended ? 'table-danger' : '' }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input sub-check"
                                               name="substitutes[]" value="{{ $player->id }}"
                                               {{ in_array($player->id, $selectedSubs) ? 'checked' : '' }}
                                               {{ $isSuspended ? 'disabled' : '' }}
                                               data-player-id="{{ $player->id }}">
                                    </td>
                                    <td>{{ $player->jersey_number }}</td>
                                    <td>{{ $player->name }} @if($player->is_u23)<span class="badge bg-warning text-dark" style="font-size:0.6rem;">U23</span>@endif</td>
                                    <td><span class="badge bg-info text-dark">{{ ucfirst($player->position) }}</span></td>
                                    <td>
                                        @if($isSuspended)
                                            <span class="badge bg-danger">{{ __('app.suspended') }}</span>
                                        @elseif($player->status === 'injured')
                                            <span class="badge bg-warning text-dark">{{ __('app.injured') }}</span>
                                        @else
                                            <span class="badge bg-success">{{ __('app.active') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success btn-lg" id="saveBtn">
                <i class="fas fa-save me-2"></i>{{ __('app.save_lineup') }}
            </button>
            <a href="{{ route('lineup-submissions.index') }}" class="btn btn-outline-secondary btn-lg">{{ __('app.cancel') }}</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startingChecks = document.querySelectorAll('.starting-check');
    const subChecks = document.querySelectorAll('.sub-check');
    const startingCount = document.getElementById('startingCount');
    const subCount = document.getElementById('subCount');
    const saveBtn = document.getElementById('saveBtn');

    function updateCounts() {
        const sChecked = document.querySelectorAll('.starting-check:checked').length;
        const subChecked = document.querySelectorAll('.sub-check:checked').length;
        startingCount.textContent = sChecked + '/11';
        subCount.textContent = subChecked + '/9';

        startingCount.className = 'badge ' + (sChecked === 11 ? 'bg-light text-dark' : 'bg-danger');
        saveBtn.disabled = sChecked !== 11;
    }

    function syncCheckboxes(source, target) {
        const playerId = source.dataset.playerId;
        if (source.checked) {
            const other = target.querySelector('[data-player-id="' + playerId + '"]');
            if (other) other.checked = false;
        }
        updateCounts();
    }

    startingChecks.forEach(cb => {
        cb.addEventListener('change', function() {
            const checked = document.querySelectorAll('.starting-check:checked').length;
            if (checked > 11) { this.checked = false; return; }
            syncCheckboxes(this, document.querySelector('.sub-check[data-player-id="' + this.dataset.playerId + '"]') ? document.querySelector('.card-body') : null);
            const subCb = document.querySelector('.sub-check[data-player-id="' + this.dataset.playerId + '"]');
            if (this.checked && subCb) subCb.checked = false;
            updateCounts();
        });
    });

    subChecks.forEach(cb => {
        cb.addEventListener('change', function() {
            const checked = document.querySelectorAll('.sub-check:checked').length;
            if (checked > 9) { this.checked = false; return; }
            const startCb = document.querySelector('.starting-check[data-player-id="' + this.dataset.playerId + '"]');
            if (this.checked && startCb) startCb.checked = false;
            updateCounts();
        });
    });

    updateCounts();
});

    // U23 enforcement: at least 1 U23 player in Starting 11
    document.getElementById('lineupForm').addEventListener('submit', function(e) {
        var startingChecked = document.querySelectorAll('.starting-check:checked');
        var hasU23 = false;
        startingChecked.forEach(function(cb) {
            if (cb.dataset.u23 === '1') hasU23 = true;
        });
        if (startingChecked.length === 11 && !hasU23) {
            e.preventDefault();
            alert('Your Starting 11 must include at least 1 U23 player. Please select at least one U23 player before submitting the line-up.');
            return false;
        }
    });

    // Show U23 count in real-time
    function updateU23Count() {
        var startingChecked = document.querySelectorAll('.starting-check:checked');
        var u23Count = 0;
        startingChecked.forEach(function(cb) {
            if (cb.dataset.u23 === '1') u23Count++;
        });
        var badge = document.getElementById('u23Counter');
        if (badge) {
            badge.textContent = 'U23: ' + u23Count;
            badge.className = 'badge ms-2 ' + (u23Count >= 1 ? 'bg-success' : 'bg-danger');
        }
    }

    document.querySelectorAll('.starting-check').forEach(function(cb) {
        cb.addEventListener('change', updateU23Count);
    });
    updateU23Count();

</script>
@endpush
@endsection
