<?php

declare(strict_types=1);

namespace Dramatar;

use GdImage;

final class Color
{
    /**
     * @param float $h hue 0..1, $s saturation 0..1, $l lightness 0..1
     * @return array{int,int,int}
     */
    public static function hslToRgb(float $h, float $s, float $l): array
    {
        $c = (1 - abs(2 * $l - 1)) * $s;
        $hp = fmod($h, 1.0) * 6;
        $x = $c * (1 - abs(fmod($hp, 2) - 1));
        [$r, $g, $b] = match ((int) floor($hp)) {
            0 => [$c, $x, 0],
            1 => [$x, $c, 0],
            2 => [0, $c, $x],
            3 => [0, $x, $c],
            4 => [$x, 0, $c],
            default => [$c, 0, $x],
        };
        $m = $l - $c / 2;

        return [(int) round(($r + $m) * 255), (int) round(($g + $m) * 255), (int) round(($b + $m) * 255)];
    }

    /** Allocate a colour on an image from HSL components. */
    public static function allocateHsl(GdImage $image, float $h, float $s, float $l): int
    {
        [$r, $g, $b] = self::hslToRgb($h, $s, $l);

        return (int) imagecolorallocate($image, $r, $g, $b);
    }
}
