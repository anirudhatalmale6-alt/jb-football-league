<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 25mm 20mm 20mm 20mm; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.6; }
        .letterhead { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #1a5276; padding-bottom: 20px; }
        .letterhead img { height: 80px; margin-bottom: 10px; }
        .letterhead h1 { margin: 0; font-size: 18px; color: #1a5276; }
        .letterhead p { margin: 2px 0; font-size: 11px; color: #666; }
        .ref-date { margin-bottom: 25px; }
        .ref-date table { width: 100%; }
        .ref-date td { font-size: 11px; vertical-align: top; }
        .subject { text-align: center; margin: 25px 0; padding: 12px; background: #fdedec; border-radius: 5px; }
        .subject h2 { margin: 0; font-size: 14px; color: #c0392b; text-transform: uppercase; }
        .content { margin: 20px 0; font-size: 12px; text-align: justify; }
        .content p { margin: 10px 0; }
        .notice-box { margin: 15px 0; padding: 15px; background: #fef9e7; border-left: 4px solid #f39c12; }
        .team-info { margin: 15px 0; }
        .team-info table { width: 100%; border-collapse: collapse; }
        .team-info td { padding: 6px 10px; border-bottom: 1px solid #eee; font-size: 11px; }
        .team-info td:first-child { font-weight: bold; width: 40%; color: #555; }
        .signature { margin-top: 40px; }
        .signature-line { border-top: 1px solid #333; width: 200px; margin-top: 50px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="letterhead">
        @if($jbfaLogoBase64)
            <img src="{{ $jbfaLogoBase64 }}" alt="JBFA Logo">
        @endif
        <h1>PERSATUAN BOLA SEPAK JOHOR BAHRU (JBFA)</h1>
        <p>Johor Bahru Football Association</p>
        <p>Liga JBFA 2026</p>
    </div>

    <div class="ref-date">
        <table>
            <tr>
                <td><strong>No. Rujukan:</strong> JBFA-RL-{{ str_pad($offer->id, 6, '0', STR_PAD_LEFT) }}</td>
                <td style="text-align: right;"><strong>Tarikh:</strong> {{ $offer->offered_at->format('d F Y') }}</td>
            </tr>
        </table>
    </div>

    <p><strong>Kepada:</strong><br>
    {{ $team->manager_name ?? 'Pengurus Pasukan' }}<br>
    {{ $team->name }}<br>
    ({{ $team->contact_email }})</p>

    <div class="subject">
        <h2>Penurunan Pangkat dari Liga Super ke Liga Perdana JBFA 2026</h2>
    </div>

    <div class="content">
        <p>Dengan hormatnya perkara di atas adalah dirujuk.</p>

        <p>Mesyuarat Jawatankuasa Liga JBFA 2026 telah memutuskan untuk menurunkan pangkat pasukan <strong>{{ $team->name }}</strong> dari <strong>Liga Super JBFA</strong> ke <strong>Liga Perdana JBFA</strong> berkuat kuasa serta-merta.</p>

        <div class="notice-box">
            <p style="margin: 0 0 8px;"><strong>Maklumat Penurunan Pangkat:</strong></p>
            <ul style="margin: 8px 0; padding-left: 20px;">
                <li>Yuran pendaftaran pasukan telah dikemas kini mengikut kadar Liga Perdana.</li>
                <li>Pasukan dikehendaki mematuhi semua peraturan Liga Perdana JBFA.</li>
                <li>Sebarang pertanyaan boleh dikemukakan melalui sistem myjbfa.com.</li>
            </ul>
        </div>

        <div class="team-info">
            <table>
                <tr>
                    <td>Pasukan</td>
                    <td>{{ $team->name }}</td>
                </tr>
                <tr>
                    <td>Liga Asal</td>
                    <td>Liga Super JBFA</td>
                </tr>
                <tr>
                    <td>Liga Baharu</td>
                    <td>Liga Perdana JBFA</td>
                </tr>
                <tr>
                    <td>Yuran Liga Perdana</td>
                    <td>RM 3,050.00</td>
                </tr>
                <tr>
                    <td>Tarikh Berkuatkuasa</td>
                    <td>{{ $offer->offered_at->format('d F Y') }}</td>
                </tr>
            </table>
        </div>

        <p>Sila log masuk ke <strong>myjbfa.com</strong> untuk melihat status terkini pasukan anda.</p>

        <p>Sekian, terima kasih.</p>
    </div>

    <div class="signature">
        <p><strong>"BERKHIDMAT UNTUK SUKAN"</strong></p>
        <div class="signature-line"></div>
        <p style="margin-top: 5px;">
            <strong>Jawatankuasa Liga JBFA 2026</strong><br>
            Persatuan Bola Sepak Johor Bahru
        </p>
    </div>

    <div class="footer">
        <p>Dokumen ini dijana secara automatik oleh sistem myjbfa.com pada {{ now()->format('d F Y, h:i A') }}</p>
    </div>
</body>
</html>
