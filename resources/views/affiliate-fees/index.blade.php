@extends('layouts.app')

@section('title', __('app.affiliate_fees'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="mb-0"><i class="fas fa-id-card me-2 text-primary"></i>{{ __('app.affiliate_fees') }}</h2>
    <form action="{{ route('affiliate-fees.remind-all') }}" method="POST"
          onsubmit="return confirm('{{ __('app.confirm_remind_all') }}');">
        @csrf
        <input type="hidden" name="competition" value="{{ $competitionId }}">
        <button type="submit" class="btn btn-warning">
            <i class="fas fa-paper-plane me-1"></i> {{ __('app.remind_all_unpaid') }}
        </button>
    </form>
</div>

<p class="text-muted">{{ __('app.affiliate_fees_intro', ['fee' => number_format($fee, 2)]) }}</p>

{{-- Summary Cards --}}
<div class="row mb-4 g-3">
    <div class="col-6 col-md-3">
        <div class="card border-start border-primary border-4 h-100">
            <div class="card-body">
                <div class="text-muted small">{{ __('app.owes_rm50') }}</div>
                <div class="h4 fw-bold text-primary mb-0">{{ $totalTeams }}</div>
                <div class="small text-muted">{{ $exemptCount }} {{ __('app.exempt') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-start border-success border-4 h-100">
            <div class="card-body">
                <div class="text-muted small">{{ __('app.paid') }}</div>
                <div class="h4 fw-bold text-success mb-0">{{ $paidCount }}</div>
                <div class="small text-muted">RM {{ number_format($collected, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-start border-danger border-4 h-100">
            <div class="card-body">
                <div class="text-muted small">{{ __('app.unpaid') }}</div>
                <div class="h4 fw-bold text-danger mb-0">{{ $unpaidCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-start border-warning border-4 h-100">
            <div class="card-body">
                <div class="text-muted small">{{ __('app.outstanding') }}</div>
                <div class="h4 fw-bold text-warning mb-0">RM {{ number_format($outstanding, 2) }}</div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('affiliate-fees.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <label class="form-label small text-muted mb-1">{{ __('app.status') }}</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>{{ __('app.all') }}</option>
                    <option value="unpaid" {{ $statusFilter === 'unpaid' ? 'selected' : '' }}>{{ __('app.unpaid') }}</option>
                    <option value="paid" {{ $statusFilter === 'paid' ? 'selected' : '' }}>{{ __('app.paid') }}</option>
                    <option value="exempt" {{ $statusFilter === 'exempt' ? 'selected' : '' }}>{{ __('app.exempt') }}</option>
                </select>
            </div>
            <div class="col-sm-4">
                <label class="form-label small text-muted mb-1">{{ __('app.competition') }}</label>
                <select name="competition" class="form-select" onchange="this.form.submit()">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($competitions as $comp)
                        <option value="{{ $comp->id }}" {{ (string) $competitionId === (string) $comp->id ? 'selected' : '' }}>
                            {{ $comp->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i> {{ __('app.filter') }}
                </button>
            </div>
        </form>
    </div>
</div>

<p class="text-muted small mb-2"><i class="fas fa-hand-pointer me-1"></i>{{ __('app.affiliate_select_hint') }}</p>

{{-- One form wrapping the whole table: tick teams, then click an action --}}
<form id="bulkForm" method="POST">
    @csrf

    {{-- Action bar --}}
    <div class="card mb-2">
        <div class="card-body py-2 d-flex flex-wrap gap-2 align-items-center">
            <span class="text-muted small me-2"><span id="selCount">0</span> {{ __('app.selected') }}</span>
            <button type="submit" class="btn btn-success btn-sm"
                    formaction="{{ route('affiliate-fees.bulk-mark') }}"
                    onclick="document.getElementById('bulkAction').value='paid';">
                <i class="fas fa-check me-1"></i> {{ __('app.mark_selected_paid') }}
            </button>
            <button type="submit" class="btn btn-outline-secondary btn-sm"
                    formaction="{{ route('affiliate-fees.bulk-mark') }}"
                    onclick="document.getElementById('bulkAction').value='unpaid'; return confirm('{{ __('app.confirm_bulk_unpaid') }}');">
                <i class="fas fa-undo me-1"></i> {{ __('app.mark_selected_unpaid') }}
            </button>
            <button type="submit" class="btn btn-outline-warning btn-sm"
                    formaction="{{ route('affiliate-fees.bulk-remind') }}"
                    onclick="return confirm('{{ __('app.confirm_bulk_remind') }}');">
                <i class="fas fa-paper-plane me-1"></i> {{ __('app.remind_selected') }}
            </button>
            <span class="vr d-none d-md-inline mx-1"></span>
            <button type="submit" class="btn btn-outline-primary btn-sm"
                    formaction="{{ route('affiliate-fees.bulk-require') }}"
                    onclick="document.getElementById('bulkRequired').value='1';">
                <i class="fas fa-tag me-1"></i> {{ __('app.set_owes_rm50') }}
            </button>
            <button type="submit" class="btn btn-outline-dark btn-sm"
                    formaction="{{ route('affiliate-fees.bulk-require') }}"
                    onclick="document.getElementById('bulkRequired').value='0'; return confirm('{{ __('app.confirm_bulk_exempt') }}');">
                <i class="fas fa-ban me-1"></i> {{ __('app.set_exempt_no_rm50') }}
            </button>
        </div>
    </div>
    <input type="hidden" name="action" id="bulkAction" value="paid">
    <input type="hidden" name="required" id="bulkRequired" value="1">

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:42px;" class="text-center">
                                <input type="checkbox" id="selectAll" class="form-check-input" title="{{ __('app.select_all') }}">
                            </th>
                            <th>{{ __('app.team') }}</th>
                            <th>{{ __('app.competition') }}</th>
                            <th class="text-end">{{ __('app.amount') }} (RM)</th>
                            <th>{{ __('app.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teams as $team)
                            <tr class="team-row" onclick="if(event.target.tagName!=='A' && event.target.tagName!=='INPUT'){var c=this.querySelector('.row-check'); c.checked=!c.checked; c.dispatchEvent(new Event('change',{bubbles:true}));}">
                                <td class="text-center">
                                    <input type="checkbox" name="team_ids[]" value="{{ $team->id }}"
                                           class="form-check-input row-check">
                                </td>
                                <td>
                                    <a href="{{ route('teams.show', $team->id) }}" class="text-decoration-none fw-semibold">
                                        {{ $team->name }}
                                    </a>
                                </td>
                                <td>{{ $team->competition->name ?? '-' }}</td>
                                <td class="text-end fw-bold">{{ number_format($team->affiliateFeeAmount(), 2) }}</td>
                                <td>
                                    @if(!$team->affiliate_fee_required)
                                        <span class="badge bg-secondary"><i class="fas fa-ban me-1"></i>{{ __('app.exempt') }}</span>
                                    @elseif($team->affiliate_fee_paid)
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>{{ __('app.paid') }}</span>
                                        @if($team->affiliate_fee_paid_at)
                                            <div class="small text-muted">{{ $team->affiliate_fee_paid_at->format('d M Y') }}</div>
                                        @endif
                                    @else
                                        <span class="badge bg-danger">{{ __('app.unpaid') }}</span>
                                        @if($team->affiliate_fee_reminded_at)
                                            <div class="small text-muted">
                                                <i class="fas fa-bell me-1"></i>{{ __('app.reminded') }}: {{ $team->affiliate_fee_reminded_at->format('d M') }}
                                            </div>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-id-card fa-2x mb-2 d-block"></i>
                                    {{ __('app.no_teams_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

<div class="mt-3">
    {{ $teams->links() }}
</div>

<script>
(function () {
    var selectAll = document.getElementById('selectAll');
    var checks = Array.prototype.slice.call(document.querySelectorAll('.row-check'));
    var counter = document.getElementById('selCount');

    function refresh() {
        var n = checks.filter(function (c) { return c.checked; }).length;
        counter.textContent = n;
        if (selectAll) {
            selectAll.checked = n > 0 && n === checks.length;
            selectAll.indeterminate = n > 0 && n < checks.length;
        }
    }
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checks.forEach(function (c) { c.checked = selectAll.checked; });
            refresh();
        });
    }
    checks.forEach(function (c) { c.addEventListener('change', refresh); });
    refresh();
})();
</script>
@endsection
