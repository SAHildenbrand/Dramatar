<?php

declare(strict_types=1);

namespace Dramatar\Builder;

/** Flat-colour face with interchangeable hair, eyes, brows, mouth and extras. Files are blockies/<part>-NN.png. */
final class BlockiesBuilder extends LayeredBuilder
{
    protected function layers(): array
    {
        return [
            new Layer('blockies', 'pants-'),
            new Layer('blockies', 'freckles-', 0.3),
            new Layer('blockies', 'eyes-'),
            new Layer('blockies', 'eyebrows-'),
            new Layer('blockies', 'mouth-'),
            new Layer('blockies', 'moustache-', 0.3),
            new Layer('blockies', 'head-'),
        ];
    }
}
