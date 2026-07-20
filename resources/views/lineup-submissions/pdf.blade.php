<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Line-Up - {{ $team->name }}</title>
    <style>
        @page { margin: 20mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #198754; padding-bottom: 15px; }
        .header img { height: 60px; margin-bottom: 10px; }
        .header h1 { font-size: 18px; margin: 5px 0; color: #198754; }
        .header h2 { font-size: 14px; margin: 3px 0; color: #666; }
        .match-info { margin-bottom: 20px; }
        .match-info table { width: 100%; border-collapse: collapse; }
        .match-info td { padding: 5px 10px; border: 1px solid #ddd; }
        .match-info td:first-child { font-weight: bold; background: #f8f9fa; width: 30%; }
        .section-title { background: #198754; color: white; padding: 8px 12px; font-size: 14px; font-weight: bold; margin: 15px 0 5px 0; }
        .section-title.subs { background: #ffc107; color: #333; }
        .players-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .players-table th { background: #f8f9fa; border: 1px solid #ddd; padding: 6px 10px; text-align: left; font-size: 11px; }
        .players-table td { border: 1px solid #ddd; padding: 6px 10px; font-size: 11px; }
        .players-table tr:nth-child(even) { background: #f9f9f9; }
        .footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 10px; color: #999; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .status-approved { background: #d1e7dd; color: #0f5132; }
        .status-submitted { background: #fff3cd; color: #664d03; }
        .status-locked { background: #212529; color: white; }
        .signatures { margin-top: 40px; }
        .signatures table { width: 100%; }
        .signatures td { padding: 10px; text-align: center; width: 50%; }
        .sign-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; }
            .u23-badge { background: #ffc107; color: #000; font-size: 6px; padding: 0px 2px; border-radius: 2px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('images/jbfa_logo.png')))
            <img src="{{ public_path('images/jbfa_logo.png') }}" alt="JBFA">
        @endif
        <h1>JBFA Football League</h1>
        <h2>Official Match Line-Up Report</h2>
    </div>

    <div class="match-info">
        <table>
            <tr><td>{{ __('app.competition') }}</td><td>{{ $match->competition->name ?? '-' }}</td></tr>
            <tr><td>{{ __('app.match_code_label') }}</td><td>{{ $match->match_code ?? '-' }}</td></tr>
            <tr><td>{{ __('app.match') }}</td><td>{{ $match->homeTeam->name ?? '-' }} vs {{ $match->awayTeam->name ?? '-' }}</td></tr>
            <tr><td>{{ __('app.date') }} / {{ __('app.time') }}</td><td>{{ $match->match_date ? $match->match_date->format('d M Y, H:i') : '-' }}</td></tr>
            <tr><td>{{ __('app.venue') }}</td><td>{{ $match->venue ?? '-' }}</td></tr>
            <tr><td>{{ __('app.team') }}</td><td><strong>{{ $team->name }}</strong></td></tr>
            <tr><td>{{ __('app.opponent') }}</td><td>{{ $opponent->name ?? '-' }}</td></tr>
            <tr>
                <td>{{ __('app.status') }}</td>
                <td>
                    <span class="status-badge status-{{ $submission->status }}">
                        {{ strtoupper($submission->status) }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">{{ __('app.starting_eleven') }} ({{ $starting->count() }})</div>
    <table class="players-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>#</th>
                <th>{{ __('app.name') }}</th>
                <th>{{ __('app.position') }}</th>
                <th>IC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($starting as $i => $lineup)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $lineup->jersey_number }}</td>
                    <td>{{ $lineup->player->name ?? '-' }}</td>
                    <td>{{ ucfirst($lineup->position ?? $lineup->player->position ?? '-') }}</td>
                    <td>{{ $lineup->player->ic_number ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title subs">{{ __('app.substitutes') }} ({{ $subs->count() }})</div>
    <table class="players-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>#</th>
                <th>{{ __('app.name') }}</th>
                <th>{{ __('app.position') }}</th>
                <th>IC</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subs as $lineup)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $lineup->jersey_number }}</td>
                    <td>{{ $lineup->player->name ?? '-' }}</td>
                    <td>{{ ucfirst($lineup->position ?? $lineup->player->position ?? '-') }}</td>
                    <td>{{ $lineup->player->ic_number ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center; color:#999;">{{ __('app.no_subs') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="signatures">
        <table>
            <tr>
                <td>
                    <div class="sign-line">
                        {{ __('app.team_manager') }}
                        @if($submission->submittedByUser)
                            <br><small>{{ $submission->submittedByUser->name }}</small>
                            @if($submission->submitted_at)<br><small>{{ $submission->submitted_at->format('d M Y H:i') }}</small>@endif
                        @endif
                    </div>
                </td>
                <td>
                    <div class="sign-line">
                        {{ __('app.match_commissioner') }}
                        @if($submission->reviewedByUser)
                            <br><small>{{ $submission->reviewedByUser->name }}</small>
                            @if($submission->reviewed_at)<br><small>{{ $submission->reviewed_at->format('d M Y H:i') }}</small>@endif
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        {{ __('app.generated_on') }}: {{ now()->format('d M Y H:i') }} | JBFA Football League Management System | myjbfa.com
    </div>
</body>
</html>
