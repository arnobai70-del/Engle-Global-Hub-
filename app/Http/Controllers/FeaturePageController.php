<?php

namespace App\Http\Controllers;

use App\Models\ContentEntry;
use App\Models\Destination;
use App\Services\SettingService;
use Illuminate\View\View;
use Throwable;

/**
 * Dedicated pages for features whose interactive part depends on an external API
 * (map, mobile app). They show what works today and mark the rest as waiting.
 */
class FeaturePageController extends Controller
{
    public function map(): View
    {
        return view('features.map', [
            'places' => ContentEntry::query()->where('status', ContentEntry::STATUS_PUBLISHED)
                ->whereNotNull('latitude')->whereNotNull('longitude')->orderBy('title')->get(),
            'destinations' => Destination::query()->published()->get(['id', 'name', 'slug', 'summary', 'region']),
        ]);
    }

    public function mobileApp(SettingService $settings): View
    {
        try {
            $homepage = $settings->group('homepage');
        } catch (Throwable) {
            $homepage = [];
        }

        return view('features.mobile-app', [
            'googlePlayUrl' => filter_var($homepage['google_play_url'] ?? null, FILTER_VALIDATE_URL) ?: null,
            'appStoreUrl' => filter_var($homepage['app_store_url'] ?? null, FILTER_VALIDATE_URL) ?: null,
        ]);
    }
}
