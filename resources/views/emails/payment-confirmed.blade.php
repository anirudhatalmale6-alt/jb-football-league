@php
    $teamName = $payment->team->name ?? 'Your team';
    $competitionName = $payment->competition->name ?? '';
    $amount = number_format($payment->amount, 2);
    $paidAt = \App\Support\Tz::myt($payment->paid_at ?: now(), 'd M Y, h:i A');
    $ref = $payment->transaction_id ?: ('#' . $payment->id);
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:600px;margin:0 auto;padding:24px;">
        <div style="background:#0f5132;color:#ffffff;padding:20px 24px;border-radius:10px 10px 0 0;">
            <h2 style="margin:0;font-size:20px;">JBFA League</h2>
        </div>
        <div style="background:#ffffff;padding:24px;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 10px 10px;">

            <div style="text-align:center;margin-bottom:20px;">
                <div style="display:inline-block;background:#d1e7dd;color:#0f5132;font-size:15px;font-weight:bold;padding:10px 22px;border-radius:30px;">
                    &#10004; Payment Confirmed / Pembayaran Disahkan
                </div>
            </div>

            <p style="font-size:15px;line-height:1.6;margin:0 0 16px;">
                Dear {{ $teamName }},<br>
                Your registration payment has been received and marked as <strong>PAID</strong>. Thank you.
            </p>

            <table style="width:100%;border-collapse:collapse;font-size:14px;margin:12px 0 20px;">
                <tr><td style="padding:8px 0;color:#6b7280;">Team / Pasukan</td><td style="padding:8px 0;text-align:right;font-weight:bold;">{{ $teamName }}</td></tr>
                <tr><td style="padding:8px 0;color:#6b7280;">Competition / Pertandingan</td><td style="padding:8px 0;text-align:right;font-weight:bold;">{{ $competitionName }}</td></tr>
                <tr><td style="padding:8px 0;color:#6b7280;">Amount / Jumlah</td><td style="padding:8px 0;text-align:right;font-weight:bold;">RM {{ $amount }}</td></tr>
                <tr><td style="padding:8px 0;color:#6b7280;">Reference / Rujukan</td><td style="padding:8px 0;text-align:right;font-weight:bold;">{{ $ref }}</td></tr>
                <tr><td style="padding:8px 0;color:#6b7280;">Date / Tarikh</td><td style="padding:8px 0;text-align:right;font-weight:bold;">{{ $paidAt }}</td></tr>
            </table>

            <p style="font-size:14px;line-height:1.6;color:#6b7280;margin:0 0 8px;">
                You can view your payment status anytime under "My Payments" in the JBFA system.
            </p>
            <p style="font-size:14px;line-height:1.6;color:#6b7280;margin:0;">
                Anda boleh menyemak status pembayaran anda pada bila-bila masa di bahagian "Pembayaran Saya" dalam sistem JBFA.
            </p>

            <hr style="border:none;border-top:1px solid #e5e7eb;margin:22px 0;">
            <p style="font-size:12px;color:#9ca3af;margin:0;text-align:center;">
                This is an automated message from the JBFA League Management System.<br>
                Ini adalah mesej automatik daripada Sistem Pengurusan Liga JBFA.
            </p>
        </div>
    </div>
</body>
</html>
