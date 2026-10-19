<?php

declare(strict_types=1);

namespace Dramatar;

use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Seeded random source. The same seed string always produces the same sequence.
 */
final class Rng
{
    private Randomizer $random;

    public function __construct(string $seed)
    {
        $int = (int) hexdec(substr(md5($seed === '' ? '0' : $seed), 0, 8));
        $this->random = new Randomizer(new Mt19937($int));
    }

    /** Inclusive integer range. */
    public function int(int $min, int $max): int
    {
        return $this->random->getInt($min, $max);
    }

    /** True with the given probability (0..1). */
    public function chance(float $probability): bool
    {
        return $probability >= 1.0 || $this->int(0, 999999) < (int) ($probability * 1000000);
    }

    /**
     * @template T
     * @param non-empty-list<T> $items
     * @return T
     */
    public function pick(array $items): mixed
    {
        return $items[$this->int(0, count($items) - 1)];
    }
}
