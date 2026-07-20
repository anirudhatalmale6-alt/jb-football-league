<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Affiliate Membership Fee Reminder</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #222; line-height: 1.6; background-color: #f5f5f5; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden;">
        <div style="background-color: #003366; color: #ffffff; padding: 20px 30px; text-align: center;">
            <h1 style="margin: 0; font-size: 20px;">PERSATUAN BOLA SEPAK JOHOR BAHRU</h1>
        </div>
        <div style="padding: 30px;">
            <p style="text-align:center;">
                <span style="display:inline-block; background-color:#dc3545; color:#fff; padding:6px 16px; border-radius:4px; font-weight:bold;">
                    YURAN KEAHLIAN GABUNGAN BELUM DIJELASKAN / AFFILIATE FEE OUTSTANDING
                </span>
            </p>

            <p>Assalamualaikum &amp; Salam Sejahtera,</p>

            <p>
                Ini adalah peringatan bahawa <strong>{{ $team->name }}</strong> masih belum menjelaskan
                yuran keahlian gabungan tahunan JBFA.
            </p>
            <p style="color:#555;">
                This is a reminder that <strong>{{ $team->name }}</strong> has not yet settled its
                annual JBFA affiliate membership fee.
            </p>

            <div style="background:#f8f9fa; border:1px solid #dee2e6; border-radius:6px; padding:15px; margin:15px 0;">
                <table style="width:100%; font-size:13px;">
                    <tr>
                        <td style="color:#003366; font-weight:bold; width:45%;">Pasukan / Team</td>
                        <td>{{ $team->name }}</td>
                    </tr>
                    <tr>
                        <td style="color:#003366; font-weight:bold;">Liga / Competition</td>
                        <td>{{ $team->competition->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color:#003366; font-weight:bold;">Yuran / Fee</td>
                        <td style="color:#c00; font-weight:bold;">RM {{ number_format($amount, 2) }}</td>
                    </tr>
                </table>
            </div>

            <p>
                Sila jelaskan bayaran yuran keahlian gabungan sebanyak <strong>RM {{ number_format($amount, 2) }}</strong>
                secepat mungkin bagi memastikan status keahlian pasukan anda kekal aktif.
            </p>
            <p style="color:#555;">
                Kindly settle the affiliate membership fee of <strong>RM {{ number_format($amount, 2) }}</strong>
                at your earliest convenience to keep your team's membership status active.
            </p>

            <p>
                Jika bayaran telah dibuat, sila abaikan peringatan ini.<br>
                <span style="color:#555;">If payment has already been made, please disregard this reminder.</span>
            </p>

            <p style="margin-top:25px;">
                Sekian, terima kasih.<br>
                <strong>Persatuan Bola Sepak Johor Bahru (JBFA)</strong>
            </p>
        </div>
        <div style="background:#f8f9fa; padding:15px 30px; text-align:center; font-size:11px; color:#666; border-top:1px solid #dee2e6;">
            <p style="margin:0;">This email was generated automatically by the JBFA League system.</p>
        </div>
    </div>
</body>
</html>
