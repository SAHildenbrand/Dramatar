<?php

declare(strict_types=1);

// Dependency-free smoke test: every builder renders, output is deterministic and varies by hash.
require dirname(__DIR__) . '/src/autoload.php';

use Dramatar\Avatar;

$avatar = new Avatar();
$failures = 0;
$check = function (bool $ok, string $label) use (&$failures): void {
    echo ($ok ? 'ok   ' : 'FAIL ') . $label . PHP_EOL;
    $failures += $ok ? 0 : 1;
};

foreach ($avatar->types() as $type) {
    $a = $avatar->render($type, 'abc123', 64);
    $check(str_starts_with($a, "\x89PNG"), "$type renders a PNG");
    $check($a === $avatar->render($type, 'abc123', 64), "$type is deterministic");
    $img = $avatar->image($type, 'abc123', 64);
    $colors = [];
    for ($x = 0; $x < 64; $x += 2) {
        for ($y = 0; $y < 64; $y += 2) {
            $colors[imagecolorat($img, $x, $y)] = true;
        }
    }
    $check(count($colors) > 3, "$type is not blank");
    $others = array_unique(array_map(fn ($h) => md5($avatar->render($type, $h, 64)), ['one', 'two', 'three', 'four', 'five']));
    $check(count($others) > 1, "$type varies with the hash");
}
$check(!$avatar->has('nonexistent'), 'unknown type is rejected');

exit($failures > 0 ? 1 : 0);
