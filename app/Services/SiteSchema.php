<?php

namespace App\Services;

use App\Models\ContentEntry;
use Illuminate\Support\Collection;

/**
 * Structured data (schema.org JSON-LD) for the public site, built only from facts the
 * owner has entered: contact details from Admin › Homepage and FAQs from the CMS.
 * Nothing is added that the site does not already show (no invented ratings or hours).
 */
class SiteSchema
{
    /**
     * @param  array<string, mixed>  $contact  name, logo, email, phone, address, social links
     * @return array<string, mixed>
     */
    public function organization(array $contact): array
    {
        return array_filter([
            '@type' => 'Organization',
            '@id' => route('home').'#organization',
            'name' => $contact['name'] ?? null,
            'url' => route('home'),
            'logo' => $contact['logo'] ?? null,
            'email' => $contact['email'] ?? null,
            'telephone' => $contact['phone'] ?? null,
            'sameAs' => array_values(array_filter((array) ($contact['social'] ?? []), static fn (mixed $url): bool => is_string($url) && str_starts_with($url, 'https://'))) ?: null,
        ]);
    }

    /**
     * The walk-in office, only when an address is known.
     *
     * @param  array<string, mixed>  $contact
     * @return array<string, mixed>|null
     */
    public function localBusiness(array $contact): ?array
    {
        if (blank($contact['address'] ?? null)) {
            return null;
        }

        return array_filter([
            '@type' => ['LocalBusiness', 'TravelAgency', 'EducationalOrganization'],
            '@id' => route('home').'#office',
            'name' => $contact['name'] ?? null,
            'url' => route('home'),
            'image' => asset('images/malaysia/petronas-towers-night.webp'),
            'logo' => $contact['logo'] ?? null,
            'email' => $contact['email'] ?? null,
            'telephone' => $contact['phone'] ?? null,
            'address' => ['@type' => 'PostalAddress', 'streetAddress' => $contact['address'], 'addressLocality' => 'Dhaka', 'addressCountry' => 'BD'],
            'parentOrganization' => ['@id' => route('home').'#organization'],
        ]);
    }

    /**
     * @param  Collection<int, ContentEntry>  $faqs
     * @return array<string, mixed>|null
     */
    public function faqPage(Collection $faqs): ?array
    {
        $questions = $faqs->map(fn (ContentEntry $faq): ?array => filled($faq->localized('title')) && filled($answer = $faq->localized('body') ?: $faq->localized('summary'))
            ? ['@type' => 'Question', 'name' => (string) $faq->localized('title'), 'acceptedAnswer' => ['@type' => 'Answer', 'text' => trim(strip_tags((string) $answer))]]
            : null)->filter()->values()->all();

        return $questions === [] ? null : ['@type' => 'FAQPage', 'mainEntity' => $questions];
    }

    /**
     * One JSON-LD script with all given nodes in a @graph.
     *
     * @param  list<array<string, mixed>|null>  $nodes
     */
    public function script(array $nodes): string
    {
        $graph = array_values(array_filter($nodes));
        if ($graph === []) {
            return '';
        }

        return '<script type="application/ld+json">'.json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG).'</script>';
    }
}
