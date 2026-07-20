@extends('layouts.app')

@section('title', __('app.mc_history'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold mb-0"><i class="fas fa-history text-primary me-2"></i>{{ __('app.mc_history') }}</h2>
    <a href="{{ route('mc-assignment.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>{{ __('app.back') }}</a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <strong>{{ $match->homeTeam->name ?? 'Home' }} vs {{ $match->awayTeam->name ?? 'Away' }}</strong>
        <span class="text-muted ms-2">{{ $match->match_date ? $match->match_date->format('d M Y, g:i A') : '-' }}</span>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('app.date_and_time') }}</th>
                    <th>{{ __('app.mc_previous') }}</th>
                    <th>{{ __('app.mc_new') }}</th>
                    <th>{{ __('app.mc_changed_by') }}</th>
                    <th>{{ __('app.mc_reason') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($match->assignmentLogs as $log)
                    <tr>
                        <td class="small">{{ \App\Support\Tz::myt($log->created_at, 'd M Y, g:i A') }}</td>
                        <td>{{ optional($log->previousMc)->name ?? '—' }}</td>
                        <td class="fw-bold">{{ optional($log->newMc)->name ?? __('app.mc_unassigned') }}</td>
                        <td class="small">{{ optional($log->changedBy)->name ?? '-' }}</td>
                        <td class="small text-muted">{{ $log->reason ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">{{ __('app.mc_no_history') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
