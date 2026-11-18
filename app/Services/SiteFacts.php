<?php

namespace App\Services;

use Throwable;

/**
 * Business facts and numbers shown on the public website (the "[X]" values in the
 * design brief). Every value is entered by the owner in Admin › Business facts and
 * starts empty: the website hides an element whose value is not filled in, so no
 * number, licence or rating is ever invented.
 */
class SiteFacts
{
    public const GROUP = 'facts';

    /**
     * Editable fields: key => [label, help, max length, section].
     *
     * @var array<string, array{0: string, 1: string, 2: int, 3: string}>
     */
    public const FIELDS = [
        'whatsapp_number' => ['WhatsApp number', 'Shown on every page as the floating WhatsApp button. Leave empty to use the main phone number.', 20, 'Contact'],
        'office_hours' => ['Office hours', 'For example: Sat–Thu 10am–7pm, Friday closed.', 80, 'Contact'],
        'reply_time' => ['Usual reply time', 'For example: Within 1 working day. Shown on service pages and the contact page.', 60, 'Contact'],
        'map_embed_url' => ['Google Map embed link', 'In Google Maps: Share › Embed a map › copy only the https://www.google.com/maps/embed?… link.', 600, 'Contact'],
        'office_photo_url' => ['Office photo (https link)', 'A photo of your reception or signboard, for the contact page.', 300, 'Contact'],

        'trade_licence_no' => ['Trade licence number', 'Shown in the trust strip, About page and footer.', 60, 'Company'],
        'company_registration_no' => ['Company registration number (RJSC)', 'Shown on the About page and in the footer.', 60, 'Company'],
        'memberships' => ['Association memberships', 'One per line, for example: ATAB member no. 1234. Only memberships you can prove.', 400, 'Company'],
        'founded_year' => ['Year founded', 'For example: 2019.', 4, 'Company'],
        'students_guided' => ['Students guided (number)', 'Only a number you can prove. Shown as "[number]+ students guided".', 7, 'Company'],
        'partner_institutions' => ['Partner institutions (number)', 'Only institutions you have a written agreement with.', 5, 'Company'],
        'visa_files_processed' => ['Visa files processed (number)', 'Only a number you can prove. Shown as "[number]+ visa files processed".', 7, 'Company'],
        'about_story' => ['About us: your story', '2–3 sentences in your own words: why the company started, who you help, what makes you different.', 800, 'Company'],
        'team_photo_url' => ['Team photo (https link)', 'A real photo of the team in the office, for the About page.', 300, 'Company'],

        'google_rating' => ['Google rating', 'For example: 4.8. Shown only together with the review count and link.', 3, 'Reviews'],
        'google_review_count' => ['Number of Google reviews', 'For example: 120.', 6, 'Reviews'],
        'google_reviews_url' => ['Google reviews link (https)', 'The public link to your Google reviews.', 300, 'Reviews'],
        'google_maps_url' => ['Google Maps link to the office (https)', 'Your Google Maps business page, shown on the About and Contact pages and in the footer.', 300, 'Reviews'],

        'my_intakes' => ['Malaysia intakes', 'For example: Jan · May · Sep. Shown on Study in Malaysia.', 80, 'Malaysia costs (MYR)'],
        'my_cost_tuition' => ['Tuition per year (MYR)', 'Diploma at a private institution, yearly estimate.', 20, 'Malaysia costs (MYR)'],
        'my_cost_housing' => ['Housing per year (MYR)', 'Shared room, yearly estimate.', 20, 'Malaysia costs (MYR)'],
        'my_cost_food' => ['Food & transport per year (MYR)', '', 20, 'Malaysia costs (MYR)'],
        'my_cost_visa' => ['Student Pass, insurance & medical (MYR)', 'EMGS sets these fees; check the official EMGS fee calculator before entering a figure.', 20, 'Malaysia costs (MYR)'],
        'my_cost_total' => ['Estimated total per year (MYR)', '', 20, 'Malaysia costs (MYR)'],
        'my_bdt_rate' => ['Exchange rate: 1 MYR in Taka (BDT)', 'For the cost calculator, for example 27.50. Update it when the rate moves. Leave empty to hide the calculator.', 10, 'Malaysia costs (MYR)'],
        'my_bdt_rate_date' => ['Exchange rate checked on', 'Shown under the calculator, for example 18 Nov 2026.', 30, 'Malaysia costs (MYR)'],
        'my_living_room' => ['Monthly: shared room (MYR)', '', 20, 'Malaysia monthly living cost (MYR)'],
        'my_living_food' => ['Monthly: food (MYR)', '', 20, 'Malaysia monthly living cost (MYR)'],
        'my_living_transport' => ['Monthly: public transport (MYR)', '', 20, 'Malaysia monthly living cost (MYR)'],
        'my_living_phone' => ['Monthly: phone & personal (MYR)', '', 20, 'Malaysia monthly living cost (MYR)'],

        'intakes' => ['Singapore intakes', 'For example: Jan · Apr · Jul · Oct.', 80, 'Singapore costs (SGD)'],
        'cost_tuition' => ['Tuition per year (SGD)', 'Diploma at a private institution, yearly estimate.', 20, 'Singapore costs (SGD)'],
        'cost_housing' => ['Housing per year (SGD)', 'Shared room, yearly estimate.', 20, 'Singapore costs (SGD)'],
        'cost_food' => ['Food & transport per year (SGD)', '', 20, 'Singapore costs (SGD)'],
        'cost_pass' => ['Student\'s Pass & medical (SGD)', '', 20, 'Singapore costs (SGD)'],
        'our_fee' => ['Our service fee (BDT)', 'Your consultancy fee, as you quote it in writing.', 30, 'Singapore costs (SGD)'],
        'cost_total' => ['Estimated total per year (SGD)', '', 20, 'Singapore costs (SGD)'],
        'living_room' => ['Monthly: shared room (SGD)', '', 20, 'Singapore monthly living cost (SGD)'],
        'living_food' => ['Monthly: food (SGD)', '', 20, 'Singapore monthly living cost (SGD)'],
        'living_transport' => ['Monthly: public transport (SGD)', '', 20, 'Singapore monthly living cost (SGD)'],
        'living_phone' => ['Monthly: phone & personal (SGD)', '', 20, 'Singapore monthly living cost (SGD)'],
    ];

    public function __construct(private SettingService $settings) {}

    public function get(string $key): ?string
    {
        $value = trim((string) ($this->all()[$key] ?? ''));

        return $value === '' ? null : $value;
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        // Remembered for the current request only; SettingService already caches across requests.
        $request = request();
        $values = $request->attributes->get('egh.site_facts');
        if (! is_array($values)) {
            try {
                $values = array_map(static fn (mixed $value): string => (string) $value, $this->settings->group(self::GROUP));
            } catch (Throwable) {
                $values = [];
            }
            $request->attributes->set('egh.site_facts', $values);
        }

        return $values;
    }

    /**
     * The "fee from" amount for a service page, if the owner entered one.
     */
    public function serviceFee(string $slug): ?string
    {
        return $this->get('fee_'.str_replace('-', '_', $slug));
    }

    public function phone(): ?string
    {
        try {
            $phone = trim((string) $this->settings->get('contact', 'phone'));
        } catch (Throwable) {
            $phone = '';
        }

        return $phone === '' ? null : $phone;
    }

    public function whatsappNumber(): ?string
    {
        return $this->get('whatsapp_number') ?? $this->phone();
    }

    /**
     * A wa.me link with an optional pre-filled message. Bangladeshi local numbers
     * (01…) are converted to the international form (8801…).
     */
    public function whatsappUrl(?string $message = null): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->whatsappNumber());
        if ($digits === null || strlen($digits) < 8) {
            return null;
        }
        if (str_starts_with($digits, '01') && strlen($digits) === 11) {
            $digits = '88'.$digits;
        }

        return 'https://wa.me/'.$digits.($message !== null && $message !== '' ? '?text='.rawurlencode($message) : '');
    }

    public function telUrl(): ?string
    {
        $phone = $this->phone();

        return $phone === null ? null : 'tel:'.preg_replace('/[^0-9+]/', '', $phone);
    }

    /**
     * A Google Maps embed URL, only when it is a real https Google embed link.
     */
    public function mapEmbedUrl(): ?string
    {
        $url = $this->get('map_embed_url');

        return $url !== null && preg_match('#^https://(www\.)?google\.[a-z.]+/maps/embed\?#i', $url) === 1 ? $url : null;
    }

    /**
     * The Google rating line, only when rating, count and link are all filled in.
     *
     * @return array{rating: string, count: string, url: string}|null
     */
    /**
     * "Our numbers": only the figures the owner entered, never a default.
     *
     * @return list<array{value: string, label: string}>
     */
    public function numbers(): array
    {
        $founded = $this->get('founded_year');
        $years = $founded !== null && ctype_digit($founded) ? (int) now()->format('Y') - (int) $founded : null;

        return array_values(array_filter([
            $this->get('students_guided') ? ['value' => $this->get('students_guided').'+', 'label' => __('students guided')] : null,
            $years !== null && $years > 0 ? ['value' => (string) $years, 'label' => trans_choice('year in business|years in business', $years)] : null,
            $this->get('partner_institutions') ? ['value' => (string) $this->get('partner_institutions'), 'label' => __('partner institutions')] : null,
            $this->get('visa_files_processed') ? ['value' => $this->get('visa_files_processed').'+', 'label' => __('visa files processed')] : null,
        ]));
    }

    /**
     * Company credentials for the About page and footer, only those the owner entered.
     *
     * @return array{trade_licence: ?string, registration: ?string, memberships: list<string>, maps_url: ?string, reviews_url: ?string}
     */
    public function credentials(): array
    {
        $https = fn (string $key): ?string => ($url = $this->get($key)) !== null && str_starts_with($url, 'https://') ? $url : null;

        return [
            'trade_licence' => $this->get('trade_licence_no'),
            'registration' => $this->get('company_registration_no'),
            'memberships' => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->get('memberships')) ?: []))),
            'maps_url' => $https('google_maps_url'),
            'reviews_url' => $https('google_reviews_url'),
        ];
    }

    public function googleRating(): ?array
    {
        $rating = $this->get('google_rating');
        $count = $this->get('google_review_count');
        $url = $this->get('google_reviews_url');

        return $rating !== null && $count !== null && $url !== null && str_starts_with($url, 'https://')
            ? ['rating' => $rating, 'count' => $count, 'url' => $url]
            : null;
    }

    /**
     * Rows of a cost table whose values are filled in.
     *
     * @param  array<string, string>  $labels  key => label
     * @return list<array{label: string, value: string}>
     */
    public function rows(array $labels, string $prefix = 'SGD '): array
    {
        $rows = [];
        foreach ($labels as $key => $label) {
            $value = $this->get($key);
            if ($value !== null) {
                $rows[] = ['label' => $label, 'value' => (preg_match('/^[\d.,\s–-]+$/u', $value) === 1 ? $prefix : '').$value];
            }
        }

        return $rows;
    }

    public function forget(): void
    {
        request()->attributes->remove('egh.site_facts');
    }
}
