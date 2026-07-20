<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Surat Kelayakan</title>
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
        .approved-stamp {
            color: #006600;
            font-size: 26px;
            font-weight: bold;
            border: 4px solid #006600;
            padding: 6px 18px;
            display: inline-block;
            transform: rotate(-8deg);
            letter-spacing: 3px;
        }
        .letter-body {
            font-size: 11px;
            line-height: 1.8;
            text-align: justify;
        }
        .letter-body p {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

@php
    $refNo = 'JBFA-EL-' . str_pad($team->id, 6, '0', STR_PAD_LEFT);
    $approvedDate = $team->statusLogs
        ? $team->statusLogs->where('new_status', 'approved')->sortByDesc('created_at')->first()
        : null;
    $approvedAt = $approvedDate ? $approvedDate->created_at : now();
    $competitionName = $team->competition->name ?? 'JBFA Football League';
    $season = $team->competition->season ?? date('Y');
    $competitionNameMalay = match($team->competition_id) {
        2 => 'LIGA SUPER JBFA',
        3 => 'LIGA PERDANA JBFA',
        4 => 'LIGA DIVISYEN JBFA',
        5 => 'PIALA FA JBFA',
        default => $competitionName,
    };
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
            
            <div style="font-size: 20px; font-weight: bold; color: #003366; margin-top: 6px; letter-spacing: 2px;">SURAT KELAYAKAN</div>
            
        </td>
        <td style="width: 70px; text-align: right; vertical-align: middle;">
            @if($competitionLogoBase64)
                <img src="{{ $competitionLogoBase64 }}" style="height: 60px; width: auto;" />
            @endif
        </td>
    </tr>
</table>

{{-- Reference number & date bar --}}
<table style="width: 100%; background-color: #003366; color: #ffffff; margin-bottom: 12px;">
    <tr>
        <td style="padding: 5px 12px; font-size: 10px; text-align: left;">
            <strong>No. Rujukan:</strong> {{ $refNo }}
        </td>
        <td style="padding: 5px 12px; font-size: 10px; text-align: right;">
            <strong>Tarikh Kelulusan:</strong> {{ $approvedAt->format('d/m/Y') }}
        </td>
    </tr>
</table>

{{-- ===== APPROVED STAMP ===== --}}
<table style="width: 100%; margin-bottom: 12px;">
    <tr>
        <td style="text-align: center;">
            <span class="approved-stamp">DILULUSKAN</span>
        </td>
    </tr>
</table>

{{-- ===== TEAM DETAILS ===== --}}
<div class="blue-bar">Maklumat Pasukan</div>
<table class="info-table" style="margin-bottom: 12px;">
    <tr>
        <td class="label-cell">Nama Pasukan</td>
        <td><strong>{{ $team->name }}</strong></td>
    </tr>
    <tr>
        <td class="label-cell">Nama Singkatan</td>
        <td>{{ $team->short_name }}</td>
    </tr>
    <tr>
        <td class="label-cell">Pertandingan</td>
        <td>{{ $competitionName }} &mdash; Season {{ $season }}</td>
    </tr>
    @if($team->group)
    <tr>
        <td class="label-cell">Kumpulan</td>
        <td>{{ $team->group->name }}</td>
    </tr>
    @endif
    <tr>
        <td class="label-cell">Pengurus Pasukan</td>
        <td>{{ $team->manager_name }}</td>
    </tr>
    <tr>
        <td class="label-cell">E-mel</td>
        <td>{{ $team->contact_email }}</td>
    </tr>
    @if($team->contact_phone)
    <tr>
        <td class="label-cell">No. Telefon</td>
        <td>{{ $team->contact_phone }}</td>
    </tr>
    @endif
    <tr>
        <td class="label-cell">Status</td>
        <td><strong style="color: #006600;">DILULUSKAN</strong></td>
    </tr>
    <tr>
        <td class="label-cell">Tarikh Kelulusan</td>
        <td>{{ $approvedAt->format('d/m/Y') }}</td>
    </tr>
</table>

{{-- ===== LETTER BODY ===== --}}
<div class="blue-bar">Pengesahan Kelayakan</div>
<div style="padding: 12px; border: 1px solid #ccd6e0; margin-bottom: 12px;">
    <div class="letter-body">
        <p>
            Dengan ini disahkan bahawa pasukan <strong>{{ $team->name }}</strong> telah memenuhi semua syarat dan keperluan
            yang ditetapkan oleh Persatuan Bola Sepak Johor Bahru (JBFA) untuk menyertai
            <strong>{{ $competitionNameMalay }} tahun {{ $season }}</strong>.
        </p>

        <p>
            Pasukan ini layak untuk menyertai semua perlawanan dan aktiviti rasmi di bawah pertandingan tersebut
            tertakluk kepada peraturan dan undang-undang yang berkuatkuasa.
        </p>

    </div>
</div>

{{-- ===== CONDITIONS ===== --}}
<div class="blue-bar">Syarat-Syarat</div>
<div style="padding: 10px; border: 1px solid #ccd6e0; margin-bottom: 12px; font-size: 9px; line-height: 1.6;">
    <table style="width: 100%;">
        <tr>
            <td style="vertical-align: top; padding: 2px 6px; width: 5%;">1.</td>
            <td style="padding: 2px 6px;">Pasukan mestilah mematuhi semua peraturan Liga JBFA sepanjang pertandingan.</td>
        </tr>
        <tr>
            <td style="vertical-align: top; padding: 2px 6px;">2.</td>
            <td style="padding: 2px 6px;">Semua pemain &amp; pegawai mestilah berdaftar secara rasmi dalam sistem JBFA.</td>
        </tr>
        <tr>
            <td style="vertical-align: top; padding: 2px 6px;">3.</td>
            <td style="padding: 2px 6px;">Bayaran yuran liga hendaklah dijelaskan pada atau selewat-lewatnya pada 12 Julai 2026.</td>
        </tr>
        <tr>
            <td style="vertical-align: top; padding: 2px 6px;">4.</td>
            <td style="padding: 2px 6px;">Borang pendaftaran pemain mestilah diselesaikan selewat-lewatnya pada 20 Julai 2026.</td>
        </tr>
        <tr>
            <td style="vertical-align: top; padding: 2px 6px;">5.</td>
            <td style="padding: 2px 6px;">JBFA berhak membatalkan kelayakan ini jika pasukan melanggar sebarang syarat &amp; garis panduan yang ditetapkan.</td>
        </tr>
    </table>
</div>


{{-- ===== FOOTER ===== --}}
<table style="width: 100%; margin-top: 20px; border-top: 2px solid #003366;">
    <tr>
        <td style="text-align: center; padding-top: 15px;">
            <div style="font-size: 11px; font-weight: bold; color: #003366; letter-spacing: 1px; margin-bottom: 8px;">
                SURAT KELAYAKAN DIJANA KOMPUTER DAN TIDAK MEMERLUKAN TANDATANGAN
            </div>

            <div style="font-size: 8px; color: #666; margin-top: 10px;">
                Dijana pada: {{ now()->format('d/m/Y H:i:s') }}
            </div>
            <div style="font-size: 7px; color: #999; margin-top: 3px;">
                PERSATUAN BOLA SEPAK JOHOR BAHRU &mdash; Sistem Pengurusan Liga JBFA
            </div>
        </td>
    </tr>
</table>

</body>
</html>
