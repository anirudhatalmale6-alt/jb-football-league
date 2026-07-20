@extends('layouts.app')

@section('title', 'Send Promotion Offer')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <h2 class="fw-bold mb-4">
                <i class="fas fa-trophy text-success me-2"></i>Send Promotion Offer
            </h2>

            <div class="card border-primary mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Promotion Details</h5>
                </div>
                <div class="card-body">
                    <table class="table mb-0">
                        <tr>
                            <td class="fw-bold" style="width: 40%;">Team</td>
                            <td>{{ $team->name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Current League</td>
                            <td>{{ $team->competition->name }} ({{ $team->competition->malayName() }})</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Promoted To</td>
                            <td><strong class="text-success">{{ $toCompetition->name }} ({{ $toCompetition->malayName() }})</strong></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">New Fee</td>
                            <td><strong class="text-primary">RM {{ number_format($newFee, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Contact Email</td>
                            <td>{{ $team->contact_email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Response Deadline</td>
                            <td><strong class="text-danger">48 hours from now</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card border-warning mb-4">
                <div class="card-body">
                    <h6 class="fw-bold"><i class="fas fa-exclamation-triangle text-warning me-2"></i>What happens when you send this offer:</h6>
                    <ul class="mb-0">
                        <li>An email with the promotion letter (PDF) will be sent to <strong>{{ $team->contact_email }}</strong></li>
                        <li>The team will see a promotion banner when they log in to myjbfa.com</li>
                        @if($toCompetition->id === 2)
                        <li>The team must respond within <strong>48 hours</strong> by agreeing to the new {{ $toCompetition->name }} fee (venue &amp; C licence already on file)</li>
                        @else
                        <li>The team must respond within <strong>48 hours</strong> by providing venue details, coaching license, and fee agreement</li>
                        @endif
                        <li>If accepted, the team will be automatically moved to {{ $toCompetition->name }}</li>
                    </ul>
                </div>
            </div>

            <form action="{{ route('promotions.store', $team) }}" method="POST">
                @csrf
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-success btn-lg"
                            onclick="return confirm('Send promotion offer to {{ $team->name }}?')">
                        <i class="fas fa-paper-plane me-2"></i>Send Promotion Offer
                    </button>
                    <a href="{{ route('teams.show', $team) }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Cancel
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
