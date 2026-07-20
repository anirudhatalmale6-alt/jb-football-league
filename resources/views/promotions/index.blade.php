@extends('layouts.app')

@section('title', 'Promotions & Relegations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-exchange-alt text-primary me-2"></i>Promotions & Relegations
    </h2>
    <div class="d-flex gap-2">
        <a href="{{ route('teams.index', ['competition_id' => 2, 'status' => 'approved']) }}" class="btn btn-outline-danger">
            <i class="fas fa-list me-1"></i> Super League Teams
        </a>
        <a href="{{ route('teams.index', ['competition_id' => 4, 'status' => 'approved']) }}" class="btn btn-outline-primary">
            <i class="fas fa-list me-1"></i> Division League Teams
        </a>
    </div>
</div>

@if($offers->isEmpty())
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>No promotion or relegation records found.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Team</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Responded</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($offers as $offer)
                    <tr>
                        <td>{{ $offer->id }}</td>
                        <td>
                            @if($offer->type === 'relegation')
                                <span class="badge bg-danger"><i class="fas fa-arrow-down me-1"></i>Relegation</span>
                            @else
                                <span class="badge bg-success"><i class="fas fa-arrow-up me-1"></i>Promotion</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('teams.show', $offer->team_id) }}" class="fw-bold text-decoration-none">
                                {{ $offer->team->name ?? 'N/A' }}
                            </a>
                        </td>
                        <td><small>{{ $offer->fromCompetition->name ?? 'N/A' }}</small></td>
                        <td><small class="{{ $offer->type === 'relegation' ? 'text-danger' : 'text-success' }} fw-bold">{{ $offer->toCompetition->name ?? 'N/A' }}</small></td>
                        <td>
                            @switch($offer->status)
                                @case('pending')
                                    @if($offer->isExpired())
                                        <span class="badge bg-secondary">Expired</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                    @break
                                @case('accepted')
                                    <span class="badge bg-success">Accepted</span>
                                    @break
                                @case('completed')
                                    <span class="badge bg-info">Completed</span>
                                    @break
                                @case('declined')
                                    <span class="badge bg-danger">Declined</span>
                                    @break
                                @case('expired')
                                    <span class="badge bg-secondary">Expired</span>
                                    @break
                            @endswitch
                        </td>
                        <td><small>{{ $offer->offered_at->format('d M Y H:i') }}</small></td>
                        <td>
                            @if($offer->responded_at)
                                <small>{{ $offer->responded_at->format('d M Y H:i') }}</small>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('promotions.letter', $offer) }}" class="btn btn-sm btn-outline-primary" target="_blank" title="Download Letter">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            @if($offer->coaching_license)
                                <a href="{{ asset('storage/' . $offer->coaching_license) }}" class="btn btn-sm btn-outline-info" target="_blank" title="View License">
                                    <i class="fas fa-id-card"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $offers->links() }}
@endif
@endsection
