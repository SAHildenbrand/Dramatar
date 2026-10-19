<?php

declare(strict_types=1);

namespace Dramatar;

use Dramatar\Builder\AnimeBuilder;
use Dramatar\Builder\BlockiesBuilder;
use Dramatar\Builder\Builder;
use Dramatar\Builder\DunesBuilder;
use Dramatar\Builder\FlowerBuilder;
use Dramatar\Builder\KaleidoscopeBuilder;
use Dramatar\Builder\SpirographBuilder;
use Dramatar\Builder\RagdollBuilder;
use GdImage;
use InvalidArgumentException;

/**
 * Entry point: Avatar::create('anime')->png($hash, 80)
 */
final class Avatar
{
    /** @var array<string,Builder> */
    private array $builders;

    public function __construct(?string $partsDir = null)
    {
        $parts = $partsDir ?? dirname(__DIR__) . '/parts';
        $this->builders = [
            'anime' => new AnimeBuilder($parts),
            'ragdoll' => new RagdollBuilder($parts),
            'blockies' => new BlockiesBuilder($parts),
            'flower' => new FlowerBuilder($parts),
            'spiro' => SpirographBuilder::spiro(),
            'arc' => SpirographBuilder::arc(),
            'arcii' => SpirographBuilder::arcii(),
            'boxy' => SpirographBuilder::boxy(),
            'kaleidoscope' => new KaleidoscopeBuilder(),
            'dunes' => new DunesBuilder(),
        ];
    }

    /** @return list<string> */
    public function types(): array
    {
        return array_keys($this->builders);
    }

    public function has(string $type): bool
    {
        return isset($this->builders[$type]);
    }

    /** Conventional input hash for an email address (Gravatar-style). */
    public static function hashEmail(string $email): string
    {
        return md5(strtolower(trim($email)));
    }

    /** Build an avatar image at the given pixel size. */
    public function image(string $type, string $hash, int $size = 80): GdImage
    {
        $builder = $this->builders[$type] ?? throw new InvalidArgumentException("Unknown avatar type: $type");
        $size = max(8, min(1024, $size));

        $source = $builder->build(new Rng($hash));
        $out = imagecreatetruecolor($size, $size);
        imagecopyresampled($out, $source, 0, 0, 0, 0, $size, $size, imagesx($source), imagesy($source));
        imagedestroy($source);

        return $out;
    }

    /** Encoded image bytes. $format: png, jpeg, webp, avif or gif. */
    public function render(string $type, string $hash, int $size = 80, string $format = 'png'): string
    {
        $image = $this->image($type, $hash, $size);
        ob_start();
        match ($format) {
            'png' => imagepng($image),
            'jpg', 'jpeg' => imagejpeg($image, null, 90),
            'webp' => imagewebp($image, null, 90),
            'avif' => imageavif($image, null, 80),
            'gif' => imagegif($image),
            default => throw new InvalidArgumentException("Unsupported format: $format"),
        };
        imagedestroy($image);

        return (string) ob_get_clean();
    }
}
