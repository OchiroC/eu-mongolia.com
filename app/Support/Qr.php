<?php

namespace App\Support;

use Endroid\QrCode\Builder\Builder;

class Qr
{
    /** Өгөгдлийг QR PNG (binary) болгож буцаана. GD ашиглана. */
    public static function png(string $data, int $size = 320): string
    {
        return (new Builder(data: $data, size: $size, margin: 8))->build()->getString();
    }
}
