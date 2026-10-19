<?php

declare(strict_types=1);

// Fallback autoloader for use without Composer.
spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'Dramatar\\')) {
        $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, 9)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});
