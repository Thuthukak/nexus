<?php

declare(strict_types=1);

namespace Modules\Events\app\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    /**
     * Generate a QR code as a base64-encoded PNG data URI.
     * dompdf supports inline base64 images.
     */
    public function generateBase64(string $data, int $size = 150): string
    {
        // Use SVG backend — renders cleanly in dompdf
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg    = $writer->writeString($data);

        // dompdf handles SVG data URIs — encode it
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
