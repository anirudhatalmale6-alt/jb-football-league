@if($submission)
    @if($submission->status === 'draft')
        <span class="badge bg-secondary">{{ __('app.draft') }}</span>
    @elseif($submission->status === 'submitted')
        <span class="badge bg-warning text-dark">{{ __('app.submitted') }}</span>
    @elseif($submission->status === 'rejected')
        <span class="badge bg-danger">{{ __('app.rejected') }}</span>
    @elseif($submission->status === 'approved')
        <span class="badge bg-success">{{ __('app.approved') }}</span>
    @elseif($submission->status === 'locked')
        <span class="badge bg-dark"><i class="fas fa-lock me-1"></i>{{ __('app.locked') }}</span>
    @endif
    @if($user->isTeamManager() && $user->managesTeam($team->id))
        <br>
        <a href="{{ route('lineup-submissions.show', [$match->id, $team->id]) }}" class="btn btn-sm btn-outline-secondary mt-1">
            <i class="fas fa-eye"></i>
        </a>
    @endif
@else
    @if($user->isTeamManager() && $user->managesTeam($team->id))
        <a href="{{ route('lineup-submissions.edit', [$match->id, $team->id]) }}" class="btn btn-sm btn-success">
            <i class="fas fa-plus me-1"></i>{{ __('app.create_lineup') }}
        </a>
    @else
        <span class="badge bg-light text-dark">{{ __('app.not_submitted') }}</span>
    @endif
@endif
