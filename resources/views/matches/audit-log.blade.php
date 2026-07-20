@extends('layouts.app')

@section('title', __('app.match_audit_log'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-clock-rotate-left text-secondary me-2"></i>{{ __('app.match_audit_log') }}
    </h2>
    <a href="{{ route('matches.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> {{ __('app.back_to_matches') }}
    </a>
</div>

<p class="text-muted">{{ __('app.match_audit_log_intro') }}</p>

@if($logs->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-clock-rotate-left fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">{{ __('app.no_audit_records') }}</h5>
        </div>
    </div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.action') }}</th>
                        <th>{{ __('app.match') }}</th>
                        <th>{{ __('app.competition') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th>{{ __('app.reason') }}</th>
                        <th>{{ __('app.performed_by') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td><small>{{ $log->created_at->format('d M Y, g:i A') }}</small></td>
                            <td>
                                @if($log->action === 'deleted')
                                    <span class="badge bg-danger">{{ __('app.action_deleted') }}</span>
                                @elseif($log->action === 'archived')
                                    <span class="badge bg-primary">{{ __('app.action_archived') }}</span>
                                @elseif($log->action === 'restored')
                                    <span class="badge bg-success">{{ __('app.action_restored') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($log->action) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $log->home_team ?? '-' }} <span class="text-muted small">vs</span> {{ $log->away_team ?? '-' }}</div>
                                <small class="text-muted">{{ $log->match_code ?? '-' }}@if($log->match_date) &middot; {{ $log->match_date->format('d M Y, g:i A') }}@endif</small>
                            </td>
                            <td><small>{{ $log->competition ?? '-' }}</small></td>
                            <td><small>{{ $log->status_at_action ? ucfirst(str_replace('_', ' ', $log->status_at_action)) : '-' }}</small></td>
                            <td><small>{{ $log->reason ?? '-' }}</small></td>
                            <td><small>{{ $log->performed_by_name ?? (optional($log->performedBy)->name ?? '-') }}</small></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $logs->links() }}
    </div>
@endif
@endsection
