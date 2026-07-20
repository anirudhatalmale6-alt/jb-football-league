@extends('layouts.app')

@section('title', 'Relegate Team - ' . $team->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0"><i class="fas fa-arrow-down me-2"></i>Relegate Team to Premier League</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action will immediately relegate the team. The team cannot reject this decision.
                </div>

                <div class="mb-4">
                    <h5>Team Details</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th class="bg-light" style="width: 40%;">Team Name</th>
                            <td><strong>{{ $team->name }}</strong></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Current League</th>
                            <td><span class="badge bg-primary">{{ $team->competition->name ?? 'N/A' }}</span></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Relegated To</th>
                            <td><span class="badge bg-success">{{ $premierLeague->name }}</span></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Current Fee</th>
                            <td>RM {{ number_format($team->competition->baseFee() + ($team->affiliate_fee_required ? 50 : 0), 2) }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">New Fee (Premier League)</th>
                            <td>RM {{ number_format($premierLeague->baseFee() + ($team->affiliate_fee_required ? 50 : 0), 2) }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Contact Email</th>
                            <td>{{ $team->contact_email ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>

                <div class="mb-4">
                    <h5>What will happen:</h5>
                    <ul>
                        <li>Team will be moved from <strong>Super League</strong> to <strong>Premier League</strong> immediately</li>
                        <li>Registration fee will be updated to Premier League rate (RM 3,050)</li>
                        <li>An official relegation letter will be generated</li>
                        <li>Team will receive an email notification with the relegation letter</li>
                        <li>Team group assignment will be reset</li>
                    </ul>
                </div>

                <form action="{{ route('relegations.store', $team) }}" method="POST">
                    @csrf
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to relegate {{ $team->name }} to Premier League? This action is immediate and cannot be undone.')">
                            <i class="fas fa-arrow-down me-1"></i> Confirm Relegation
                        </button>
                        <a href="{{ route('teams.show', $team) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
