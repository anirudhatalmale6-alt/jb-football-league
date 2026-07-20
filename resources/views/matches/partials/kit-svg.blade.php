@php
    /**
     * Reusable football kit visual (shirt + shorts + socks), coloured from a
     * jersey submission. Geometry/colours come from App\Support\Kit so the
     * Match Details page, Live/Match view and the PDFs share one source.
     *
     * Params: $shirt, $shorts, $socks (hex, nullable), $w (width px),
     *         $stroke (outline colour), $caption (label under kit),
     *         $pdf (bool: emit an <img> data-URI for DomPDF instead of inline SVG).
     */
    $w       = $w ?? 84;
    $stroke  = $stroke ?? '#2b2b2b';
    $caption = $caption ?? null;
    $pdf     = $pdf ?? false;
    $svg     = \App\Support\Kit::svg($shirt ?? null, $shorts ?? null, $socks ?? null, $w, $stroke);
    $h       = round($w * 1.65);
@endphp
<div style="display:inline-block; text-align:center;">
    @if($pdf)
        <img src="data:image/svg+xml;base64,{{ base64_encode($svg) }}" style="width:{{ $w }}px; height:{{ $h }}px;" alt="kit">
    @else
        {!! $svg !!}
    @endif
    @if($caption)
        <div style="font-size:11px; color:#666; margin-top:2px; font-weight:600; text-transform:uppercase; letter-spacing:.3px;">{{ $caption }}</div>
    @endif
</div>
