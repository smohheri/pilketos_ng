<?php

namespace App\Libraries;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrService
{
    public function svg(string $content, int $scale = 6): string
    {
        $options = new QROptions([
            'eccLevel' => 'M',
            'scale' => $scale,
            'addQuietzone' => true,
            'quietzoneSize' => 2,
            'svgAddXmlHeader' => false,
            'svgUseFillAttributes' => true,
            'outputBase64' => false,
        ]);

        return (new QRCode($options))->render($content);
    }
}