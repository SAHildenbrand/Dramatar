<?php

declare(strict_types=1);

namespace Dramatar\Builder;

use Dramatar\Color;
use Dramatar\Rng;
use GdImage;

/** A sunset over 4-6 layers of rolling hills, fading from pale in the distance to dark in front. */
final class DunesBuilder implements Builder
{
    private const SIZE = 480;

    public function build(Rng $rng): GdImage
    {
        $image = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagealphablending($image, true);
        imageantialias($image, true);

        $hue = $rng->int(0, 359) / 360;
        $layers = $rng->int(4, 6);

        $this->sky($image, $hue);
        $this->sun($image, $rng, $hue);

        for ($layer = 0; $layer < $layers; $layer++) {
            $depth = $layer / max(1, $layers - 1);
            $base = 200 + $layer * (self::SIZE - 240) / $layers;
            $amplitude = 24 + $layer * 10;
            $freq1 = $rng->int(80, 200) / 10000;
            $freq2 = $rng->int(200, 400) / 10000;
            $phase1 = $rng->int(0, 628) / 100;
            $phase2 = $rng->int(0, 628) / 100;

            $polygon = [0, self::SIZE];
            for ($x = 0; $x <= self::SIZE; $x += 6) {
                $polygon[] = $x;
                $polygon[] = (int) ($base + $amplitude * sin($x * $freq1 + $phase1) + $amplitude * 0.4 * sin($x * $freq2 + $phase2));
            }
            $polygon[] = self::SIZE;
            $polygon[] = self::SIZE;

            imagefilledpolygon($image, $polygon, Color::allocateHsl(
                $image,
                fmod($hue + 0.04 - 0.04 * $depth + 1, 1.0),
                0.55 - 0.1 * $depth,
                0.62 - 0.5 * $depth,
            ));
        }

        return $image;
    }

    private function sky(GdImage $image, float $hue): void
    {
        [$tr, $tg, $tb] = Color::hslToRgb(fmod($hue + 0.08, 1.0), 0.6, 0.7);
        [$br, $bg, $bb] = Color::hslToRgb($hue, 0.8, 0.82);
        for ($y = 0; $y < self::SIZE; $y++) {
            $t = $y / self::SIZE;
            $color = (int) imagecolorallocate(
                $image,
                (int) ($tr + ($br - $tr) * $t),
                (int) ($tg + ($bg - $tg) * $t),
                (int) ($tb + ($bb - $tb) * $t),
            );
            imageline($image, 0, $y, self::SIZE, $y, $color);
        }
    }

    private function sun(GdImage $image, Rng $rng, float $hue): void
    {
        $x = $rng->int(120, self::SIZE - 120);
        $y = $rng->int(110, 210);
        [$r, $g, $b] = Color::hslToRgb(fmod($hue - 0.03 + 1, 1.0), 0.9, 0.9);
        for ($glow = 6; $glow >= 1; $glow--) {
            $size = 70 + $glow * 22;
            imagefilledellipse($image, $x, $y, $size, $size, (int) imagecolorallocatealpha($image, $r, $g, $b, 118 - $glow * 3));
        }
        imagefilledellipse($image, $x, $y, 70, 70, (int) imagecolorallocate($image, 255, 250, 235));
    }
}
