@extends('layouts.app')

@section('title', $player->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-user text-success me-2"></i>{{ $player->name }}
        @if($player->is_u23)<span class="badge bg-warning text-dark" style="font-size:0.6rem;vertical-align:middle;">U23</span>@endif
    </h2>
    <div class="d-flex gap-2">
        @auth
            @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin() || (auth()->user()->isTeamManager() && auth()->user()->managesTeam($player->team_id)))
                <a href="{{ route('players.edit', $player) }}" class="btn btn-outline-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            @endif
        @endauth
        <a href="{{ route('players.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

@if(($isAdmin ?? false) && !empty($flags))
<div class="alert alert-warning d-flex align-items-start mb-4">
    <i class="fas fa-flag me-3 mt-1 fa-lg text-warning"></i>
    <div>
        <strong>Flagged for Review</strong>
        <ul class="mb-0 mt-1">
            @foreach($flags as $flag)
                <li>{{ $flag }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-2 text-center mb-3">
                @if($player->photo)
                    <img src="{{ asset('storage/' . $player->photo) }}" alt="{{ $player->name }}" class="img-fluid rounded shadow-sm" style="max-height: 160px; width: auto;">
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height:160px; width:130px; margin:0 auto;">
                        <i class="fas fa-user fa-3x text-muted"></i>
                    </div>
                @endif
            </div>
            <div class="col-md-5">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width: 150px;">Full Name</th>
                        <td class="fw-semibold">{{ $player->name }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Team</th>
                        <td>
                            @if($player->team)
                                <a href="{{ route('teams.show', $player->team) }}">{{ $player->team->name }}</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Jersey Number</th>
                        <td>
                            @if($player->jersey_number)
                                <span class="badge bg-dark fs-6">#{{ $player->jersey_number }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Position</th>
                        <td>{{ ucfirst($player->position ?? '-') }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-5">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width: 150px;">Date of Birth</th>
                        <td>{{ $player->date_of_birth ? $player->date_of_birth->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Age</th>
                        <td>{{ $player->age ?? '-' }} @if($player->is_u23)<span class="badge bg-warning text-dark" style="font-size:0.65rem;">U23</span>@endif</td>
                    </tr>
@if($canViewIc ?? false)
                    <tr>
                        <th class="text-muted">IC Number</th>
                        <td>
                            {{ $player->ic_number ?? '-' }}
                            @auth
                                @if($player->ic_photo && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin()))
                                    <a href="#" class="text-primary ms-2" data-bs-toggle="modal" data-bs-target="#icModal" style="font-size:0.85rem;">
                                        <i class="fas fa-id-card me-1"></i>View IC Image
                                    </a>
                                @endif
                            @endauth
                        </td>
                    </tr>
@endif
                    <tr>
                        <th class="text-muted">Status</th>
                        <td>
                            @if($player->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($player->status === 'suspended')
                                <span class="badge bg-danger">Suspended</span>
                            @elseif($player->status === 'injured')
                                <span class="badge bg-warning text-dark">Injured</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($player->status ?? 'unknown') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Verification</th>
                        <td>
                            @if($player->verification_status === 'verified')
                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Verified</span>
                            @elseif($player->verification_status === 'flagged')
                                <span class="badge bg-warning text-dark"><i class="fas fa-flag me-1"></i>Flagged for Review</span>
                            @elseif($player->verification_status === 'rejected')
                                <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Rejected</span>
                            @else
                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Verified</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

@auth
@if($player->ic_photo && (auth()->user()->isSuper() || auth()->user()->isLeagueAdmin()))
<div class="modal fade" id="icModal" tabindex="-1" aria-labelledby="icModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="icModalLabel">
                    <i class="fas fa-id-card me-2"></i>IC Verification - {{ $player->name }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th class="text-muted">Name:</th><td class="fw-semibold">{{ $player->name }}</td></tr>
                            @if($canViewIc ?? false)<tr><th class="text-muted">IC Number:</th><td class="fw-semibold">{{ $player->ic_number }}</td></tr>@endif
                            <tr><th class="text-muted">Date of Birth:</th><td>{{ $player->date_of_birth ? $player->date_of_birth->format('d M Y') : '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th class="text-muted">Team:</th><td>{{ $player->team->name ?? '-' }}</td></tr>
                            <tr><th class="text-muted">Age:</th><td>{{ $player->age ?? '-' }}</td></tr>
                            <tr><th class="text-muted">Verification:</th><td>
                                @if($player->verification_status === 'verified')<span class="badge bg-success">Verified</span>
                                @elseif($player->verification_status === 'flagged')<span class="badge bg-warning text-dark">Flagged</span>
                                @elseif($player->verification_status === 'rejected')<span class="badge bg-danger">Rejected</span>
                                @else <span class="badge bg-success">Verified</span>
                                @endif
                            </td></tr>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="text-center" style="overflow: auto; max-height: 500px;">
                    <img src="{{ asset('storage/' . $player->ic_photo) }}" alt="IC Image" id="icImage"
                         class="img-fluid rounded shadow" style="cursor: zoom-in; transition: transform 0.3s;">
                </div>
                <div class="text-center mt-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="zoomIc('in')"><i class="fas fa-search-plus"></i> Zoom In</button>
                    <button class="btn btn-sm btn-outline-primary" onclick="zoomIc('out')"><i class="fas fa-search-minus"></i> Zoom Out</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="zoomIc('reset')"><i class="fas fa-undo"></i> Reset</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
@endauth
@endsection

@push('scripts')
<script>
var icZoom = 1;
function zoomIc(action) {
    var img = document.getElementById('icImage');
    if (!img) return;
    if (action === 'in') icZoom = Math.min(icZoom + 0.3, 3);
    else if (action === 'out') icZoom = Math.max(icZoom - 0.3, 0.5);
    else icZoom = 1;
    img.style.transform = 'scale(' + icZoom + ')';
    img.style.cursor = icZoom > 1 ? 'zoom-out' : 'zoom-in';
}
</script>
@endpush