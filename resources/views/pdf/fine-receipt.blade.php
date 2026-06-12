<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fine Payment Receipt</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #222;
            line-height: 1.4;
        }
        .blue-bar {
            background-color: #003366;
            color: #ffffff;
            padding: 6px 10px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px 10px;
            font-size: 10px;
            border: 1px solid #ccd6e0;
        }
        .info-table .label-cell {
            background-color: #e8eef5;
            font-weight: bold;
            color: #003366;
            width: 35%;
        }
        .paid-stamp {
            color: #006600;
            font-size: 28px;
            font-weight: bold;
            border: 4px solid #006600;
            padding: 6px 20px;
            display: inline-block;
            transform: rotate(-8deg);
            letter-spacing: 3px;
        }
    </style>
</head>
<body>

@php
    $teamName = $fine->team->name ?? '-';
    $competitionName = $fine->competition->name ?? '-';
    $receiptNo = 'JBFA-F-' . str_pad($fine->id, 6, '0', STR_PAD_LEFT);
    $playerName = $fine->player ? $fine->player->name : 'Denda Pasukan / Team Fine';
    $jerseyNo = $fine->player && $fine->player->jersey_number ? '#' . $fine->player->jersey_number : '';
@endphp

{{-- ===== HEADER ===== --}}
<table style="width: 100%; margin-bottom: 8px;">
    <tr>
        <td style="width: 70px; text-align: left; vertical-align: middle;">
            @if($jbfaLogoBase64)
                <img src="{{ $jbfaLogoBase64 }}" style="height: 60px; width: auto;" />
            @endif
        </td>
        <td style="text-align: center; vertical-align: middle;">
            <div style="font-size: 14px; font-weight: bold; color: #003366; letter-spacing: 1px;">PERSATUAN BOLA SEPAK JOHOR BAHRU</div>
            <div style="font-size: 11px; color: #003366; margin-top: 1px;">Johor Bahru Football Association (JBFA)</div>
            <div style="font-size: 18px; font-weight: bold; color: #cc0000; margin-top: 6px; letter-spacing: 2px;">RESIT PEMBAYARAN DENDA</div>
            <div style="font-size: 10px; color: #555;">FINE PAYMENT RECEIPT</div>
        </td>
        <td style="width: 70px; text-align: right; vertical-align: middle;">
            @if($competitionLogoBase64)
                <img src="{{ $competitionLogoBase64 }}" style="height: 60px; width: auto;" />
            @endif
        </td>
    </tr>
</table>

{{-- Receipt number & date bar --}}
<table style="width: 100%; background-color: #003366; color: #ffffff; margin-bottom: 12px;">
    <tr>
        <td style="padding: 5px 12px; font-size: 10px; text-align: left;">
            <strong>No. Resit / Receipt No:</strong> {{ $receiptNo }}
        </td>
        <td style="padding: 5px 12px; font-size: 10px; text-align: right;">
            <strong>Tarikh / Date:</strong> {{ $fine->paid_at ? $fine->paid_at->format('d/m/Y H:i') : $fine->created_at->format('d/m/Y H:i') }}
        </td>
    </tr>
</table>

{{-- ===== PAYMENT STATUS STAMP ===== --}}
<table style="width: 100%; margin-bottom: 12px;">
    <tr>
        <td style="text-align: center;">
            <span class="paid-stamp">TELAH DIBAYAR / PAID</span>
        </td>
    </tr>
</table>

{{-- ===== FINE DETAILS ===== --}}
<div class="blue-bar">Maklumat Denda / Fine Information</div>
<table class="info-table" style="margin-bottom: 10px;">
    <tr>
        <td class="label-cell">Pasukan / Team</td>
        <td>{{ $teamName }}</td>
    </tr>
    <tr>
        <td class="label-cell">Pemain / Player</td>
        <td>{{ $playerName }} {{ $jerseyNo }}</td>
    </tr>
    <tr>
        <td class="label-cell">Pertandingan / Competition</td>
        <td>{{ $competitionName }}</td>
    </tr>
    @if($fine->matchGame)
    <tr>
        <td class="label-cell">Perlawanan / Match</td>
        <td>{{ $fine->matchGame->match_code ?? '' }} — {{ $fine->matchGame->homeTeam->name ?? '' }} vs {{ $fine->matchGame->awayTeam->name ?? '' }}</td>
    </tr>
    @endif
    <tr>
        <td class="label-cell">Jenis Denda / Fine Type</td>
        <td><strong>{{ $fine->fineTypeLabel() }}</strong></td>
    </tr>
    @if($fine->description)
    <tr>
        <td class="label-cell">Keterangan / Description</td>
        <td>{{ $fine->description }}</td>
    </tr>
    @endif
</table>

{{-- ===== PAYMENT DETAILS ===== --}}
<div class="blue-bar">Maklumat Pembayaran / Payment Details</div>
<table class="info-table" style="margin-bottom: 10px;">
    <tr>
        <td class="label-cell">Jumlah Denda / Fine Amount</td>
        <td><strong style="font-size: 14px; color: #cc0000;">RM {{ number_format($fine->amount, 2) }}</strong></td>
    </tr>
    <tr>
        <td class="label-cell">Kaedah Bayaran / Payment Method</td>
        <td>{{ strtoupper($fine->payment_method ?? 'MANUAL') }}</td>
    </tr>
    <tr>
        <td class="label-cell">Status</td>
        <td><strong style="color: #006600;">DIBAYAR / PAID</strong></td>
    </tr>
    @if($fine->transaction_id)
    <tr>
        <td class="label-cell">ID Transaksi / Transaction ID</td>
        <td>{{ $fine->transaction_id }}</td>
    </tr>
    @endif
    @if($fine->paid_at)
    <tr>
        <td class="label-cell">Tarikh Bayar / Paid Date</td>
        <td>{{ $fine->paid_at->format('d/m/Y H:i:s') }}</td>
    </tr>
    @endif
    <tr>
        <td class="label-cell">Dikeluarkan Oleh / Issued By</td>
        <td>{{ $fine->issuedByUser->name ?? '-' }}</td>
    </tr>
</table>

{{-- ===== FOOTER ===== --}}
<table style="width: 100%; margin-top: 30px; border-top: 2px solid #003366;">
    <tr>
        <td style="text-align: center; padding-top: 15px;">
            <div style="font-size: 11px; font-weight: bold; color: #003366; letter-spacing: 1px; margin-bottom: 8px;">
                COMPUTER GENERATED RECEIPT DOES NOT REQUIRE A SIGNATURE
            </div>
            <div style="font-size: 9px; color: #003366; margin-bottom: 4px;">
                RESIT DIJANA KOMPUTER TIDAK MEMERLUKAN TANDATANGAN
            </div>
            <div style="font-size: 8px; color: #666; margin-top: 10px;">
                Dijana pada / Generated: {{ now()->format('d/m/Y H:i:s') }}
            </div>
            <div style="font-size: 7px; color: #999; margin-top: 3px;">
                PERSATUAN BOLA SEPAK JOHOR BAHRU — Johor Bahru Football League Management System
            </div>
        </td>
    </tr>
</table>

</body>
</html>
