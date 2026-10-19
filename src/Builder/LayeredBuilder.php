<?php

declare(strict_types=1);

namespace Dramatar\Builder;

use Dramatar\Color;
use Dramatar\Rng;
use GdImage;
use RuntimeException;

/**
 * Stacks randomly chosen transparent PNG parts over a coloured background.
 * Subclasses only declare the layer stack (bottom to top).
 */
abstract class LayeredBuilder implements Builder
{
    protected const SIZE = 240;

    /** @var array<string,list<string>> */
    private static array $fileCache = [];

    public function __construct(protected readonly string $partsDir)
    {
    }

    /** @return list<Layer> bottom-to-top */
    abstract protected function layers(): array;

    /** Fill the background. Default: pastel with a random hue. */
    protected function background(GdImage $image, Rng $rng): void
    {
        $hue = $rng->int(1, 240) / 240;
        $sat = $rng->int(100, 230) / 240;
        imagefill($image, 0, 0, Color::allocateHsl($image, $hue, $sat, 100 / 240));
    }

    /** Copy one part onto the canvas. Override to rescale/offset. */
    protected function place(GdImage $canvas, GdImage $part, Layer $layer): void
    {
        imagecopy($canvas, $part, 0, 0, 0, 0, imagesx($part), imagesy($part));
    }

    public function build(Rng $rng): GdImage
    {
        $image = imagecreatetruecolor(static::SIZE, static::SIZE);
        imagealphablending($image, true);
        $this->background($image, $rng);

        foreach ($this->layers() as $layer) {
            $files = $this->files($layer);
            if ($files === []) {
                continue;
            }
            $file = $rng->pick($files);
            if (!$rng->chance($layer->chance)) {
                continue;
            }
            $part = $this->load($file);
            $this->place($image, $part, $layer);
            imagedestroy($part);
        }

        return $image;
    }

    /** @return list<string> sorted absolute paths */
    private function files(Layer $layer): array
    {
        $key = $this->partsDir . '|' . $layer->dir . '|' . $layer->prefix;
        if (!isset(self::$fileCache[$key])) {
            $found = [];
            foreach (glob($this->partsDir . '/' . $layer->dir . '/*.png') ?: [] as $path) {
                $name = basename($path);
                if ($name[0] !== '.' && str_starts_with($name, $layer->prefix)) {
                    $found[] = $path;
                }
            }
            natsort($found);
            self::$fileCache[$key] = array_values($found);
        }

        return self::$fileCache[$key];
    }

    private function load(string $path): GdImage
    {
        $part = @imagecreatefrompng($path);
        if ($part === false) {
            throw new RuntimeException("Failed to load part image: $path");
        }
        imagealphablending($part, true);
        imagesavealpha($part, true);

        return $part;
    }
}
