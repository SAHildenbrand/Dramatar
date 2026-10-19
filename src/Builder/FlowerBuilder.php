<?php

declare(strict_types=1);

namespace Dramatar\Builder;

use Dramatar\Color;
use Dramatar\Rng;
use GdImage;

/**
 * A star-petalled flower drawn with GD primitives, sitting on leaf parts, with an optional bug.
 * Layers: background, leaves (parts), flower (drawn), bug (part).
 */
final class FlowerBuilder extends LayeredBuilder
{
    /** The flower is drawn at 2x and downsampled for smooth edges. */
    private const SS = 2;

    protected function layers(): array
    {
        return [
            new Layer('flower', 'leaf_'),
            new Layer('flower', 'leaf_', 0.6),
        ];
    }

    protected function background(GdImage $image, Rng $rng): void
    {
        imagefill($image, 0, 0, Color::allocateHsl($image, $rng->int(0, 359) / 360, 0.75, 0.55));
    }

    public function build(Rng $rng): GdImage
    {
        $image = parent::build($rng);

        $flower = $this->drawFlower($rng);
        $size = static::SIZE;
        imagecopyresampled($image, $flower, 0, 0, 0, 0, $size, $size, imagesx($flower), imagesy($flower));
        imagedestroy($flower);

        $bugs = new BugLayer($this->partsDir);
        $bugs->apply($image, $rng);

        return $image;
    }

    /** Transparent 2x canvas holding just the flower. */
    private function drawFlower(Rng $rng): GdImage
    {
        $px = static::SIZE * self::SS;
        $canvas = imagecreatetruecolor($px, $px);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, (int) imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagealphablending($canvas, true);

        $hue = $rng->int(0, 359) / 360;
        $points = $rng->int(5, 8);
        // Max extent = centre offset + radius <= 6 + 105 = 111 of 120, so petals never touch the edge.
        $radius = $rng->int(74, 105) * self::SS;
        $cx = (static::SIZE / 2 + $rng->int(-6, 6)) * self::SS;
        $cy = (static::SIZE / 2 + $rng->int(-6, 6)) * self::SS;
        $rotation = $rng->int(0, 359) * M_PI / 180;

        // Outer star (dark), inner star (lighter), then a rosette and disc in the middle.
        $this->star($canvas, $cx, $cy, $radius, $radius * 0.62, $points, $rotation, Color::allocateHsl($canvas, $hue, 0.75, 0.30));
        $this->star($canvas, $cx, $cy, $radius * 0.92, $radius * 0.58, $points, $rotation, Color::allocateHsl($canvas, $hue, 0.70, 0.45));
        $this->star($canvas, $cx, $cy, $radius * 0.62, $radius * 0.40, $points, $rotation, Color::allocateHsl($canvas, $hue, 0.65, 0.58));
        $this->rosette($canvas, $cx, $cy, $radius * 0.40, $rng, fmod($hue + 0.33, 1.0));

        return $canvas;
    }

    private function star(GdImage $canvas, float $cx, float $cy, float $outer, float $inner, int $points, float $rotation, int $color): void
    {
        $poly = [];
        for ($i = 0; $i < $points * 2; $i++) {
            $r = $i % 2 === 0 ? $outer : $inner;
            $a = $rotation + $i * M_PI / $points;
            $poly[] = (int) round($cx + $r * cos($a));
            $poly[] = (int) round($cy + $r * sin($a));
        }
        imagefilledpolygon($canvas, $poly, $color);
    }

    /** Spirograph-style centre: a disc with a rose curve traced over it. */
    private function rosette(GdImage $canvas, float $cx, float $cy, float $radius, Rng $rng, float $hue): void
    {
        imagefilledellipse($canvas, (int) $cx, (int) $cy, (int) ($radius * 2), (int) ($radius * 2), Color::allocateHsl($canvas, $hue, 0.6, 0.30));

        $petals = $rng->int(3, 9);
        $line = Color::allocateHsl($canvas, $hue, 0.7, 0.62);
        imagesetthickness($canvas, self::SS);
        $prevX = $prevY = null;
        for ($t = 0; $t <= 2 * M_PI * 2 + 0.05; $t += 0.04) {
            $r = $radius * 0.92 * cos($petals * $t / 2);
            $x = (int) round($cx + $r * cos($t));
            $y = (int) round($cy + $r * sin($t));
            if ($prevX !== null) {
                imageline($canvas, $prevX, $prevY, $x, $y, $line);
            }
            [$prevX, $prevY] = [$x, $y];
        }
        imagesetthickness($canvas, 1);
    }
}
