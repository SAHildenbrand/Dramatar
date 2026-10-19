<?php

declare(strict_types=1);

namespace Dramatar\Builder;

/** Rag-doll character. */
final class RagdollBuilder extends LayeredBuilder
{
    protected function layers(): array
    {
        return array_map(
            static fn (string $dir) => new Layer("ragdoll/$dir"),
            ['Shadow', 'Frame', 'HairBack', 'Base', 'Pants', 'Shirts', 'Accessories', 'Mouth', 'Nose', 'Eyes', 'Hair'],
        );
    }
}
