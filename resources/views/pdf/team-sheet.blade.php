<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Sheet / Start List</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #198754;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 18px;
            color: #198754;
            margin-bottom: 4px;
        }

        .header h2 {
            font-size: 14px;
            color: #555;
            font-weight: normal;
        }

        .match-info {
            width: 100%;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
        }

        .match-info td {
            padding: 3px 8px;
            font-size: 11px;
        }

        .match-info .label {
            font-weight: bold;
            color: #555;
            width: 120px;
        }

        .match-info .teams {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            padding: 8px;
        }

        .team-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .team-title {
            font-size: 13px;
            font-weight: bold;
            color: #fff;
            background-color: #212529;
            padding: 6px 10px;
            margin-bottom: 0;
        }

        .section-subtitle {
            font-size: 10px;
            font-weight: bold;
            color: #198754;
            padding: 6px 0 4px;
            border-bottom: 1px solid #198754;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.roster-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        table.roster-table th,
        table.roster-table td {
            border: 1px solid #dee2e6;
            padding: 4px 8px;
            text-align: left;
        }

        table.roster-table th {
            background-color: #343a40;
            color: #fff;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.roster-table td {
            font-size: 10px;
        }

        table.roster-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .officials-section {
            margin-top: 8px;
        }

        .officials-table {
            width: 100%;
            border-collapse: collapse;
        }

        .officials-table th,
        .officials-table td {
            border: 1px solid #dee2e6;
            padding: 3px 8px;
            font-size: 10px;
        }

        .officials-table th {
            background-color: #6c757d;
            color: #fff;
            font-size: 9px;
            text-transform: uppercase;
        }

        .signatures {
            margin-top: 40px;
            width: 100%;
            page-break-inside: avoid;
        }

        .signatures td {
            width: 33.33%;
            padding: 10px 15px;
            text-align: center;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 10px;
            color: #555;
            margin-top: 40px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            color: #999;
        }

        .no-data {
            text-align: center;
            color: #999;
            padding: 8px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $match->competition->name ?? 'JB Football League' }}</h1>
        <h2>Team Sheet / Start List</h2>
    </div>

    <!-- Match Info -->
    <table class="match-info">
        <tr>
            <td colspan="4" class="teams">
                {{ $match->homeTeam->name ?? 'Home Team' }}
                &nbsp;&nbsp; vs &nbsp;&nbsp;
                {{ $match->awayTeam->name ?? 'Away Team' }}
            </td>
        </tr>
        <tr>
            <td class="label">Date & Time:</td>
            <td>{{ $match->match_date ? $match->match_date->format('d M Y, H:i') : '-' }}</td>
            <td class="label">Venue:</td>
            <td>{{ $match->venue ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Matchday:</td>
            <td>{{ $match->matchday ?? '-' }}</td>
            <td class="label">Competition:</td>
            <td>{{ $match->competition->name ?? '-' }} ({{ $match->competition->season ?? '' }})</td>
        </tr>
    </table>

    @php
        $homeLineup = $match->lineups->where('team_id', $match->home_team_id);
        $awayLineup = $match->lineups->where('team_id', $match->away_team_id);
        $homeStarters = $homeLineup->where('is_starting', true)->sortBy('jersey_number');
        $homeSubs = $homeLineup->where('is_starting', false)->sortBy('jersey_number');
        $awayStarters = $awayLineup->where('is_starting', true)->sortBy('jersey_number');
        $awaySubs = $awayLineup->where('is_starting', false)->sortBy('jersey_number');

        $homeTeam = $match->homeTeam;
        $awayTeam = $match->awayTeam;
    @endphp

    <!-- Home Team Section -->
    <div class="team-section">
        <div class="team-title">{{ $homeTeam->name ?? 'Home Team' }} (Home)</div>

        <div class="section-subtitle">Starting XI</div>
        <table class="roster-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No.</th>
                    <th>Name</th>
                    <th style="width: 100px;">Position</th>
                </tr>
            </thead>
            <tbody>
                @forelse($homeStarters as $lineup)
                    <tr>
                        <td>{{ $lineup->jersey_number }}</td>
                        <td>{{ $lineup->player->name ?? '-' }}</td>
                        <td>{{ ucfirst($lineup->position ?? '-') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="no-data">No starting lineup submitted</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-subtitle">Substitutes</div>
        <table class="roster-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No.</th>
                    <th>Name</th>
                    <th style="width: 100px;">Position</th>
                </tr>
            </thead>
            <tbody>
                @forelse($homeSubs as $lineup)
                    <tr>
                        <td>{{ $lineup->jersey_number }}</td>
                        <td>{{ $lineup->player->name ?? '-' }}</td>
                        <td>{{ ucfirst($lineup->position ?? '-') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="no-data">No substitutes listed</td></tr>
                @endforelse
            </tbody>
        </table>

        @if($homeTeam && $homeTeam->officials->isNotEmpty())
            <div class="officials-section">
                <div class="section-subtitle">Team Officials</div>
                <table class="officials-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($homeTeam->officials as $official)
                            <tr>
                                <td>{{ $official->name }}</td>
                                <td>{{ ucfirst($official->role ?? '-') }}</td>
                                <td>{{ $official->contact_phone ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Away Team Section -->
    <div class="team-section">
        <div class="team-title">{{ $awayTeam->name ?? 'Away Team' }} (Away)</div>

        <div class="section-subtitle">Starting XI</div>
        <table class="roster-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No.</th>
                    <th>Name</th>
                    <th style="width: 100px;">Position</th>
                </tr>
            </thead>
            <tbody>
                @forelse($awayStarters as $lineup)
                    <tr>
                        <td>{{ $lineup->jersey_number }}</td>
                        <td>{{ $lineup->player->name ?? '-' }}</td>
                        <td>{{ ucfirst($lineup->position ?? '-') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="no-data">No starting lineup submitted</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-subtitle">Substitutes</div>
        <table class="roster-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No.</th>
                    <th>Name</th>
                    <th style="width: 100px;">Position</th>
                </tr>
            </thead>
            <tbody>
                @forelse($awaySubs as $lineup)
                    <tr>
                        <td>{{ $lineup->jersey_number }}</td>
                        <td>{{ $lineup->player->name ?? '-' }}</td>
                        <td>{{ ucfirst($lineup->position ?? '-') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="no-data">No substitutes listed</td></tr>
                @endforelse
            </tbody>
        </table>

        @if($awayTeam && $awayTeam->officials->isNotEmpty())
            <div class="officials-section">
                <div class="section-subtitle">Team Officials</div>
                <table class="officials-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($awayTeam->officials as $official)
                            <tr>
                                <td>{{ $official->name }}</td>
                                <td>{{ ucfirst($official->role ?? '-') }}</td>
                                <td>{{ $official->contact_phone ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Signatures -->
    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line">Home Team Manager</div>
            </td>
            <td>
                <div class="signature-line">Match Commissioner</div>
            </td>
            <td>
                <div class="signature-line">Away Team Manager</div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        Generated on {{ now()->format('d M Y, H:i:s') }} &mdash; JB Football League Management System
    </div>
</body>
</html>
