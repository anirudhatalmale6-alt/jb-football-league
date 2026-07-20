@extends('layouts.app')

@section('title', __('app.mc_assignment'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h2 class="fw-bold mb-0"><i class="fas fa-user-tag text-primary me-2"></i>{{ __('app.mc_assignment') }}</h2>
    <a href="{{ route('head-mc.dashboard') }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-clipboard-check me-1"></i>{{ __('app.head_mc_dashboard') }}
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

@if($commissioners->isEmpty())
    <div class="alert alert-warning py-2">
        <i class="fas fa-exclamation-triangle me-1"></i>{{ __('app.mc_none_available') }}
    </div>
@endif

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1">{{ __('app.competition') }}</label>
                <select name="competition_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($competitions as $c)
                        <option value="{{ $c->id }}" {{ (string) request('competition_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('app.mc_filter') }}</label>
                <select name="assigned" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('app.all') }}</option>
                    <option value="no" {{ request('assigned') === 'no' ? 'selected' : '' }}>{{ __('app.mc_unassigned') }}</option>
                    <option value="yes" {{ request('assigned') === 'yes' ? 'selected' : '' }}>{{ __('app.mc_assigned') }}</option>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('app.match_label') }}</th>
                    <th class="d-none d-md-table-cell">{{ __('app.date_and_time') }}</th>
                    <th class="d-none d-lg-table-cell">{{ __('app.venue') }}</th>
                    <th>{{ __('app.mc_assigned_to') }}</th>
                    <th style="min-width:260px;">{{ __('app.mc_assign_action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($matches as $m)
                    <tr>
                        <td>
                            <a href="{{ route('matches.show', $m) }}" class="fw-bold text-decoration-none">
                                {{ $m->homeTeam->name ?? 'Home' }} <span class="text-muted">vs</span> {{ $m->awayTeam->name ?? 'Away' }}
                            </a>
                            <div class="small text-muted">{{ $m->competition->name ?? '-' }} @if($m->matchday) &bull; {{ __('app.matchday') }} {{ $m->matchday }} @endif</div>
                            <div class="d-md-none small text-muted"><i class="fas fa-calendar me-1"></i>{{ $m->match_date ? $m->match_date->format('d M Y, g:i A') : '-' }}</div>
                        </td>
                        <td class="d-none d-md-table-cell small">{{ $m->match_date ? $m->match_date->format('d M Y, g:i A') : '-' }}</td>
                        <td class="d-none d-lg-table-cell small">{{ $m->venue ?? '-' }}</td>
                        <td>
                            @if($m->assignedMc)
                                <span class="badge bg-success"><i class="fas fa-user-check me-1"></i>{{ $m->assignedMc->name }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('app.mc_unassigned') }}</span>
                            @endif
                            @if($m->assignmentLogs()->exists())
                                <a href="{{ route('mc-assignment.history', $m) }}" class="small ms-1" title="{{ __('app.mc_history') }}"><i class="fas fa-history"></i></a>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('mc-assignment.assign', $m) }}" class="d-flex flex-column gap-1">
                                @csrf
                                <div class="d-flex gap-1">
                                    <select name="mc_user_id" class="form-select form-select-sm">
                                        <option value="">{{ __('app.mc_unassign') }}</option>
                                        @foreach($commissioners as $mc)
                                            <option value="{{ $mc->id }}" {{ (int) $m->assigned_mc_user_id === (int) $mc->id ? 'selected' : '' }}>{{ $mc->name }}{{ $mc->isHeadMatchCommissioner() ? ' ('.__('app.role_head_mc').')' : '' }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary text-nowrap"><i class="fas fa-save me-1"></i>{{ __('app.mc_save') }}</button>
                                </div>
                                @if($m->assigned_mc_user_id)
                                    <input type="text" name="reason" class="form-control form-control-sm" placeholder="{{ __('app.mc_reason_placeholder') }}" style="font-size:11px;">
                                @endif
                            </form>
                            @if($viewer && $viewer->isHeadMatchCommissioner() && (int) $m->assigned_mc_user_id !== (int) $viewer->id)
                                <form method="POST" action="{{ route('mc-assignment.assign', $m) }}" class="mt-1">
                                    @csrf
                                    <input type="hidden" name="mc_user_id" value="{{ $viewer->id }}">
                                    <input type="hidden" name="reason" value="{{ __('app.mc_self_assign_reason') }}">
                                    <button type="submit" class="btn btn-sm btn-outline-success w-100 text-nowrap"><i class="fas fa-user-plus me-1"></i>{{ __('app.mc_assign_myself') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">{{ __('app.mc_no_matches') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $matches->links() }}</div>
@endsection
