{{-- ===== TEAM JERSEY COLOURS (visual only, Home left / Away right) ===== --}}
<div class="blue-bar" style="margin-top: 6px;">Team Jersey Colours / Warna Jersi</div>
<table style="width:100%; border-collapse:collapse; font-size:9px; margin-bottom:4px;">
    <tr>
        @foreach([['name' => $homeTeamName, 'label' => 'Home', 'j' => $homeJersey], ['name' => $awayTeamName, 'label' => 'Away', 'j' => $awayJersey]] as $row)
            @php $j = $row['j']; @endphp
            <td style="width:50%; vertical-align:top; padding:3px 6px; text-align:center; {{ $loop->first ? 'border-right:1px solid #ccd6e0;' : '' }}">
                <div style="font-weight:bold; background:#f0f4f8; padding:3px 4px; margin-bottom:3px;">{{ $row['name'] }} ({{ $row['label'] }})</div>
                @if($j)
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="width:50%; text-align:center; vertical-align:top;">
                                @include('matches.partials.kit-svg', ['shirt' => $j->shirt_hex, 'shorts' => $j->shorts_hex, 'socks' => $j->socks_hex, 'w' => 40, 'caption' => 'Player', 'pdf' => true])
                            </td>
                            <td style="width:50%; text-align:center; vertical-align:top;">
                                @include('matches.partials.kit-svg', ['shirt' => $j->gk_shirt_hex, 'shorts' => $j->gk_shorts_hex, 'socks' => $j->gk_socks_hex, 'w' => 36, 'caption' => 'Goalkeeper', 'pdf' => true])
                            </td>
                        </tr>
                    </table>
                @else
                    <em style="color:#888;">Not submitted</em>
                @endif
            </td>
        @endforeach
    </tr>
</table>
