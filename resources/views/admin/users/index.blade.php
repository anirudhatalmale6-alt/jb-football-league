@extends('layouts.app')

@section('title', __('app.user_management'))

@push('styles')
<style>
    .user-name, .user-club { text-transform: uppercase; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fas fa-users-cog text-success me-2"></i>{{ __('app.user_management') }}
            </h2>
            <p class="text-muted mb-0">{{ __('app.user_management_desc') }}</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="card text-center shadow-sm">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-dark">{{ $stats['total'] }}</div>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center shadow-sm border-danger">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-danger">{{ $stats['super_admin'] }}</div>
                    <small class="text-muted">Super Admin</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center shadow-sm border-primary">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-primary">{{ $stats['league_admin'] }}</div>
                    <small class="text-muted">League Admin</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center shadow-sm border-success">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-success">{{ $stats['team_manager'] }}</div>
                    <small class="text-muted">Team Manager</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center shadow-sm border-secondary">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-secondary">{{ $stats['public'] }}</div>
                    <small class="text-muted">Public</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center shadow-sm border-warning">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-warning">{{ $stats['inactive'] }}</div>
                    <small class="text-muted">Disabled</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Name, email or club..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Role</label>
                    <select name="role" class="form-select form-select-sm">
                        <option value="">All Roles</option>
                        <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="league_admin" {{ request('role') === 'league_admin' ? 'selected' : '' }}>{{ __('app.league_admin_commissioner') }}</option>
                        <option value="head_match_commissioner" {{ request('role') === 'head_match_commissioner' ? 'selected' : '' }}>{{ __('app.role_head_mc') }}</option>
                        <option value="match_commissioner" {{ request('role') === 'match_commissioner' ? 'selected' : '' }}>{{ __('app.role_mc') }}</option>
                        <option value="team_manager" {{ request('role') === 'team_manager' ? 'selected' : '' }}>Team Manager</option>
                        <option value="public" {{ request('role') === 'public' ? 'selected' : '' }}>{{ __('app.public_viewer') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-1"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Registered Users</h5>
            <span class="badge bg-success">{{ $users->total() }} users</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Name</th>
                        <th class="d-none d-md-table-cell">Email</th>
                        <th class="d-none d-lg-table-cell">Club</th>
                        <th>Role</th>
                        <th class="d-none d-md-table-cell">Team</th>
                        <th>Status</th>
                        <th class="d-none d-lg-table-cell">Registered</th>
                        <th style="width:120px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="{{ !$user->is_active ? 'table-warning opacity-75' : '' }}">
                            <td class="text-muted small">{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="fw-semibold" id="name-display-{{ $user->id }}">{{ $user->name }}</span>
                                    @if($user->id !== auth()->id())
                                        <button type="button" class="btn btn-link btn-sm p-0 text-muted" onclick="editName({{ $user->id }}, '{{ addslashes($user->name) }}')" title="Edit name">
                                            <i class="fas fa-pencil-alt" style="font-size:0.7rem;"></i>
                                        </button>
                                    @endif
                                </div>
                                <small class="text-muted d-md-none">{{ $user->email }}</small>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <small>{{ $user->email }}</small>
                                @if($user->hasVerifiedEmail())
                                    <i class="fas fa-check-circle text-success ms-1" title="Verified"></i>
                                @else
                                    <i class="fas fa-exclamation-circle text-warning ms-1" title="Not Verified"></i>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell"><small>{{ $user->club_team ?? '-' }}</small></td>
                            <td>
                                @if($user->id === auth()->id())
                                    @if($user->role === 'super_admin')
                                        <span class="badge bg-danger">Super Admin</span>
                                    @elseif($user->role === 'league_admin')
                                        <span class="badge bg-primary">League Admin</span>
                                    @elseif($user->role === 'head_match_commissioner')
                                        <span class="badge bg-info text-dark">{{ __('app.role_head_mc') }}</span>
                                    @elseif($user->role === 'match_commissioner')
                                        <span class="badge bg-info text-dark">{{ __('app.role_mc') }}</span>
                                    @elseif($user->role === 'team_manager')
                                        <span class="badge bg-success">Team Manager</span>
                                    @else
                                        <span class="badge bg-secondary">Public</span>
                                    @endif
                                    <small class="text-muted">(you)</small>
                                @else
                                    <form method="POST" action="{{ route('admin.users.update-role', $user) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" class="form-select form-select-sm d-inline-block" style="width:auto;font-size:0.75rem;" onchange="this.form.submit()">
                                            <option value="super_admin" {{ $user->role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                            <option value="league_admin" {{ $user->role === 'league_admin' ? 'selected' : '' }}>League Admin</option>
                                            <option value="head_match_commissioner" {{ $user->role === 'head_match_commissioner' ? 'selected' : '' }}>{{ __('app.role_head_mc') }}</option>
                                            <option value="match_commissioner" {{ $user->role === 'match_commissioner' ? 'selected' : '' }}>{{ __('app.role_mc') }}</option>
                                            <option value="team_manager" {{ $user->role === 'team_manager' ? 'selected' : '' }}>Team Manager</option>
                                            <option value="public" {{ $user->role === 'public' ? 'selected' : '' }}>Public</option>
                                        </select>
                                    </form>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.assign-team', $user) }}" class="d-inline team-assign-form">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="team_id" value="{{ $user->team_id ?? '' }}">
                                        <input type="text"
                                            class="form-control form-control-sm d-inline-block team-input"
                                            style="width:80px;font-size:0.75rem;text-transform:uppercase;font-weight:bold;"
                                            value="{{ $user->team->short_name ?? '' }}"
                                            list="teamList"
                                            placeholder="---"
                                            autocomplete="off">
                                    </form>
                                @else
                                    <small>{{ $user->team->name ?? '-' }}</small>
                                @endif
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Disabled</span>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <small class="text-muted">{{ $user->created_at->format('d M Y') }}</small>
                            </td>
                            <td>
                                @if($user->id !== auth()->id())
                                    <div class="btn-group btn-group-sm">
                                        @if(!$user->hasVerifiedEmail())
                                            <form method="POST" action="{{ route('admin.users.verify-email', $user) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-info" title="Mark email as verified" onclick="return confirm('Manually verify email for {{ $user->name }}?')">
                                                    <i class="fas fa-envelope-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            @if($user->is_active)
                                                <button type="submit" class="btn btn-outline-warning" title="Disable account" onclick="return confirm('Disable {{ $user->name }}?')">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-outline-success" title="Enable account">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete user" onclick="return confirm('Delete {{ $user->name }}? This cannot be undone.')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-users-slash fa-2x mb-2 d-block"></i>
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="card-footer">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Role Legend -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Role Permissions</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Role</th>
                            <th>Permissions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge bg-danger">Super Admin</span></td>
                            <td><small>Full system access: user management, competitions, teams, fixtures, reports, settings</small></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-primary">League Admin / Commissioner</span></td>
                            <td><small>Match operations: line-up verification, live score, match events, match status, reports</small></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">Team Manager</span></td>
                            <td><small>Own team only: submit registration, submit line-up, amend rejected submissions</small></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-secondary">Public / Viewer</span></td>
                            <td><small>View only: fixtures, results, live score, standings, reports</small></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Name Modal -->
<div class="modal fade" id="editNameModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="editNameForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header py-2">
                    <h6 class="modal-title"><i class="fas fa-pencil-alt me-2"></i>Edit Name</h6>
                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label small fw-semibold">Full Name</label>
                    <input type="text" name="name" id="editNameInput" class="form-control" required maxlength="255">
                    <small class="text-muted">Name will be auto-formatted to Title Case.</small>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editName(userId, currentName) {
    document.getElementById('editNameForm').action = '/admin/users/' + userId + '/name';
    document.getElementById('editNameInput').value = currentName;
    new bootstrap.Modal(document.getElementById('editNameModal')).show();
}
</script>

@push('scripts')
<datalist id="teamList">
    @foreach($teams as $team)
        <option value="{{ strtoupper($team->short_name ?? $team->name) }} ({{ $team->competition->short_name ?? $team->competition->name ?? '-' }})" data-id="{{ $team->id }}">
    @endforeach
</datalist>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var teamMap = {};
    @foreach($teams as $team)
        teamMap["{{ strtoupper($team->short_name ?? $team->name) }} ({{ $team->competition->short_name ?? $team->competition->name ?? '-' }})"] = {{ $team->id }};
    @endforeach

    document.querySelectorAll('.team-input').forEach(function(input) {
        input.addEventListener('change', function() {
            var form = this.closest('form');
            var hiddenField = form.querySelector('input[name="team_id"]');
            var val = this.value.trim().toUpperCase();
            this.value = val;

            if (val === '') {
                hiddenField.value = '';
                form.submit();
            } else if (teamMap[val] !== undefined) {
                hiddenField.value = teamMap[val];
                form.submit();
            } else {
                this.style.borderColor = '#dc3545';
                this.title = 'Team not found';
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.dispatchEvent(new Event('change'));
            }
        });
    });
});
</script>
@endpush

@endsection
