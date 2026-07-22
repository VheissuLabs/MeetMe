<?php

namespace App\Actions;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class GenerateQrCode
{
    public function svg(string $data, int $size = 320): string
    {
        $writer = new Writer(new ImageRenderer(
            new RendererStyle($size, margin: 1),
            new SvgImageBackEnd,
        ));

        return $writer->writeString($data);
    }
}
