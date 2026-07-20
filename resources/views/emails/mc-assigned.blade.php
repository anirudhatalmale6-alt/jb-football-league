@php
    $home = $match->homeTeam->name ?? 'Home';
    $away = $match->awayTeam->name ?? 'Away';
    $when = $match->match_date ? $match->match_date->format('d M Y, g:i A') : '-';
    $comp = $match->competition->name ?? '-';
    $venue = $match->venue ?? '-';
    $url = rtrim(config('app.url'), '/') . '/matches/' . $match->id;
@endphp
<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;color:#222;">
    <div style="background:#003366;color:#fff;padding:16px 20px;border-radius:6px 6px 0 0;">
        <h2 style="margin:0;font-size:18px;">You have been assigned as Match Commissioner</h2>
        <div style="font-size:13px;opacity:.85;">Anda telah ditugaskan sebagai Pesuruhjaya Perlawanan</div>
    </div>
    <div style="border:1px solid #e0e0e0;border-top:none;padding:20px;border-radius:0 0 6px 6px;">
        <p>Dear {{ $commissioner->name }},</p>
        <p>You have been assigned to officiate the following match. Please review the match details and complete your match-day duties.</p>
        <table style="width:100%;border-collapse:collapse;font-size:14px;margin:16px 0;">
            <tr><td style="padding:6px 0;color:#666;width:120px;">Match</td><td style="padding:6px 0;font-weight:bold;">{{ $home }} vs {{ $away }}</td></tr>
            <tr><td style="padding:6px 0;color:#666;">Competition</td><td style="padding:6px 0;">{{ $comp }}</td></tr>
            <tr><td style="padding:6px 0;color:#666;">Date &amp; Time</td><td style="padding:6px 0;">{{ $when }}</td></tr>
            <tr><td style="padding:6px 0;color:#666;">Venue</td><td style="padding:6px 0;">{{ $venue }}</td></tr>
        </table>
        <p style="text-align:center;margin:24px 0;">
            <a href="{{ $url }}" style="background:#198754;color:#fff;text-decoration:none;padding:10px 22px;border-radius:5px;font-weight:bold;">Open Match Control Panel</a>
        </p>
        <p style="font-size:12px;color:#888;">This is an automated notification from the JBFA League Management System.</p>
    </div>
</div>
