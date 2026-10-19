# Dramatar

Deterministic avatar generator. Give it a hash (typically `md5(lowercase(email))`) and a type, and it always returns the same avatar. Modern PHP 8.2+ / GD rewrite of the original Dramatar `lib/builders/*.php` scripts.

Dramatar ("a more dramatic avatar") was a free dynamic-avatar service created in 2010 by Rocket Full, Ltd. Co., with WordPress and vBulletin plugins, and served over half a million avatars by September 2011. The service is no longer running. See [docs/HISTORY.md](docs/HISTORY.md) for the story and artwork credits.

```php
use Dramatar\Avatar;

$avatars = new Avatar();                       // parts live in ./parts
$hash    = Avatar::hashEmail('me@example.com');
file_put_contents('me.png', $avatars->render('anime', $hash, 120));
```

HTTP: point a rewrite at `public/index.php`, then `GET /avatar/{hash}.png?type=anime&s=80` (formats: png, jpg, webp, avif, gif). Responses are cached on disk under `cache/` and served with immutable cache headers and an ETag.

## Types

| Type | Builder | Notes |
|---|---|---|
| `anime`, `ragdoll`, `blockies`, `flower` | `LayeredBuilder` subclasses | Random transparent PNG parts stacked over a coloured background. |
| `spiro`, `arc`, `arcii`, `boxy` | `SpirographBuilder` presets | Two overlaid epicycle curves; the presets differ only in parameter ranges and `Stroke` style. |

Details and samples for every model: [docs/models.md](docs/models.md).

Part files are discovered from `parts/<dir>/`, so new artwork needs no code change. To add a layered type, subclass `LayeredBuilder`, return a `Layer` list (bottom to top), and register it in `Avatar`.

## Differences from the old code

- Same hash gives a stable avatar, but **not the same avatar as the old service**: the old code used PHP's `srand`/`rand`, which isn't reproducible across PHP versions. The seed is now MD5 → `Random\Randomizer` with Mt19937.
- Canvases are square 240x240. The old 240x300 anime/ragdoll canvases were squashed into a square on output; only the top 240 rows were ever used, so those are now cropped instead of distorted.
- Spirograph curves are capped at 6000 steps (originals could loop forever) and drawn at 2x then downsampled for smooth lines.
- `blockies` and `flower` had no surviving builder; their layer stacks (and optional freckles/moustache) are new.
- Dropped: MySQL hit counters, `$_GET` globals, `mysql_*`.

Run `composer test` (or `php tests/smoke.php`).
