<?php

declare(strict_types=1);

namespace Dramatar\Builder;

use Dramatar\Color;
use Dramatar\Rng;
use GdImage;

/**
 * Two overlaid epicycle curves: x = ra*cos(k*a) + rb*cos(k*b), y likewise with sin.
 * The old spiro / arc / arcii / boxy builders were all
 * this algorithm with different ranges and stroke styles; those are the named presets below.
 *
 * Ranges are [min, max] inclusive. Curve 1 uses (a, b, ra) and radius $outer1;
 * curve 2 uses (c, d, ra) and radius $outer2, where rb = outer - i (curve 1) / outer - i2 (curve 2).
 */
final class SpirographBuilder implements Builder
{
    private const SIZE = 240;
    /** Supersampling factor; the caller scales back down, which gives smooth lines. */
    private const SCALE = 2;
    /** The originals looped until the curve closed, which could be forever. */
    private const MAX_STEPS = 6000;

    /**
     * @param array{int,int} $a
     * @param array{int,int} $b
     * @param array{int,int} $i
     * @param array{int,int} $c
     * @param array{int,int} $d
     * @param array{int,int} $i2
     */
    public function __construct(
        private readonly Stroke $stroke1,
        private readonly Stroke $stroke2,
        private readonly array $a = [-20, 20],
        private readonly array $b = [5, 10],
        private readonly array $i = [1, 100],
        private readonly array $c = [1, 10],
        private readonly array $d = [1, 5],
        private readonly array $i2 = [1, 50],
        private readonly int $outer1 = 120,
        private readonly int $outer2 = 120,
    ) {
    }

    public static function spiro(): self
    {
        return new self(Stroke::Line, Stroke::Rainbow);
    }

    public static function arc(): self
    {
        return new self(Stroke::Ellipse, Stroke::Ellipse, outer2: 50);
    }

    public static function arcii(): self
    {
        return new self(Stroke::Ellipse, Stroke::Ellipse, i: [1, 20], outer1: 50, outer2: 50);
    }

    public static function boxy(): self
    {
        return new self(Stroke::Rectangle, Stroke::Rectangle, a: [1, 100], b: [-100, 10], i: [-50, 50], c: [1, 100], d: [-100, 10], i2: [10, 50]);
    }

    public function build(Rng $rng): GdImage
    {
        $px = self::SIZE * self::SCALE;
        $image = imagecreatetruecolor($px, $px);
        imagealphablending($image, true);
        imageantialias($image, true);
        imagefill($image, 0, 0, (int) imagecolorallocate($image, 255, 255, 255));

        $dark = (int) imagecolorallocate($image, $rng->int(0, 90), $rng->int(0, 90), $rng->int(0, 90));
        $light = (int) imagecolorallocate($image, $rng->int(110, 255), $rng->int(110, 255), $rng->int(110, 255));
        // Draw order of random values matches the original: curve parameters after colours.
        $a = $rng->int(...$this->a);
        $b = $rng->int(...$this->b);
        $ra = $rng->int(...$this->i);
        $c = $rng->int(...$this->c);
        $d = $rng->int(...$this->d);
        $ra2 = $rng->int(...$this->i2);

        imagesetthickness($image, self::SCALE);
        $this->curve($image, $rng, $this->stroke1, $dark, $a, $b, $ra, $this->outer1 - $ra);
        // The original reused ra (i) as the first radius of the second curve.
        $this->curve($image, $rng, $this->stroke2, $light, $c, $d, $ra, $this->outer2 - $ra2);

        return $image;
    }

    private function curve(GdImage $image, Rng $rng, Stroke $stroke, int $color, int $angleA, int $angleB, int $ra, int $rb): void
    {
        $center = self::SIZE * self::SCALE / 2;
        $point = static fn (int $k): array => [
            $center + self::SCALE * ($ra * cos($k * $angleA) + $rb * cos($k * $angleB)),
            $center + self::SCALE * ($ra * sin($k * $angleA) + $rb * sin($k * $angleB)),
        ];

        [$startX, $startY] = $point(1);
        [$x1, $y1] = [$startX, $startY];
        $hue = $rng->int(0, 240);

        for ($k = 2; $k < self::MAX_STEPS; $k++) {
            [$x2, $y2] = [$x1, $y1];
            [$x1, $y1] = $point($k);

            switch ($stroke) {
                case Stroke::Line:
                    imageline($image, (int) $x1, (int) $y1, (int) $x2, (int) $y2, $color);
                    break;
                case Stroke::Rectangle:
                    imagerectangle($image, (int) $x1, (int) $y1, (int) $x2, (int) $y2, $color);
                    break;
                case Stroke::Ellipse:
                    imageellipse($image, (int) $x1, (int) $y1, $rng->int(1, 25) * self::SCALE, $rng->int(1, 25) * self::SCALE, $color);
                    break;
                case Stroke::Rainbow:
                    $rgb = Color::hslToRgb($hue / 240, 100 / 240, 120 / 240);
                    $hue = ($hue + 1) % 241;
                    imageline($image, (int) $x1, (int) $y1, (int) $x2, (int) $y2, (int) imagecolorallocate($image, ...$rgb));
                    break;
            }

            // Closed the loop: back within 2px of the start.
            if (abs($x1 - $startX) < 2 * self::SCALE && abs($y1 - $startY) < 2 * self::SCALE) {
                break;
            }
        }
    }
}
