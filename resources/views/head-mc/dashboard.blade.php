@extends('layouts.app')

@section('title', __('app.head_mc_dashboard'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h2 class="fw-bold mb-0"><i class="fas fa-clipboard-check text-primary me-2"></i>{{ __('app.head_mc_dashboard') }}</h2>
    <div class="d-flex align-items-center gap-2">
        <form method="GET" class="d-flex align-items-center gap-1">
            <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm" onchange="this.form.submit()">
        </form>
        <a href="{{ route('mc-assignment.index') }}" class="btn btn-outline-primary btn-sm text-nowrap"><i class="fas fa-user-tag me-1"></i>{{ __('app.mc_assignment') }}</a>
    </div>
</div>

@php
    $cards = [
        ['label' => __('app.hmc_total_today'), 'val' => $summary['total'], 'bg' => 'primary', 'icon' => 'fa-futbol'],
        ['label' => __('app.hmc_assigned'), 'val' => $summary['assigned'], 'bg' => 'success', 'icon' => 'fa-user-check'],
        ['label' => __('app.hmc_unassigned'), 'val' => $summary['unassigned'], 'bg' => 'warning', 'icon' => 'fa-user-slash'],
        ['label' => __('app.hmc_live'), 'val' => $summary['live'], 'bg' => 'danger', 'icon' => 'fa-circle'],
        ['label' => __('app.hmc_completed'), 'val' => $summary['completed'], 'bg' => 'secondary', 'icon' => 'fa-lock'],
        ['label' => __('app.hmc_missing_photos'), 'val' => $summary['missing_photos'], 'bg' => 'warning', 'icon' => 'fa-camera'],
        ['label' => __('app.hmc_missing_report'), 'val' => $summary['missing_report'], 'bg' => 'warning', 'icon' => 'fa-file-circle-xmark'],
        ['label' => __('app.hmc_pending_confirm'), 'val' => $summary['pending_confirmation'], 'bg' => 'info', 'icon' => 'fa-hourglass-half'],
    ];
@endphp

<div class="row g-2 mb-3">
    @foreach($cards as $c)
        <div class="col-6 col-md-3">
            <div class="card border-{{ $c['bg'] }} h-100">
                <div class="card-body py-2 text-center">
                    <div class="text-{{ $c['bg'] }} mb-1"><i class="fas {{ $c['icon'] }}"></i></div>
                    <div class="fs-4 fw-bold">{{ $c['val'] }}</div>
                    <div class="small text-muted">{{ $c['label'] }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@php
    $yn = fn($ok) => $ok
        ? '<span class="badge bg-success">'.__('app.hmc_done').'</span>'
        : '<span class="badge bg-secondary">'.__('app.hmc_pending').'</span>';
    $statusBadge = [
        'scheduled' => 'bg-info', 'live' => 'bg-danger', 'half_time' => 'bg-warning text-dark',
        'second_half' => 'bg-danger', 'full_time' => 'bg-secondary', 'completed' => 'bg-secondary', 'closed' => 'bg-dark',
    ];
@endphp

<div class="card">
    <div class="card-header bg-dark text-white py-2"><i class="fas fa-tasks me-2"></i>{{ __('app.hmc_task_status') }} — {{ \Illuminate\Support\Carbon::parse($date)->format('d M Y') }}</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.85rem;">
            <thead class="table-light">
                <tr>
                    <th>{{ __('app.match_label') }}</th>
                    <th>{{ __('app.mc_assigned_to') }}</th>
                    <th>{{ __('app.hmc_status') }}</th>
                    <th class="text-center">{{ __('app.hmc_lineup') }}</th>
                    <th class="text-center">{{ __('app.hmc_jersey') }}</th>
                    <th class="text-center">{{ __('app.hmc_events') }}</th>
                    <th class="text-center">{{ __('app.hmc_photos') }}</th>
                    <th class="text-center">{{ __('app.hmc_report') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                    @php $m = $r['match']; @endphp
                    <tr>
                        <td>
                            <a href="{{ route('matches.show', $m) }}" class="fw-bold text-decoration-none">{{ $m->homeTeam->name ?? 'Home' }} <span class="text-muted">vs</span> {{ $m->awayTeam->name ?? 'Away' }}</a>
                            <div class="small text-muted">{{ $m->competition->name ?? '-' }} &bull; {{ optional($m->match_date)->format('g:i A') ?? '-' }}</div>
                        </td>
                        <td>
                            @if($r['assigned'])
                                <span class="badge bg-success">{{ $r['mc_name'] }}</span>
                            @else
                                <a href="{{ route('mc-assignment.index') }}" class="badge bg-warning text-dark text-decoration-none">{{ __('app.mc_unassigned') }}</a>
                            @endif
                        </td>
                        <td><span class="badge {{ $statusBadge[$r['status']] ?? 'bg-secondary' }}">{{ strtoupper(str_replace('_',' ',$r['status'])) }}</span></td>
                        <td class="text-center">{!! $yn($r['lineup_ok']) !!}</td>
                        <td class="text-center">{!! $yn($r['jersey_ok']) !!}</td>
                        <td class="text-center">
                            @if($r['events_incomplete'])
                                <span class="badge bg-warning text-dark" title="{{ __('app.hmc_events_incomplete') }}">0</span>
                            @else
                                <span class="badge bg-light text-dark">{{ $r['events_count'] }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $r['photos_complete'] ? 'bg-success' : 'bg-secondary' }}">{{ $r['photos'] }}/{{ $r['photos_total'] }}</span>
                        </td>
                        <td class="text-center">
                            @if($r['report_done'])
                                <span class="badge bg-success">{{ __('app.hmc_completed') }}</span>
                            @elseif($r['pending_confirmation'])
                                <span class="badge bg-info">{{ __('app.hmc_pending_confirm') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('app.hmc_pending') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">{{ __('app.hmc_no_matches') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
