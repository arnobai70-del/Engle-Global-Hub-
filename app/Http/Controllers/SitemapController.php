<?php

namespace App\Http\Controllers;

use App\Services\Feature\FeatureManager;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(FeatureManager $features): Response
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('terms'), 'priority' => '0.5'],
        ];

        foreach ([
            'about' => ['route' => 'about', 'priority' => '0.7'],
            'support' => ['route' => 'support', 'priority' => '0.6'],
            'visa' => ['route' => 'work-visa.index', 'priority' => '0.7'],
        ] as $feature => $page) {
            if ($features->isVisibleTo($feature, null)) {
                $urls[] = [
                    'loc' => route($page['route']),
                    'priority' => $page['priority'],
                ];
            }
        }

        return response(
            view('seo.sitemap', ['urls' => $urls])->render(),
            200,
            [
                'Content-Type' => 'application/xml; charset=UTF-8',
                'Cache-Control' => 'public, max-age=3600',
            ],
        );
    }
}
