<?php

declare(strict_types=1);

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

final readonly class QrCodeRenderer
{
    /**
     * Render a QR code as an SVG data URI that can be used as an image source.
     */
    public function handle(string $content, int $size = 260): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd,
        );

        $svg = (new Writer($renderer))->writeString($content);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
