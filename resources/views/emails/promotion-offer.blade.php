<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
        .header { background: linear-gradient(135deg, #1a5276, #2980b9); padding: 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; }
        .header p { color: #d4e6f1; margin: 5px 0 0; font-size: 14px; }
        .body-content { padding: 30px; }
        .badge { display: inline-block; padding: 8px 20px; border-radius: 20px; font-weight: bold; font-size: 14px; }
        .badge-promotion { background: #d4efdf; color: #1e8449; border: 2px solid #27ae60; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px 12px; border-bottom: 1px solid #ecf0f1; font-size: 14px; }
        .info-table td:first-child { font-weight: bold; color: #555; width: 40%; }
        .highlight-box { background: #eaf2f8; border-left: 4px solid #2980b9; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0; }
        .requirements { background: #fef9e7; border-left: 4px solid #f39c12; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0; }
        .requirements ul { margin: 10px 0; padding-left: 20px; }
        .requirements li { margin: 8px 0; font-size: 14px; color: #333; }
        .cta-button { display: inline-block; padding: 14px 30px; background: #27ae60; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; }
        .deadline { background: #fdedec; border: 2px solid #e74c3c; padding: 15px; text-align: center; border-radius: 8px; margin: 20px 0; }
        .deadline strong { color: #e74c3c; font-size: 16px; }
        .footer { background: #2c3e50; padding: 20px; text-align: center; color: #bdc3c7; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PERSATUAN BOLA SEPAK JOHOR BAHRU (JBFA)</h1>
            <p>Liga JBFA 2026</p>
        </div>

        <div class="body-content">
            <div style="text-align: center; margin-bottom: 25px;">
                <span class="badge badge-promotion">TAWARAN KENAIKAN PANGKAT / PROMOTION OFFER</span>
            </div>

            <p style="font-size: 15px; color: #333;">Kepada <strong>{{ $team->manager_name ?? $team->name }}</strong>,</p>

            @php
                $toComp = $offer->toCompetition;
                $fromComp = $offer->fromCompetition;
                $toName = $toComp ? $toComp->malayName() : 'Liga JBFA';
                $fromName = $fromComp ? $fromComp->malayName() : 'Liga JBFA';
                $annualFee = (optional($offer->team)->affiliate_fee_required && $toComp && $toComp->type === 'league') ? 50.00 : 0;
                $newFee = $toComp ? $toComp->baseFee() + $annualFee : 0;
                $toSuper = $toComp && (int) $toComp->id === 2;
            @endphp

            <div class="highlight-box">
                <p style="margin: 0; font-size: 15px; line-height: 1.6;">
                    <strong>Tawaran Khas Menyertai {{ $toName }} 2026.</strong><br><br>
                    Mesyuarat Jawatankuasa Liga JBFA 2026 memutuskan untuk menawarkan pasukan anda bertanding dalam {{ $toName }}.
                </p>
            </div>

            <table class="info-table">
                <tr>
                    <td>Pasukan / Team</td>
                    <td><strong>{{ $team->name }}</strong></td>
                </tr>
                <tr>
                    <td>Liga Semasa / Current League</td>
                    <td>{{ $fromName }}</td>
                </tr>
                <tr>
                    <td>Liga Baharu / New League</td>
                    <td><strong style="color: #27ae60;">{{ $toName }}</strong></td>
                </tr>
            </table>

            <div class="requirements">
                <p style="margin: 0 0 10px; font-weight: bold; color: #333;">Pasukan anda diminta untuk mengemukakan persetujuan dengan menyediakan keperluan berikut:</p>
                <ul>
                    @if($toSuper)
                    <li><strong>Persetujuan membayar yuran {{ $toName }} RM{{ number_format($newFee, 0) }}</strong> (padang &amp; Lesen Kejurulatihan C telah ada dalam rekod)</li>
                    @else
                    <li><strong>Nama padang &amp; alamat padang</strong></li>
                    <li><strong>Lesen Kejurulatihan C AFC/FAM</strong></li>
                    <li><strong>Persetujuan membayar yuran {{ $toName }} RM{{ number_format($newFee, 0) }}</strong></li>
                    @endif
                </ul>
            </div>

            <div class="deadline">
                <strong>Tempoh: 48 Jam / Deadline: 48 Hours</strong><br>
                <span style="font-size: 13px; color: #666;">Tamat: {{ $offer->expires_at->format('d M Y, h:i A') }}</span>
            </div>

            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ url('/promotions/' . $offer->id . '/respond') }}" class="cta-button">
                    Respon Sekarang / Respond Now
                </a>
            </div>

            <p style="font-size: 13px; color: #777; text-align: center;">
                Sila log masuk ke <strong>myjbfa.com</strong> untuk mengemukakan jawapan anda.
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0;">PERSATUAN BOLA SEPAK JOHOR BAHRU (JBFA)</p>
            <p style="margin: 5px 0 0;">Liga JBFA 2026 &bull; myjbfa.com</p>
        </div>
    </div>
</body>
</html>
