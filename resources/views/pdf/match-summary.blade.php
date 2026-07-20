<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Match Summary Report</title>
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
        .full-width {
            width: 100%;
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

        /* ---- Event annotations ---- */
        .evt-goal { color: #006600; font-weight: bold; font-size: 7px; }
        .evt-og { color: #cc0000; font-weight: bold; font-size: 7px; }
        .evt-yellow { color: #cc9900; font-weight: bold; font-size: 7px; }
        .evt-red { color: #cc0000; font-weight: bold; font-size: 7px; }
        .evt-sub { color: #0066aa; font-size: 7px; }
        .u23-badge { background: #ffc107; color: #000; font-size: 5px; padding: 0px 2px; border-radius: 2px; font-weight: bold; vertical-align: middle; }
        .evt-penalty { color: #006600; font-weight: bold; font-size: 7px; }

        /* ---- Footer ---- */
        .footer-text {
            font-size: 7px;
            color: #666;
        }
        .legend-text {
            font-size: 7px;
            color: #444;
        }
    </style>
</head>
<body>

@php
    $competitionName = $match->competition->name ?? 'Football League';
    $homeTeamName = $match->homeTeam->name ?? 'Home Team';
    $awayTeamName = $match->awayTeam->name ?? 'Away Team';
    $matchNo = $match->match_code ?? $match->matchday ?? '-';
    $matchDate = $match->match_date ? $match->match_date->format('d/m/Y') : '-';
    $matchTime = $match->match_date ? $match->match_date->format('H:i') : '-';
    $venue = $match->venue ?? '-';

    // Helper to get event annotations for a player
    function getPlayerAnnotations($playerId, $playerEventsMap) {
        if (!isset($playerEventsMap[$playerId])) return '';
        $annotations = [];
        foreach ($playerEventsMap[$playerId] as $evt) {
            $min = $evt->minute . "'";
            if ($evt->extra_time_minute) {
                $min = $evt->minute . "+" . $evt->extra_time_minute . "'";
            }
            switch ($evt->event_type) {
                case 'goal':
                    $annotations[] = '<span class="evt-goal">[G ' . $min . ']</span>';
                    break;
                case 'own_goal':
                    $annotations[] = '<span class="evt-og">[OG ' . $min . ']</span>';
                    break;
                case 'penalty_scored':
                    $annotations[] = '<span class="evt-penalty">[P ' . $min . ']</span>';
                    break;
                case 'penalty_missed':
                    $annotations[] = '<span class="evt-og">[PM ' . $min . ']</span>';
                    break;
                case 'yellow_card':
                    $annotations[] = '<span class="evt-yellow">[YC ' . $min . ']</span>';
                    break;
                case 'red_card':
                    $annotations[] = '<span class="evt-red">[RC ' . $min . ']</span>';
                    break;
                case 'substitution_out':
                    $annotations[] = '<span class="evt-sub">[&lt;&lt; ' . $min . ']</span>';
                    break;
                case 'substitution_in':
                    $annotations[] = '<span class="evt-sub">[&gt;&gt; ' . $min . ']</span>';
                    break;
                case 'substitution':
                    $annotations[] = '<span class="evt-sub">[&lt;&lt; ' . $min . ']</span>';
                    break;
            }
        }
        return implode(' ', $annotations);
    }

    // Get official by role helper
    function getOfficialByRole($officials, $role) {
        if (!$officials || $officials->isEmpty()) return '-';
        $official = $officials->first(function($o) use ($role) {
            return strtolower($o->role) === strtolower($role);
        });
        return $official ? $official->name : '-';
    }

    // Position abbreviation
    function posAbbr($position) {
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
            <div style="font-size: 18px; font-weight: bold; color: #003366; letter-spacing: 2px;">MATCH SUMMARY</div>
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
<table style="width: 100%; background-color: #e8eef5; margin-bottom: 8px;">
    <tr>
        <td style="padding: 3px 10px; font-size: 8px; color: #003366; text-align: left;">
            <strong>Duration:</strong> 90 Minutes
        </td>
        <td style="padding: 3px 10px; font-size: 8px; color: #003366; text-align: center;">
            <strong>Weather:</strong> {{ $match->notes ?? '-' }}
        </td>
        <td style="padding: 3px 10px; font-size: 8px; color: #003366; text-align: right;">
            <strong>Attendance:</strong> -
        </td>
    </tr>
</table>

{{-- ===== SCORE SECTION ===== --}}
<table style="width: 100%; border: 2px solid #003366; margin-bottom: 5px;">
    <tr>
        <td style="width: 35%; text-align: right; padding: 8px 8px; vertical-align: middle;">
            @if(isset($homeLogoBase64))
                <img src="{{ $homeLogoBase64 }}" style="height: 35px; width: 35px; object-fit: contain; vertical-align: middle; margin-right: 6px;">
            @endif
            <span style="font-size: 13px; font-weight: bold; color: #003366;">{{ $homeTeamName }}</span>
        </td>
        <td style="width: 7%; text-align: center; padding: 8px 4px; vertical-align: middle;">
            <span style="font-size: 22px; font-weight: bold; color: #003366;">{{ $match->home_score ?? 0 }}</span>
        </td>
        <td style="width: 10%; text-align: center; padding: 8px 4px; vertical-align: middle;">
            <div style="font-size: 8px; font-weight: bold; color: #ffffff; background-color: #003366; padding: 3px 6px; letter-spacing: 1px;">FULL TIME</div>
        </td>
        <td style="width: 7%; text-align: center; padding: 8px 4px; vertical-align: middle;">
            <span style="font-size: 22px; font-weight: bold; color: #003366;">{{ $match->away_score ?? 0 }}</span>
        </td>
        <td style="width: 35%; text-align: left; padding: 8px 8px; vertical-align: middle;">
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
        {{-- Home Team Starting Lineup --}}
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
                            <td style="text-align: center;">{{ posAbbr($lineup->position) }}</td>
                            <td>
                                {{ $lineup->player->name ?? '-' }} @if($lineup->player && $lineup->player->is_u23)<span class="u23-badge">U23</span>@endif
                                {!! getPlayerAnnotations($lineup->player_id, $playerEventsMap) !!}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align: center; color: #999; padding: 6px;">No lineup data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </td>
        {{-- Away Team Starting Lineup --}}
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
                            <td style="text-align: center;">{{ posAbbr($lineup->position) }}</td>
                            <td>
                                {{ $lineup->player->name ?? '-' }} @if($lineup->player && $lineup->player->is_u23)<span class="u23-badge">U23</span>@endif
                                {!! getPlayerAnnotations($lineup->player_id, $playerEventsMap) !!}
                            </td>
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
        {{-- Home Substitutes --}}
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
                            <td style="text-align: center;">{{ posAbbr($lineup->position) }}</td>
                            <td>
                                {{ $lineup->player->name ?? '-' }} @if($lineup->player && $lineup->player->is_u23)<span class="u23-badge">U23</span>@endif
                                {!! getPlayerAnnotations($lineup->player_id, $playerEventsMap) !!}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align: center; color: #999; padding: 6px;">No substitutes</td></tr>
                    @endforelse
                </tbody>
            </table>
        </td>
        {{-- Away Substitutes --}}
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
                            <td style="text-align: center;">{{ posAbbr($lineup->position) }}</td>
                            <td>
                                {{ $lineup->player->name ?? '-' }} @if($lineup->player && $lineup->player->is_u23)<span class="u23-badge">U23</span>@endif
                                {!! getPlayerAnnotations($lineup->player_id, $playerEventsMap) !!}
                            </td>
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
        {{-- Home Team Officials --}}
        <td style="width: 50%; vertical-align: top; padding-right: 3px;">
            <div class="blue-bar-light">{{ $homeTeamName }} (Home)</div>
            <table class="officials-table">
                <tr>
                    <td class="label-cell">Pengurus Pasukan</td>
                    <td>{{ $match->homeTeam->manager_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Ketua Jurulatih</td>
                    <td>{{ getOfficialByRole($homeOfficials, 'head_coach') !== '-' ? getOfficialByRole($homeOfficials, 'head_coach') : getOfficialByRole($homeOfficials, 'ketua jurulatih') }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Penolong Jurulatih</td>
                    <td>{{ getOfficialByRole($homeOfficials, 'assistant_coach') !== '-' ? getOfficialByRole($homeOfficials, 'assistant_coach') : getOfficialByRole($homeOfficials, 'penolong jurulatih') }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Fisioterapi</td>
                    <td>{{ getOfficialByRole($homeOfficials, 'physiotherapist') !== '-' ? getOfficialByRole($homeOfficials, 'physiotherapist') : getOfficialByRole($homeOfficials, 'fisioterapi') }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Kitman</td>
                    <td>{{ getOfficialByRole($homeOfficials, 'kitman') !== '-' ? getOfficialByRole($homeOfficials, 'kitman') : getOfficialByRole($homeOfficials, 'kit_manager') }}</td>
                </tr>
            </table>
        </td>
        {{-- Away Team Officials --}}
        <td style="width: 50%; vertical-align: top; padding-left: 3px;">
            <div class="blue-bar-light">{{ $awayTeamName }} (Away)</div>
            <table class="officials-table">
                <tr>
                    <td class="label-cell">Pengurus Pasukan</td>
                    <td>{{ $match->awayTeam->manager_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Ketua Jurulatih</td>
                    <td>{{ getOfficialByRole($awayOfficials, 'head_coach') !== '-' ? getOfficialByRole($awayOfficials, 'head_coach') : getOfficialByRole($awayOfficials, 'ketua jurulatih') }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Penolong Jurulatih</td>
                    <td>{{ getOfficialByRole($awayOfficials, 'assistant_coach') !== '-' ? getOfficialByRole($awayOfficials, 'assistant_coach') : getOfficialByRole($awayOfficials, 'penolong jurulatih') }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Fisioterapi</td>
                    <td>{{ getOfficialByRole($awayOfficials, 'physiotherapist') !== '-' ? getOfficialByRole($awayOfficials, 'physiotherapist') : getOfficialByRole($awayOfficials, 'fisioterapi') }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Kitman</td>
                    <td>{{ getOfficialByRole($awayOfficials, 'kitman') !== '-' ? getOfficialByRole($awayOfficials, 'kitman') : getOfficialByRole($awayOfficials, 'kit_manager') }}</td>
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



{{-- ===== MATCH REMARKS ===== --}}
@if($match->match_remarks)
<div class="blue-bar" style="margin-top: 6px;">Match Remarks / Report Notes</div>
<table style="width: 100%; border: 1px solid #ccd6e0; margin-bottom: 6px;">
    <tr>
        <td style="padding: 5px 8px; font-size: 8px; line-height: 1.5;">
            {!! nl2br(e($match->match_remarks)) !!}
        </td>
    </tr>
</table>
@endif

@include('pdf.partials.jersey-block')

{{-- ===== E-SIGNATURES ===== --}}
<div class="blue-bar" style="margin-top: 4px;">E-Signature Confirmation / Pengesahan Tandatangan</div>
<table style="width: 100%; border-collapse: collapse; margin-bottom: 3px; table-layout: fixed;">
    <tr>
        @php
            $sigRolesData = [
                ["role" => "head_referee", "label" => "Head Referee", "label_ms" => "Pengadil Utama", "team" => null],
                ["role" => "home_team_rep", "label" => "Home Team Rep", "label_ms" => "Wakil Tuan Rumah", "team" => $match->homeTeam->name ?? "-"],
                ["role" => "away_team_rep", "label" => "Away Team Rep", "label_ms" => "Wakil Pelawat", "team" => $match->awayTeam->name ?? "-"],
                ["role" => "match_commissioner", "label" => "Match Commissioner", "label_ms" => "Pesuruhjaya", "team" => null],
            ];
        @endphp
        @foreach($sigRolesData as $idx => $sigInfo)
            @php $sig = $signatures[$sigInfo["role"]] ?? null; @endphp
            <td style="width: 25%; vertical-align: top; padding: 0; border: 1px solid #ccd6e0;">
                {{-- Row 1: Role Title (fixed height) --}}
                <div style="font-size: 7px; font-weight: bold; color: #003366; text-align: center; background-color: #e8eef5; padding: 2px 2px; line-height: 1.3;">
                    {{ $idx + 1 }}. {{ $sigInfo["label"] }}
                </div>
                {{-- Row 2: Role label MS + Team (fixed height) --}}
                <div style="height: 18px; text-align: center; padding: 1px 2px;">
                    <div style="font-size: 6px; color: #555;">{{ $sigInfo["label_ms"] }}</div>
                    @if($sigInfo["team"])
                        <div style="font-size: 6px; color: #003366; font-weight: bold;">{{ strtoupper($sigInfo["team"]) }}</div>
                    @endif
                </div>
                {{-- Row 3: Name (fixed height) --}}
                <div style="height: 14px; font-size: 7px; padding: 1px 4px; overflow: hidden;">
                    <span style="color: #555;">Name:</span>
                    <strong>{{ $sig && $sig->confirmed ? $sig->name : "___________" }}</strong>
                </div>
                {{-- Row 4: Signature box (fixed height) --}}
                <div style="height: 18px; border: 1px solid #ccd6e0; margin: 1px 4px; text-align: center; overflow: hidden;">
                    @if($sig && $sig->signature_data && str_starts_with($sig->signature_data, "data:image"))
                        <img src="{{ $sig->signature_data }}" style="max-height: 16px; max-width: 90%;">
                    @endif
                </div>
                {{-- Row 5: Date (fixed height) --}}
                <div style="height: 12px; font-size: 6px; padding: 1px 4px; color: #555;">
                    {{ $sig && $sig->confirmed && $sig->signed_at ? $sig->signed_at->format("d/m/Y H:i") : "Date: ___________" }}
                </div>
                {{-- Row 6: Status (fixed height) --}}
                <div style="height: 12px; font-size: 6px; text-align: center; padding: 0 2px;">
                    @if($sig && $sig->confirmed)
                        <span style="color: #006600;">&#10004; Confirmed</span>
                    @else
                        <span style="color: #cc0000;">&#10008; Pending</span>
                    @endif
                </div>
                {{-- Row 7: Remarks --}}
                <div style="font-size: 6px; padding: 1px 4px; color: #333; border-top: 1px dotted #ccd6e0;">
                    <span style="color: #888;">Remarks:</span>
                    {{ $sig && trim((string) $sig->remarks) !== '' ? $sig->remarks : 'No remarks' }}
                </div>
            </td>
        @endforeach
    </tr>
</table>

{{-- ===== FOOTER SECTION ===== --}}
<table style="width: 100%; margin-top: 5px;">
    <tr>
        <td style="width: 50%; vertical-align: bottom;">
            <div style="font-size: 7px; color: #666;">
                I confirm that all match details, scores, events, and remarks recorded above are correct and acknowledged by the undersigned parties.
            </div>
        </td>
        <td style="width: 50%; text-align: right; vertical-align: bottom;">
            <div style="font-size: 7px; color: #666; margin-bottom: 3px;">
                This is an official match document generated by the League Management System.
            </div>
            <div style="font-size: 7px; color: #666;">
                Generated: {{ now()->format('d/m/Y H:i:s') }}
            </div>
        </td>
    </tr>
</table>

{{-- Legend --}}
<table style="width: 100%; margin-top: 4px; border-top: 1px solid #ccd6e0;">
    <tr>
        <td style="padding-top: 4px;">
            <div class="legend-text">
                <strong>Legend:</strong>
                G - Goal &nbsp;&nbsp;
                OG - Own Goal &nbsp;&nbsp;
                P - Penalty &nbsp;&nbsp;
                PS - Penalty Shoot Out &nbsp;&nbsp;
                BM - Before Match &nbsp;&nbsp;
                HT - During Half Time &nbsp;&nbsp;
                EAM - Extra Time After Match &nbsp;&nbsp;
                EHT - Extra Time Half Time &nbsp;&nbsp;
                YC - Yellow Card &nbsp;&nbsp;
                RC - Red Card &nbsp;&nbsp;
                &lt;&lt; - Substituted Out &nbsp;&nbsp;
                &gt;&gt; - Substituted In
            </div>
        </td>
    </tr>
</table>

</body>
</html>
