<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Match Summary Report</title>
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

        .match-score {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .match-score .teams {
            font-size: 16px;
            font-weight: bold;
        }

        .match-score .score {
            font-size: 28px;
            font-weight: bold;
            margin: 8px 0;
            color: #212529;
        }

        .match-score .status {
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #198754;
            margin: 20px 0 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #198754;
        }

        .match-info-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .match-info-table td {
            padding: 4px 8px;
            vertical-align: top;
        }

        .match-info-table .label {
            font-weight: bold;
            color: #555;
            width: 130px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #dee2e6;
            padding: 5px 8px;
            text-align: left;
        }

        table.data-table th {
            background-color: #212529;
            color: #fff;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.data-table td {
            font-size: 10px;
        }

        table.data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .lineups-container {
            width: 100%;
            margin-bottom: 15px;
        }

        .lineups-container td {
            width: 50%;
            vertical-align: top;
            padding: 0 5px;
        }

        .lineup-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .lineup-table th,
        .lineup-table td {
            border: 1px solid #dee2e6;
            padding: 3px 6px;
            font-size: 10px;
        }

        .lineup-table th {
            background-color: #343a40;
            color: #fff;
            font-size: 9px;
            text-transform: uppercase;
        }

        .lineup-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .lineup-subtitle {
            font-size: 10px;
            font-weight: bold;
            color: #555;
            margin: 8px 0 4px;
        }

        .officials-table {
            width: 100%;
            margin-top: 10px;
        }

        .officials-table td {
            padding: 3px 8px;
            font-size: 10px;
        }

        .officials-table .label {
            font-weight: bold;
            color: #555;
            width: 160px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            color: #999;
        }

        .event-icon {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 2px;
            vertical-align: middle;
            margin-right: 3px;
        }

        .event-goal { color: #198754; font-weight: bold; }
        .event-own-goal { color: #dc3545; font-weight: bold; }
        .event-yellow { color: #ffc107; }
        .event-red { color: #dc3545; }
        .event-sub { color: #0dcaf0; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $match->competition->name ?? 'JB Football League' }}</h1>
        <h2>Match Summary Report</h2>
    </div>

    <!-- Score -->
    <div class="match-score">
        <div class="teams">
            {{ $match->homeTeam->name ?? 'Home Team' }}
            &nbsp;&nbsp;vs&nbsp;&nbsp;
            {{ $match->awayTeam->name ?? 'Away Team' }}
        </div>
        <div class="score">
            {{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}
        </div>
        <div class="status">{{ ucfirst($match->status ?? 'N/A') }}</div>
    </div>

    <!-- Match Info -->
    <div class="section-title">Match Information</div>
    <table class="match-info-table">
        <tr>
            <td class="label">Date & Time:</td>
            <td>{{ $match->match_date ? $match->match_date->format('d M Y, H:i') : '-' }}</td>
            <td class="label">Venue:</td>
            <td>{{ $match->venue ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Competition:</td>
            <td>{{ $match->competition->name ?? '-' }}</td>
            <td class="label">Matchday:</td>
            <td>{{ $match->matchday ?? '-' }}</td>
        </tr>
    </table>

    <!-- Lineups -->
    @php
        $homeLineup = $match->lineups->where('team_id', $match->home_team_id);
        $awayLineup = $match->lineups->where('team_id', $match->away_team_id);
        $homeStarters = $homeLineup->where('is_starting', true)->sortBy('jersey_number');
        $homeSubs = $homeLineup->where('is_starting', false)->sortBy('jersey_number');
        $awayStarters = $awayLineup->where('is_starting', true)->sortBy('jersey_number');
        $awaySubs = $awayLineup->where('is_starting', false)->sortBy('jersey_number');
    @endphp

    @if($homeLineup->isNotEmpty() || $awayLineup->isNotEmpty())
        <div class="section-title">Lineups</div>
        <table class="lineups-container">
            <tr>
                <td>
                    <div class="lineup-subtitle">{{ $match->homeTeam->name ?? 'Home' }} - Starting XI</div>
                    <table class="lineup-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Name</th>
                                <th>Position</th>
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
                                <tr><td colspan="3" style="text-align:center; color:#999;">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($homeSubs->isNotEmpty())
                        <div class="lineup-subtitle">Substitutes</div>
                        <table class="lineup-table">
                            <tbody>
                                @foreach($homeSubs as $lineup)
                                    <tr>
                                        <td style="width: 40px;">{{ $lineup->jersey_number }}</td>
                                        <td>{{ $lineup->player->name ?? '-' }}</td>
                                        <td>{{ ucfirst($lineup->position ?? '-') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </td>
                <td>
                    <div class="lineup-subtitle">{{ $match->awayTeam->name ?? 'Away' }} - Starting XI</div>
                    <table class="lineup-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Name</th>
                                <th>Position</th>
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
                                <tr><td colspan="3" style="text-align:center; color:#999;">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($awaySubs->isNotEmpty())
                        <div class="lineup-subtitle">Substitutes</div>
                        <table class="lineup-table">
                            <tbody>
                                @foreach($awaySubs as $lineup)
                                    <tr>
                                        <td style="width: 40px;">{{ $lineup->jersey_number }}</td>
                                        <td>{{ $lineup->player->name ?? '-' }}</td>
                                        <td>{{ ucfirst($lineup->position ?? '-') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </td>
            </tr>
        </table>
    @endif

    <!-- Match Events -->
    @if($match->events->isNotEmpty())
        <div class="section-title">Match Events</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 60px;">Minute</th>
                    <th style="width: 120px;">Event</th>
                    <th>Team</th>
                    <th>Player</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($match->events->sortBy('minute') as $event)
                    <tr>
                        <td>{{ $event->minute }}'{{ $event->extra_time_minute ? '+' . $event->extra_time_minute : '' }}</td>
                        <td>
                            @switch($event->event_type)
                                @case('goal')
                                    <span class="event-goal">&#9917; Goal</span>
                                    @break
                                @case('own_goal')
                                    <span class="event-own-goal">&#9917; Own Goal</span>
                                    @break
                                @case('yellow_card')
                                    <span class="event-yellow">&#9646; Yellow Card</span>
                                    @break
                                @case('red_card')
                                    <span class="event-red">&#9646; Red Card</span>
                                    @break
                                @case('substitution_in')
                                    <span class="event-sub">&#8644; Sub In</span>
                                    @break
                                @case('substitution_out')
                                    <span class="event-sub">&#8644; Sub Out</span>
                                    @break
                                @case('penalty_scored')
                                    <span class="event-goal">&#9917; Penalty (Scored)</span>
                                    @break
                                @case('penalty_missed')
                                    <span style="color:#999;">&#9917; Penalty (Missed)</span>
                                    @break
                                @default
                                    {{ ucfirst(str_replace('_', ' ', $event->event_type)) }}
                            @endswitch
                        </td>
                        <td>{{ $event->team->name ?? '-' }}</td>
                        <td>{{ $event->player->name ?? '-' }}</td>
                        <td>{{ $event->notes ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Officials -->
    <div class="section-title">Match Officials</div>
    <table class="officials-table">
        <tr>
            <td class="label">Referee:</td>
            <td>{{ $match->referee ?? '-' }}</td>
            <td class="label">Assistant Referee 1:</td>
            <td>{{ $match->assistant_referee_1 ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Assistant Referee 2:</td>
            <td>{{ $match->assistant_referee_2 ?? '-' }}</td>
            <td class="label">Fourth Official:</td>
            <td>{{ $match->fourth_official ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Match Commissioner:</td>
            <td>{{ $match->match_commissioner ?? '-' }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        Generated on {{ now()->format('d M Y, H:i:s') }} &mdash; JB Football League Management System
    </div>
</body>
</html>
