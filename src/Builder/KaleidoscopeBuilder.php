<?php

declare(strict_types=1);

namespace Dramatar\Builder;

use Dramatar\Color;
use Dramatar\Rng;
use GdImage;

/**
 * Random translucent shapes mirrored around the centre, like a kaleidoscope.
 * Shapes are drawn in one wedge, then rotated and reflected to fill the circle.
 */
final class KaleidoscopeBuilder implements Builder
{
    private const SIZE = 480;
    private const SHAPES = 9;

    public function build(Rng $rng): GdImage
    {
        $image = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagealphablending($image, true);
        imageantialias($image, true);

        $hue = $rng->int(0, 359) / 360;
        imagefill($image, 0, 0, $this->color($image, $hue, 0.5, 0.12));

        $segments = $rng->int(5, 10);
        $wedge = M_PI / $segments;
        $center = self::SIZE / 2;

        $shapes = [];
        for ($i = 0; $i < self::SHAPES; $i++) {
            $radius = 30 + $rng->int(0, 190);
            $angle = $rng->int(0, 1000) / 1000 * $wedge;
            $points = [];
            for ($p = $rng->int(3, 5); $p > 0; $p--) {
                $points[] = [$radius + $rng->int(-40, 40), $angle + $rng->int(-250, 250) / 1000];
            }
            $shapes[] = [
                $points,
                [
                    $hue + $rng->int(-180, 180) / 1000,
                    $rng->int(50, 95) / 100,
                    $rng->int(35, 70) / 100,
                ],
                $rng->int(10, 60),
            ];
        }

        foreach ($shapes as [$points, [$h, $s, $l], $alpha]) {
            [$r, $g, $b] = Color::hslToRgb(fmod($h + 1, 1.0), $s, $l);
            $color = (int) imagecolorallocatealpha($image, $r, $g, $b, $alpha);

            for ($k = 0; $k < $segments * 2; $k++) {
                $mirrored = $k % 2 === 1;
                $rotation = $k * $wedge * 2;
                $polygon = [];
                foreach ($points as [$rad, $ang]) {
                    $a = $rotation + ($mirrored ? $wedge * 2 - $ang : $ang);
                    $polygon[] = (int) ($center + $rad * cos($a));
                    $polygon[] = (int) ($center + $rad * sin($a));
                }
                imagefilledpolygon($image, $polygon, $color);
            }
        }

        imagefilledellipse($image, (int) $center, (int) $center, 30, 30, $this->color($image, fmod($hue + 0.5, 1.0), 0.8, 0.8));

        return $image;
    }

    private function color(GdImage $image, float $h, float $s, float $l): int
    {
        return Color::allocateHsl($image, $h, $s, $l);
    }
}
