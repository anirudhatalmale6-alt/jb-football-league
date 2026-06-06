@extends('layouts.app')

@section('title', __('app.competitions'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="fas fa-trophy text-success me-2"></i>{{ __('app.competitions') }}
    </h2>
    @auth
        @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
            <a href="{{ route('competitions.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> {{ __('app.create_competition') }}
            </a>
        @endif
    @endauth
</div>

@if($competitions->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-trophy fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">{{ __('app.no_competitions_found') }}</h5>
            <p class="text-muted">Competitions will appear here once created.</p>
        </div>
    </div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.season') }}</th>
                        <th>{{ __('app.type') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th>{{ __('app.start_date') }}</th>
                        <th>{{ __('app.end_date') }}</th>
                        <th class="text-center">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($competitions as $competition)
                        <tr>
                            <td class="fw-semibold d-flex align-items-center">
                                @if($competition->logo)
                                    <img src="{{ asset('storage/'.$competition->logo) }}" alt="" class="me-2" style="height:40px;width:40px;object-fit:contain;">
                                @endif
                                {{ $competition->name }}
                            </td>
                            <td>{{ $competition->season }}</td>
                            <td>
                                @if($competition->type === 'league')
                                    <span class="badge bg-primary">{{ __('app.league') }}</span>
                                @elseif($competition->type === 'knockout' || $competition->type === 'cup')
                                    <span class="badge bg-warning text-dark">{{ __('app.knockout') }}</span>
                                @elseif($competition->type === 'futsal')
                                    <span class="badge bg-info">{{ __('app.futsal') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($competition->type) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($competition->status === 'active')
                                    <span class="badge bg-success">{{ __('app.active') }}</span>
                                @elseif($competition->status === 'upcoming')
                                    <span class="badge bg-info">{{ __('app.upcoming') }}</span>
                                @elseif($competition->status === 'completed')
                                    <span class="badge bg-secondary">{{ __('app.completed') }}</span>
                                @else
                                    <span class="badge bg-dark">{{ ucfirst($competition->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $competition->start_date ? $competition->start_date->format('d M Y') : '-' }}</td>
                            <td>{{ $competition->end_date ? $competition->end_date->format('d M Y') : '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('competitions.show', $competition) }}" class="btn btn-sm btn-outline-primary" title="{{ __('app.view') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @auth
                                    @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                                        <a href="{{ route('competitions.edit', $competition) }}" class="btn btn-sm btn-outline-warning" title="{{ __('app.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('competitions.destroy', $competition) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this competition?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('app.delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endauth
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $competitions->links() }}
    </div>
@endif
@endsection
