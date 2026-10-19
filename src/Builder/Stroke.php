<?php

declare(strict_types=1);

namespace Dramatar\Builder;

/** How each step of a spirograph curve is drawn. */
enum Stroke
{
    case Line;
    case Ellipse;
    case Rectangle;
    /** Solid line whose hue advances one step per segment. */
    case Rainbow;
}
