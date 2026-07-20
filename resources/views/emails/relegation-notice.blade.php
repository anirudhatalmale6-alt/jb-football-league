<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); padding: 30px; text-align: center; color: #fff; }
        .header h1 { margin: 0 0 5px 0; font-size: 20px; }
        .header p { margin: 0; opacity: 0.9; font-size: 14px; }
        .badge { display: inline-block; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4); border-radius: 20px; padding: 5px 15px; margin-bottom: 15px; font-size: 12px; letter-spacing: 1px; }
        .body { padding: 30px; color: #333; line-height: 1.6; }
        .info-box { background: #f8f9fa; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 0 4px 4px 0; }
        .info-box table { width: 100%; }
        .info-box td { padding: 4px 0; }
        .info-box td:first-child { font-weight: bold; width: 40%; color: #555; }
        .notice-box { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .notice-box strong { color: #856404; }
        .footer { padding: 20px 30px; background: #f8f9fa; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="badge">NOTIS RASMI / OFFICIAL NOTICE</div>
            <h1>Penurunan Pangkat Liga</h1>
            <p>League Relegation Notice</p>
        </div>
        <div class="body">
            <p>Kepada Pengurus Pasukan / To the Team Manager of,</p>
            <p style="font-size: 18px; font-weight: bold; color: #dc3545;">{{ $team->name }}</p>

            <p>Mesyuarat Jawatankuasa Liga JBFA 2026 telah memutuskan untuk menurunkan pangkat pasukan anda dari <strong>Liga Super</strong> ke <strong>Liga Perdana</strong>.</p>

            <p>The JBFA 2026 League Committee has decided to relegate your team from the <strong>Super League</strong> to the <strong>Premier League</strong>.</p>

            <div class="info-box">
                <table>
                    <tr>
                        <td>Pasukan / Team:</td>
                        <td>{{ $team->name }}</td>
                    </tr>
                    <tr>
                        <td>Dari / From:</td>
                        <td>Liga Super JBFA / JBFA Super League</td>
                    </tr>
                    <tr>
                        <td>Ke / To:</td>
                        <td>Liga Perdana JBFA / JBFA Premier League</td>
                    </tr>
                    <tr>
                        <td>Tarikh Berkuatkuasa / Effective:</td>
                        <td>{{ $offer->offered_at->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td>Yuran Baru / New Fee:</td>
                        <td>RM 3,050</td>
                    </tr>
                </table>
            </div>

            <div class="notice-box">
                <strong>Nota / Note:</strong> Yuran pendaftaran pasukan anda telah dikemas kini mengikut kadar Liga Perdana. Sila rujuk sistem myjbfa.com untuk maklumat lanjut.
                <br><br>
                Your team registration fee has been updated to the Premier League rate. Please refer to the myjbfa.com system for further details.
            </div>

            <p>Surat rasmi penurunan pangkat dilampirkan bersama e-mel ini.</p>
            <p><em>The official relegation letter is attached to this email.</em></p>

            <p style="margin-top: 30px;">Sekian, terima kasih.<br>
            <strong>Jawatankuasa Liga JBFA 2026</strong><br>
            <em>JBFA 2026 League Committee</em></p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Persatuan Bola Sepak Johor Bahru (JBFA) | myjbfa.com</p>
        </div>
    </div>
</body>
</html>
