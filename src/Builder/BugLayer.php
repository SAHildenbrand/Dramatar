<?php

declare(strict_types=1);

namespace Dramatar\Builder;

use Dramatar\Rng;
use GdImage;

/** Optionally stamps one pre-positioned bug part (flower/bug_N.png) on top of an image. */
final class BugLayer extends LayeredBuilder
{
    public function __construct(string $partsDir, private readonly float $chance = 0.6)
    {
        parent::__construct($partsDir);
    }

    protected function layers(): array
    {
        return [new Layer('flower', 'bug_', $this->chance)];
    }

    public function apply(GdImage $image, Rng $rng): void
    {
        $layer = $this->layers()[0];
        $files = array_values(array_filter(
            glob($this->partsDir . '/flower/bug_*.png') ?: [],
            static fn (string $p) => !str_starts_with(basename($p), '.'),
        ));
        if ($files === [] || !$rng->chance($layer->chance)) {
            return;
        }
        $part = imagecreatefrompng($rng->pick($files));
        imagealphablending($part, true);
        imagecopy($image, $part, 0, 0, 0, 0, imagesx($part), imagesy($part));
        imagedestroy($part);
    }
}
