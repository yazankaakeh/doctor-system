<?php

namespace Modules\Doctor\Traits;

trait PDFTrait
{
    protected function injectPdfCss(string $html): string
    {
        $cssUrl = asset($this->mixOrAssetPath('assets/pdf.css')); // helper below
        $tag = '<link rel="stylesheet" href="'.$cssUrl.'">';

        return str_contains($html, '</head>')
            ? str_replace('</head>', "{$tag}\n</head>", $html)
            : ($tag.$html);
    }

    // Generate on-the-fly and stream
    protected function mixOrAssetPath(string $path): string
    {
        // simple passthrough; asset() reads mix-manifest if present in older setups
        return $path;
    }
}
