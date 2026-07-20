<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jersey Colour Submission Reminder</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #222; line-height: 1.6; background-color: #f5f5f5; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden;">
        <div style="background-color: #003366; color: #ffffff; padding: 20px 30px; text-align: center;">
            <h1 style="margin: 0; font-size: 20px;">PERSATUAN BOLA SEPAK JOHOR BAHRU</h1>
        </div>
        <div style="padding: 30px;">
            <p style="text-align:center;">
                <span style="display:inline-block; background-color:#ffc107; color:#000; padding:6px 16px; border-radius:4px; font-weight:bold;">
                    JERSEY COLOUR SUBMISSION REQUIRED
                </span>
            </p>

            <p>Assalamualaikum &amp; Salam Sejahtera,</p>

            <p>
                This is a reminder that <strong>{{ $team->name }}</strong> has not yet submitted its
                jersey colours for the upcoming match:
            </p>

            <div style="background:#f8f9fa; border:1px solid #dee2e6; border-radius:6px; padding:15px; margin:15px 0;">
                <table style="width:100%; font-size:13px;">
                    <tr>
                        <td style="color:#003366; font-weight:bold; width:40%;">Match</td>
                        <td>{{ $match->homeTeam->name ?? 'Home' }} vs {{ $match->awayTeam->name ?? 'Away' }}</td>
                    </tr>
                    <tr>
                        <td style="color:#003366; font-weight:bold;">Competition</td>
                        <td>{{ $match->competition->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color:#003366; font-weight:bold;">Kick-Off</td>
                        <td>{{ $match->match_date ? $match->match_date->format('d M Y, h:i A') : 'TBC' }}</td>
                    </tr>
                    <tr>
                        <td style="color:#003366; font-weight:bold;">Submission Deadline</td>
                        <td style="color:#c00; font-weight:bold;">{{ $deadline }}</td>
                    </tr>
                </table>
            </div>

            <p>
                Jersey colours must be submitted no later than <strong>3 days before kick-off</strong>
                so the Match Commissioner can check for colour clashes.
                ({{ $daysBefore }} day(s) reminder)
            </p>

            <p style="text-align:center; margin:25px 0;">
                <a href="{{ route('matches.show', $match->id) }}"
                   style="background-color:#28a745; color:#ffffff; text-decoration:none; padding:12px 28px; border-radius:6px; font-weight:bold;">
                    Submit Jersey Colours
                </a>
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
