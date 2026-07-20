<?php

namespace App\Support;

class Kit
{
    /**
     * Build the standard football kit illustration (shirt + shorts + socks)
     * as an SVG string, coloured from a jersey submission. Single source of
     * truth for the Match Details page, Live/Match view and the PDFs.
     *
     * The paths are authored in a 100x165 space but wrapped in a scaled group
     * with a small viewBox, because DomPDF sizes SVG images from the viewBox
     * (ignoring width/height); this keeps the PDF kit small while the browser
     * still scales the same SVG up crisply via the root width/height.
     */
    public static function svg($shirt = null, $shorts = null, $socks = null, $w = 84, $stroke = '#2b2b2b'): string
    {
        $shirtC  = !empty($shirt)  ? $shirt  : '#ffffff';
        $shortsC = !empty($shorts) ? $shorts : '#ffffff';
        $socksC  = !empty($socks)  ? $socks  : '#ffffff';
        $h  = round($w * 1.65);
        $sc = 0.35;
        $vbw = round(100 * $sc, 2);
        $vbh = round(165 * $sc, 2);
        $sw = 2.0;

        return <<<SVG
<svg width="{$w}" height="{$h}" viewBox="0 0 {$vbw} {$vbh}" xmlns="http://www.w3.org/2000/svg">
<g transform="scale({$sc})">
<path d="M34,18 L20,24 L10,46 L26,42 L28,80 L72,80 L72,42 L90,46 L80,24 L66,18 Q58,30 50,30 Q42,30 34,18 Z" fill="{$shirtC}" stroke="{$stroke}" stroke-width="{$sw}" stroke-linejoin="round"/>
<path d="M29,83 L71,83 L74,122 L55,122 L50,102 L45,122 L26,122 Z" fill="{$shortsC}" stroke="{$stroke}" stroke-width="{$sw}" stroke-linejoin="round"/>
<path d="M31,126 L43,126 L41,157 L33,157 Z" fill="{$socksC}" stroke="{$stroke}" stroke-width="{$sw}" stroke-linejoin="round"/>
<path d="M57,126 L69,126 L67,157 L59,157 Z" fill="{$socksC}" stroke="{$stroke}" stroke-width="{$sw}" stroke-linejoin="round"/>
</g>
</svg>
SVG;
    }
}
