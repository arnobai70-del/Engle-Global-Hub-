<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves files from the public disk (site logo, hero, team photos, media
 * library) when the server has no working public/storage link, so uploads
 * show on any host. Private documents live on other disks and are never served.
 */
class PublicFileController extends Controller
{
    public function __invoke(string $path): StreamedResponse
    {
        $isHidden = collect(explode('/', $path))->contains(fn (string $segment): bool => Str::startsWith($segment, '.'));

        abort_if($isHidden || ! Storage::disk('public')->fileExists($path), 404);

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=604800',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
