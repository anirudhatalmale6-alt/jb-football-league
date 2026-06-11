<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt</title>
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
        .fee-table {
            width: 100%;
            border-collapse: collapse;
        }
        .fee-table th {
            background-color: #003366;
            color: #ffffff;
            padding: 5px 10px;
            font-size: 9px;
            text-transform: uppercase;
            text-align: left;
            border: 1px solid #002244;
        }
        .fee-table td {
            padding: 5px 10px;
            font-size: 10px;
            border: 1px solid #ccd6e0;
        }
        .fee-table tr:nth-child(even) td {
            background-color: #f4f7fa;
        }
        .fee-table .total-row td {
            background-color: #003366;
            color: #ffffff;
            font-weight: bold;
            font-size: 11px;
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
        .pending-stamp {
            color: #cc6600;
            font-size: 28px;
            font-weight: bold;
            border: 4px solid #cc6600;
            padding: 6px 20px;
            display: inline-block;
            transform: rotate(-8deg);
            letter-spacing: 3px;
        }
    </style>
</head>
<body>

@php
    $teamName = $payment->team->name ?? '-';
    $competitionName = $payment->competition->name ?? '-';
    $receiptNo = 'JBFA-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);
    $registrationFee = $payment->competition->registration_fee ?? 0;
    $securityDeposit = $payment->competition->security_deposit ?? 0;
    $matchdayFee = $payment->competition->matchday_fee ?? 0;
    $annualFee = 50.00;
    $totalFee = $registrationFee + $securityDeposit + $matchdayFee + $annualFee;
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
            <div style="font-size: 20px; font-weight: bold; color: #003366; margin-top: 6px; letter-spacing: 2px;">RESIT PEMBAYARAN</div>
            <div style="font-size: 10px; color: #555;">PAYMENT RECEIPT</div>
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
            <strong>{{ __('app.receipt_no') }}:</strong> {{ $receiptNo }}
        </td>
        <td style="padding: 5px 12px; font-size: 10px; text-align: right;">
            <strong>{{ __('app.receipt_date') }}:</strong> {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : $payment->created_at->format('d/m/Y H:i') }}
        </td>
    </tr>
</table>

{{-- ===== PAYMENT STATUS STAMP ===== --}}
<table style="width: 100%; margin-bottom: 12px;">
    <tr>
        <td style="text-align: center;">
            @if($payment->status === 'paid')
                <span class="paid-stamp">TELAH DIBAYAR / PAID</span>
            @elseif($payment->status === 'pending')
                <span class="pending-stamp">BELUM DIBAYAR / PENDING</span>
            @endif
        </td>
    </tr>
</table>

{{-- ===== TEAM & REGISTRATION DETAILS ===== --}}
<div class="blue-bar">Maklumat Pasukan / Team Information</div>
<table class="info-table" style="margin-bottom: 10px;">
    <tr>
        <td class="label-cell">{{ __('app.receipt_team') }}</td>
        <td>{{ $teamName }}</td>
    </tr>
    <tr>
        <td class="label-cell">{{ __('app.receipt_competition') }}</td>
        <td>{{ $competitionName }} — Season {{ $payment->competition->season ?? '2026' }}</td>
    </tr>
    <tr>
        <td class="label-cell">{{ __('app.receipt_registered_by') }}</td>
        <td>{{ $payment->user->name ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label-cell">{{ __('app.receipt_club_email') }}</td>
        <td>{{ $payment->team->contact_email ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label-cell">{{ __('app.receipt_club_phone') }}</td>
        <td>{{ $payment->team->contact_phone ?? '-' }}</td>
    </tr>
    @if($payment->team->manager_name)
    <tr>
        <td class="label-cell">Pengurus Kelab / Club Manager</td>
        <td>{{ $payment->team->manager_name }}</td>
    </tr>
    @endif
</table>

{{-- ===== FEE BREAKDOWN ===== --}}
<div class="blue-bar">{{ __('app.receipt_fee_breakdown') }} / Fee Breakdown</div>
<table class="fee-table" style="margin-bottom: 10px;">
    <thead>
        <tr>
            <th style="width: 10%;">#</th>
            <th>Perkara / Description</th>
            <th style="width: 25%; text-align: right;">Jumlah / Amount (RM)</th>
        </tr>
    </thead>
    <tbody>
        @if($registrationFee > 0)
        <tr>
            <td>1</td>
            <td>{{ __('app.receipt_registration_fee') }}</td>
            <td style="text-align: right;">{{ number_format($registrationFee, 2) }}</td>
        </tr>
        @endif
        @if($securityDeposit > 0)
        <tr>
            <td>{{ $registrationFee > 0 ? 2 : 1 }}</td>
            <td>{{ __('app.receipt_security_deposit') }}</td>
            <td style="text-align: right;">{{ number_format($securityDeposit, 2) }}</td>
        </tr>
        @endif
        @if($matchdayFee > 0)
        @php $rowNum = ($registrationFee > 0 ? 1 : 0) + ($securityDeposit > 0 ? 1 : 0) + 1; @endphp
        <tr>
            <td>{{ $rowNum }}</td>
            <td>{{ __('app.receipt_matchday_fee') }}</td>
            <td style="text-align: right;">{{ number_format($matchdayFee, 2) }}</td>
        </tr>
        @endif
        @php $annualRow = ($registrationFee > 0 ? 1 : 0) + ($securityDeposit > 0 ? 1 : 0) + ($matchdayFee > 0 ? 1 : 0) + 1; @endphp
        <tr>
            <td>{{ $annualRow }}</td>
            <td>{{ __('app.receipt_annual_fee') }}</td>
            <td style="text-align: right;">{{ number_format($annualFee, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td colspan="2" style="text-align: right;">{{ __('app.receipt_total') }} / TOTAL</td>
            <td style="text-align: right;">RM {{ number_format($totalFee, 2) }}</td>
        </tr>
    </tbody>
</table>

{{-- ===== PAYMENT DETAILS ===== --}}
<div class="blue-bar">Maklumat Pembayaran / Payment Details</div>
<table class="info-table" style="margin-bottom: 10px;">
    <tr>
        <td class="label-cell">{{ __('app.receipt_amount') }}</td>
        <td><strong style="font-size: 12px;">RM {{ number_format($payment->amount, 2) }}</strong></td>
    </tr>
    <tr>
        <td class="label-cell">{{ __('app.receipt_method') }}</td>
        <td>{{ strtoupper($payment->payment_method ?? 'FPX') }} (Toyyibpay)</td>
    </tr>
    <tr>
        <td class="label-cell">{{ __('app.receipt_status') }}</td>
        <td>
            @if($payment->status === 'paid')
                <strong style="color: #006600;">DIBAYAR / PAID</strong>
            @elseif($payment->status === 'pending')
                <strong style="color: #cc6600;">BELUM DIBAYAR / PENDING</strong>
            @elseif($payment->status === 'failed')
                <strong style="color: #cc0000;">GAGAL / FAILED</strong>
            @endif
        </td>
    </tr>
    @if($payment->transaction_id)
    <tr>
        <td class="label-cell">{{ __('app.receipt_transaction_id') }}</td>
        <td>{{ $payment->transaction_id }}</td>
    </tr>
    @endif
    @if($payment->paid_at)
    <tr>
        <td class="label-cell">{{ __('app.receipt_paid_at') }}</td>
        <td>{{ $payment->paid_at->format('d/m/Y H:i:s') }}</td>
    </tr>
    @endif
    @if($payment->billcode)
    <tr>
        <td class="label-cell">Billcode</td>
        <td>{{ $payment->billcode }}</td>
    </tr>
    @endif
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
