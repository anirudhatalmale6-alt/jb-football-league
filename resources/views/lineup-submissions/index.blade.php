@extends('layouts.app')
@section('title', __('app.lineup_submissions'))

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-clipboard-list me-2"></i>{{ __('app.lineup_submissions') }}</h2>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="competition_id" class="form-select">
                        <option value="">{{ __('app.all_competitions') }}</option>
                        @foreach($competitions as $comp)
                            <option value="{{ $comp->id }}" {{ request('competition_id') == $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success"><i class="fas fa-filter me-1"></i>{{ __('app.filter') }}</button>
                </div>
            </form>
        </div>
    </div>

    @if($matches->isEmpty())
        <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>{{ __('app.no_upcoming_matches') }}</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>{{ __('app.match') }}</th>
                        <th>{{ __('app.competition') }}</th>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.venue') }}</th>
                        <th class="text-center">{{ __('app.home_lineup') }}</th>
                        <th class="text-center">{{ __('app.away_lineup') }}</th>
                        @if($user->canOperateMatches())
                            <th class="text-center">{{ __('app.review') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($matches as $match)
                        @php
                            $homeSub = $match->lineupSubmissions->where('team_id', $match->home_team_id)->first();
                            $awaySub = $match->lineupSubmissions->where('team_id', $match->away_team_id)->first();
                            $isOverdue = $match->match_date && $match->match_date->diffInHours(now(), false) > -1;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $match->homeTeam->name ?? '-' }}</strong> vs <strong>{{ $match->awayTeam->name ?? '-' }}</strong>
                                @if($match->match_code)<br><small class="text-muted">{{ $match->match_code }}</small>@endif
                            </td>
                            <td>{{ $match->competition->name ?? '-' }}</td>
                            <td>
                                {{ $match->match_date ? $match->match_date->format('d M Y H:i') : '-' }}
                                @if($isOverdue && (!$homeSub || $homeSub->status === 'draft') || ($isOverdue && (!$awaySub || $awaySub->status === 'draft')))
                                    <br><span class="badge bg-danger">{{ __('app.overdue') }}</span>
                                @endif
                            </td>
                            <td>{{ $match->venue ?? '-' }}</td>
                            <td class="text-center">
                                @include('lineup-submissions._status_badge', ['submission' => $homeSub, 'match' => $match, 'team' => $match->homeTeam, 'user' => $user])
                            </td>
                            <td class="text-center">
                                @include('lineup-submissions._status_badge', ['submission' => $awaySub, 'match' => $match, 'team' => $match->awayTeam, 'user' => $user])
                            </td>
                            @if($user->canOperateMatches())
                                <td class="text-center">
                                    @if($match->canOperateBy($user))
                                        <a href="{{ route('lineup-submissions.review', $match->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-clipboard-check me-1"></i>{{ __('app.review') }}
                                        </a>
                                    @else
                                        <span class="text-muted small" title="{{ __('app.not_your_match') }}">&mdash;</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $matches->links() }}
    @endif
</div>
@endsection
