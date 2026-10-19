<?php

declare(strict_types=1);

namespace Dramatar\Builder;

/**
 * One category of part images: <partsDir>/<dir>/<prefix>*.png
 * Part files are discovered from disk, so adding artwork needs no code change.
 */
final class Layer
{
    public function __construct(
        public readonly string $dir,
        public readonly string $prefix = '',
        public readonly float $chance = 1.0,
    ) {
    }
}
