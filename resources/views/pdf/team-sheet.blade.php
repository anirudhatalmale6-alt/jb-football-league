<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Sheet / Start List</title>
    <style>
        @page {
            margin: 15mm 12mm 15mm 12mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #222;
            line-height: 1.3;
        }

        /* ---- Blue header bar ---- */
        .blue-bar {
            background-color: #003366;
            color: #ffffff;
            padding: 4px 8px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .blue-bar-light {
            background-color: #e8eef5;
            color: #003366;
            padding: 3px 8px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* ---- Tables ---- */
        table {
            border-collapse: collapse;
        }

        /* ---- Lineup tables ---- */
        .lineup-table {
            width: 100%;
            border-collapse: collapse;
        }
        .lineup-table th {
            background-color: #003366;
            color: #ffffff;
            padding: 3px 5px;
            font-size: 7px;
            text-transform: uppercase;
            text-align: left;
            border: 1px solid #002244;
        }
        .lineup-table td {
            padding: 2px 5px;
            font-size: 8px;
            border: 1px solid #ccd6e0;
        }
        .lineup-table tr:nth-child(even) td {
            background-color: #f4f7fa;
        }

        /* ---- Officials table ---- */
        .officials-table {
            width: 100%;
            border-collapse: collapse;
        }
        .officials-table td {
            padding: 2px 5px;
            font-size: 8px;
            border: 1px solid #ccd6e0;
        }
        .officials-table .label-cell {
            background-color: #e8eef5;
            font-weight: bold;
            color: #003366;
            width: 35%;
        }

        /* ---- Match officials ---- */
        .match-officials-table {
            width: 100%;
            border-collapse: collapse;
        }
        .match-officials-table td {
            padding: 3px 8px;
            font-size: 8px;
            border: 1px solid #ccd6e0;
        }
        .match-officials-table .label-cell {
            background-color: #e8eef5;
            font-weight: bold;
            color: #003366;
            width: 180px;
        }
    </style>
</head>
<body>

@php
    $competitionName = $match->competition->name ?? 'Football League';
    $homeTeamName = $match->homeTeam->name ?? 'Home Team';
    $awayTeamName = $match->awayTeam->name ?? 'Away Team';
    $matchNo = $match->matchday ?? '-';
    $matchDate = $match->match_date ? $match->match_date->format('d/m/Y') : '-';
    $matchTime = $match->match_date ? $match->match_date->format('H:i') : '-';
    $venue = $match->venue ?? '-';

    // Position abbreviation
    function tsPositionAbbr($position) {
        if (!$position) return '-';
        $map = [
            'goalkeeper' => 'GK', 'gk' => 'GK',
            'defender' => 'DF', 'df' => 'DF', 'def' => 'DF',
            'midfielder' => 'MF', 'mf' => 'MF', 'mid' => 'MF',
            'forward' => 'FW', 'fw' => 'FW', 'fwd' => 'FW',
            'striker' => 'FW', 'st' => 'FW',
        ];
        $lower = strtolower(trim($position));
        return $map[$lower] ?? strtoupper(substr($position, 0, 2));
    }

    // Get official by role
    function tsGetOfficialByRole($officials, $role) {
        if (!$officials || $officials->isEmpty()) return '-';
        $official = $officials->first(function($o) use ($role) {
            return strtolower($o->role) === strtolower($role);
        });
        return $official ? $official->name : '-';
    }
@endphp

{{-- ===== HEADER SECTION ===== --}}
<table style="width: 100%; margin-bottom: 3px;">
    <tr>
        <td style="width: 60px; text-align: left; vertical-align: middle;">
            @if($competitionLogoBase64)
                <img src="{{ $competitionLogoBase64 }}" style="height: 50px; width: auto;" />
            @endif
        </td>
        <td style="text-align: center; vertical-align: middle;">
            <div style="font-size: 18px; font-weight: bold; color: #003366; letter-spacing: 2px;">TEAM SHEET / START LIST</div>
            <div style="font-size: 11px; color: #003366; margin-top: 2px;">{{ $competitionName }}</div>
            @if($match->competition && $match->competition->season)
                <div style="font-size: 9px; color: #555;">Season {{ $match->competition->season }}</div>
            @endif
        </td>
        <td style="width: 60px; text-align: right; vertical-align: middle;">
            @if($competitionLogoBase64)
                <img src="{{ $competitionLogoBase64 }}" style="height: 50px; width: auto;" />
            @endif
        </td>
    </tr>
</table>

{{-- Match details bar --}}
<table style="width: 100%; background-color: #003366; color: #ffffff; margin-bottom: 0;">
    <tr>
        <td style="padding: 4px 10px; font-size: 8px; text-align: left;">
            <strong>Match No:</strong> {{ $matchNo }}
        </td>
        <td style="padding: 4px 10px; font-size: 8px; text-align: center;">
            <strong>Date:</strong> {{ $matchDate }} &nbsp;&nbsp; <strong>Time:</strong> {{ $matchTime }}
        </td>
        <td style="padding: 4px 10px; font-size: 8px; text-align: right;">
            <strong>Stadium/Venue:</strong> {{ $venue }}
        </td>
    </tr>
</table>

{{-- Teams vs bar --}}
<table style="width: 100%; border: 2px solid #003366; margin-bottom: 8px; margin-top: 0;">
    <tr>
        <td style="width: 42%; text-align: right; padding: 8px 10px; vertical-align: middle;">
            @if(isset($homeLogoBase64))
                <img src="{{ $homeLogoBase64 }}" style="height: 35px; width: 35px; object-fit: contain; vertical-align: middle; margin-right: 6px;">
            @endif
            <span style="font-size: 13px; font-weight: bold; color: #003366;">{{ $homeTeamName }}</span>
        </td>
        <td style="width: 10%; text-align: center; padding: 8px 4px; vertical-align: middle;">
            <span style="font-size: 11px; font-weight: bold; color: #ffffff; background-color: #003366; padding: 3px 10px;">VS</span>
        </td>
        <td style="width: 42%; text-align: left; padding: 8px 10px; vertical-align: middle;">
            <span style="font-size: 13px; font-weight: bold; color: #003366;">{{ $awayTeamName }}</span>
            @if(isset($awayLogoBase64))
                <img src="{{ $awayLogoBase64 }}" style="height: 35px; width: 35px; object-fit: contain; vertical-align: middle; margin-left: 6px;">
            @endif
        </td>
    </tr>
</table>

{{-- ===== STARTING LINEUP ===== --}}
<div class="blue-bar">Starting Lineup</div>
<table style="width: 100%; margin-bottom: 0;">
    <tr>
        {{-- Home Team --}}
        <td style="width: 50%; vertical-align: top; padding-right: 3px;">
            <div class="blue-bar-light">{{ $homeTeamName }} (Home)</div>
            <table class="lineup-table">
                <thead>
                    <tr>
                        <th style="width: 30px; text-align: center;">#</th>
                        <th style="width: 30px; text-align: center;">Pos</th>
                        <th>Player Name</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($homeStarters as $lineup)
                        <tr>
                            <td style="text-align: center;">{{ $lineup->jersey_number }}</td>
                            <td style="text-align: center;">{{ tsPositionAbbr($lineup->position) }}</td>
                            <td>{{ $lineup->player->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align: center; color: #999; padding: 6px;">No lineup data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </td>
        {{-- Away Team --}}
        <td style="width: 50%; vertical-align: top; padding-left: 3px;">
            <div class="blue-bar-light">{{ $awayTeamName }} (Away)</div>
            <table class="lineup-table">
                <thead>
                    <tr>
                        <th style="width: 30px; text-align: center;">#</th>
                        <th style="width: 30px; text-align: center;">Pos</th>
                        <th>Player Name</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($awayStarters as $lineup)
                        <tr>
                            <td style="text-align: center;">{{ $lineup->jersey_number }}</td>
                            <td style="text-align: center;">{{ tsPositionAbbr($lineup->position) }}</td>
                            <td>{{ $lineup->player->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align: center; color: #999; padding: 6px;">No lineup data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </td>
    </tr>
</table>

{{-- ===== SUBSTITUTES ===== --}}
<div class="blue-bar" style="margin-top: 6px;">Substitutes</div>
<table style="width: 100%; margin-bottom: 0;">
    <tr>
        {{-- Home Subs --}}
        <td style="width: 50%; vertical-align: top; padding-right: 3px;">
            <div class="blue-bar-light">{{ $homeTeamName }} (Home)</div>
            <table class="lineup-table">
                <thead>
                    <tr>
                        <th style="width: 30px; text-align: center;">#</th>
                        <th style="width: 30px; text-align: center;">Pos</th>
                        <th>Player Name</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($homeSubs as $lineup)
                        <tr>
                            <td style="text-align: center;">{{ $lineup->jersey_number }}</td>
                            <td style="text-align: center;">{{ tsPositionAbbr($lineup->position) }}</td>
                            <td>{{ $lineup->player->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align: center; color: #999; padding: 6px;">No substitutes</td></tr>
                    @endforelse
                </tbody>
            </table>
        </td>
        {{-- Away Subs --}}
        <td style="width: 50%; vertical-align: top; padding-left: 3px;">
            <div class="blue-bar-light">{{ $awayTeamName }} (Away)</div>
            <table class="lineup-table">
                <thead>
                    <tr>
                        <th style="width: 30px; text-align: center;">#</th>
                        <th style="width: 30px; text-align: center;">Pos</th>
                        <th>Player Name</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($awaySubs as $lineup)
                        <tr>
                            <td style="text-align: center;">{{ $lineup->jersey_number }}</td>
                            <td style="text-align: center;">{{ tsPositionAbbr($lineup->position) }}</td>
                            <td>{{ $lineup->player->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align: center; color: #999; padding: 6px;">No substitutes</td></tr>
                    @endforelse
                </tbody>
            </table>
        </td>
    </tr>
</table>

{{-- ===== TEAM OFFICIALS ON THE BENCH ===== --}}
<div class="blue-bar" style="margin-top: 6px;">Team Officials on the Bench</div>
<table style="width: 100%; margin-bottom: 0;">
    <tr>
        {{-- Home Officials --}}
        <td style="width: 50%; vertical-align: top; padding-right: 3px;">
            <div class="blue-bar-light">{{ $homeTeamName }} (Home)</div>
            <table class="officials-table">
                <tr>
                    <td class="label-cell">Pengurus Pasukan</td>
                    <td>{{ $match->homeTeam->manager_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Ketua Jurulatih</td>
                    <td>{{ tsGetOfficialByRole($homeOfficials, 'head_coach') !== '-' ? tsGetOfficialByRole($homeOfficials, 'head_coach') : tsGetOfficialByRole($homeOfficials, 'ketua jurulatih') }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Penolong Jurulatih</td>
                    <td>{{ tsGetOfficialByRole($homeOfficials, 'assistant_coach') !== '-' ? tsGetOfficialByRole($homeOfficials, 'assistant_coach') : tsGetOfficialByRole($homeOfficials, 'penolong jurulatih') }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Fisioterapi</td>
                    <td>{{ tsGetOfficialByRole($homeOfficials, 'physiotherapist') !== '-' ? tsGetOfficialByRole($homeOfficials, 'physiotherapist') : tsGetOfficialByRole($homeOfficials, 'fisioterapi') }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Kitman</td>
                    <td>{{ tsGetOfficialByRole($homeOfficials, 'kitman') !== '-' ? tsGetOfficialByRole($homeOfficials, 'kitman') : tsGetOfficialByRole($homeOfficials, 'kit_manager') }}</td>
                </tr>
            </table>
        </td>
        {{-- Away Officials --}}
        <td style="width: 50%; vertical-align: top; padding-left: 3px;">
            <div class="blue-bar-light">{{ $awayTeamName }} (Away)</div>
            <table class="officials-table">
                <tr>
                    <td class="label-cell">Pengurus Pasukan</td>
                    <td>{{ $match->awayTeam->manager_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Ketua Jurulatih</td>
                    <td>{{ tsGetOfficialByRole($awayOfficials, 'head_coach') !== '-' ? tsGetOfficialByRole($awayOfficials, 'head_coach') : tsGetOfficialByRole($awayOfficials, 'ketua jurulatih') }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Penolong Jurulatih</td>
                    <td>{{ tsGetOfficialByRole($awayOfficials, 'assistant_coach') !== '-' ? tsGetOfficialByRole($awayOfficials, 'assistant_coach') : tsGetOfficialByRole($awayOfficials, 'penolong jurulatih') }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Fisioterapi</td>
                    <td>{{ tsGetOfficialByRole($awayOfficials, 'physiotherapist') !== '-' ? tsGetOfficialByRole($awayOfficials, 'physiotherapist') : tsGetOfficialByRole($awayOfficials, 'fisioterapi') }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Kitman</td>
                    <td>{{ tsGetOfficialByRole($awayOfficials, 'kitman') !== '-' ? tsGetOfficialByRole($awayOfficials, 'kitman') : tsGetOfficialByRole($awayOfficials, 'kit_manager') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- ===== MATCH OFFICIALS ===== --}}
<div class="blue-bar" style="margin-top: 6px;">Match Officials</div>
<table class="match-officials-table">
    <tr>
        <td class="label-cell">Match Commissioner</td>
        <td>{{ $match->match_commissioner ?? '-' }}</td>
        <td class="label-cell">Referee</td>
        <td>{{ $match->referee ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label-cell">Assistant Referee 1</td>
        <td>{{ $match->assistant_referee_1 ?? '-' }}</td>
        <td class="label-cell">Assistant Referee 2</td>
        <td>{{ $match->assistant_referee_2 ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label-cell">Fourth Official</td>
        <td>{{ $match->fourth_official ?? '-' }}</td>
        <td class="label-cell">&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
</table>

{{-- ===== SIGNATURES ===== --}}
<table style="width: 100%; margin-top: 25px;">
    <tr>
        <td style="width: 33%; text-align: center; vertical-align: bottom; padding: 0 10px;">
            <div style="margin-top: 30px; border-top: 1px solid #333; padding-top: 4px; font-size: 8px; color: #555;">
                Home Team Manager
            </div>
        </td>
        <td style="width: 34%; text-align: center; vertical-align: bottom; padding: 0 10px;">
            <div style="margin-top: 30px; border-top: 1px solid #333; padding-top: 4px; font-size: 8px; color: #555;">
                Match Commissioner
            </div>
        </td>
        <td style="width: 33%; text-align: center; vertical-align: bottom; padding: 0 10px;">
            <div style="margin-top: 30px; border-top: 1px solid #333; padding-top: 4px; font-size: 8px; color: #555;">
                Away Team Manager
            </div>
        </td>
    </tr>
</table>

{{-- ===== FOOTER ===== --}}
<table style="width: 100%; margin-top: 15px; border-top: 1px solid #ccd6e0;">
    <tr>
        <td style="text-align: center; padding-top: 5px; font-size: 7px; color: #666;">
            This is an official match document generated by the League Management System.
            &nbsp;&nbsp;|&nbsp;&nbsp; Generated: {{ now()->format('d/m/Y H:i:s') }}
        </td>
    </tr>
</table>

</body>
</html>
