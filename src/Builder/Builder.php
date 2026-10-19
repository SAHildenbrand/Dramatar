<?php

declare(strict_types=1);

namespace Dramatar\Builder;

use Dramatar\Rng;
use GdImage;

interface Builder
{
    /** Draw an avatar for this seed. The image is square; the caller resizes it. */
    public function build(Rng $rng): GdImage;
}
