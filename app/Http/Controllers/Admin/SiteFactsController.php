<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityRecorder;
use App\Services\SettingService;
use App\Services\SiteFacts;
use App\Services\Travel\ServiceCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin › Business facts: the owner-supplied numbers and facts the website shows
 * (licence, founding year, ratings, costs, fees). Empty fields stay hidden on the site.
 */
class SiteFactsController extends Controller
{
    public function edit(SiteFacts $facts, ServiceCatalog $catalog): View
    {
        return view('admin.facts.edit', [
            'sections' => collect(SiteFacts::FIELDS)->groupBy(fn (array $field): string => $field[3], true),
            'feeFields' => $this->feeFields($catalog),
            'values' => $facts->all(),
        ]);
    }

    public function update(Request $request, SettingService $settings, ServiceCatalog $catalog, ActivityRecorder $activity): RedirectResponse
    {
        $rules = [];
        foreach (SiteFacts::FIELDS as $key => [, , $max]) {
            $rules[$key] = ['nullable', 'string', 'max:'.$max];
        }
        foreach (array_keys($this->feeFields($catalog)) as $key) {
            $rules[$key] = ['nullable', 'string', 'max:30'];
        }
        $rules['founded_year'][] = 'regex:/^(19|20)\d{2}$/';
        $rules['students_guided'][] = 'regex:/^\d+$/';
        $rules['partner_institutions'][] = 'regex:/^\d+$/';
        $rules['google_rating'][] = 'regex:/^[1-5](\.\d)?$/';
        $rules['google_review_count'][] = 'regex:/^\d+$/';
        $rules['my_bdt_rate'][] = 'regex:/^\d{1,4}(\.\d{1,4})?$/';
        foreach (['google_reviews_url', 'office_photo_url', 'team_photo_url'] as $urlKey) {
            $rules[$urlKey][] = 'url:https';
        }
        $rules['map_embed_url'][] = 'regex:#^https://(www\.)?google\.[a-z.]+/maps/embed\?#i';

        $data = $request->validate($rules, [
            'map_embed_url.regex' => 'Paste the Google Maps embed link (it starts with https://www.google.com/maps/embed?).',
            'google_rating.regex' => 'Enter the rating as shown on Google, for example 4.8.',
            'my_bdt_rate.regex' => 'Enter the exchange rate as a number, for example 27.50.',
        ]);

        foreach ($data as $key => $value) {
            $settings->set(SiteFacts::GROUP, $key, trim(strip_tags((string) $value)), 'string', true);
        }
        $activity->record($request->user(), 'facts.updated', null, ['keys' => array_keys(array_filter($data, 'filled'))]);

        return redirect()->route('admin.facts.edit')->with('status', 'Saved. Empty fields stay hidden on the website.');
    }

    /**
     * "Fee from" fields for every service page: key => service title.
     *
     * @return array<string, string>
     */
    private function feeFields(ServiceCatalog $catalog): array
    {
        $fields = [];
        foreach ($catalog->all() as $slug => $service) {
            $fields['fee_'.str_replace('-', '_', $slug)] = $service['title'];
        }

        return $fields;
    }
}
