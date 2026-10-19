<?php

declare(strict_types=1);

namespace Dramatar\Builder;

use GdImage;

/** Anime-style character. Parts are 200x300 and drawn enlarged so the head fills the frame. */
final class AnimeBuilder extends LayeredBuilder
{
    protected function layers(): array
    {
        return array_map(
            static fn (string $dir) => new Layer("anime/$dir"),
            ['Shadow', 'Base', 'Shirts', 'Pants', 'Shoes', 'Eyes', 'Mouth', 'Makeup', 'Headgear', 'Hair', 'Coats', 'Accessories'],
        );
    }

    protected function place(GdImage $canvas, GdImage $part, Layer $layer): void
    {
        imagecopyresampled($canvas, $part, -50, -30, 0, 0, 340, 440, imagesx($part), imagesy($part));
    }
}
