<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pasukan Diluluskan</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            color: #222;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            background-color: #003366;
            color: #ffffff;
            padding: 20px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 12px;
            opacity: 0.8;
        }
        .body-content {
            padding: 30px;
        }
        .badge-approved {
            display: inline-block;
            background-color: #28a745;
            color: #ffffff;
            padding: 6px 16px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
            letter-spacing: 1px;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }
        .info-box table {
            width: 100%;
        }
        .info-box td {
            padding: 4px 0;
            font-size: 13px;
        }
        .info-box .label {
            color: #003366;
            font-weight: bold;
            width: 40%;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 15px 30px;
            text-align: center;
            font-size: 11px;
            color: #666;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PERSATUAN BOLA SEPAK JOHOR BAHRU</h1>
            
        </div>

        <div class="body-content">
            <p style="text-align: center; margin-bottom: 20px;">
                <span class="badge-approved">DILULUSKAN</span>
            </p>

            <p>Assalamualaikum & Salam Sejahtera,</p>

            <p>
                Dengan sukacitanya dimaklumkan bahawa pasukan <strong>{{ $team->name }}</strong>
                telah <strong>diluluskan</strong> untuk menyertai
                <strong>{{ $competitionNameMalay }} tahun {{ $team->competition->season ?? date('Y') }}</strong>.
            </p>



            <div class="info-box">
                <table>
                    <tr>
                        <td class="label">Pasukan</td>
                        <td>{{ $team->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Pertandingan</td>
                        <td>{{ $team->competition->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Pengurus</td>
                        <td>{{ $team->manager_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status</td>
                        <td style="color: #28a745; font-weight: bold;">DILULUSKAN</td>
                    </tr>
                </table>
            </div>

            @if($team->competition_id != 1)
            <p>
                Surat Kelayakan telah dilampirkan bersama e-mel ini.
                Anda juga boleh memuat turun surat ini melalui portal di
                <a href="{{ route('teams.show', $team) }}" style="color: #003366;">myjbfa.com</a>.
            </p>

            @endif

            <p>
                Sila pastikan yuran liga dijelaskan pada atau selewat-lewatnya pada 12 Julai 2026.
            </p>


            <p>
                Sila pastikan semua pemain dan pegawai berdaftar secara rasmi dalam sistem JBFA
                selewat-lewatnya pada 20 Julai 2026.
            </p>


            <p style="margin-top: 25px;">
                Sekian, terima kasih.<br>
                <strong>Persatuan Bola Sepak Johor Bahru (JBFA)</strong>
            </p>
        </div>

        <div class="footer">
            <p>
                E-mel ini dijana secara automatik oleh sistem JBFA. Sila jangan balas e-mel ini.
            </p>
            <p style="margin-top: 8px;">
                PERSATUAN BOLA SEPAK JOHOR BAHRU &mdash; Sistem Pengurusan Liga JBFA
            </p>
        </div>
    </div>
</body>
</html>
