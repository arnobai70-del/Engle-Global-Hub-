<?php

namespace App\Services\Travel;

class TravelDemoCatalog
{
    /** @return array<int, array<string, mixed>> */
    public function hotels(): array
    {
        return [
            [
                'id' => 'demo-dubai-marina',
                'name' => 'Dubai Marina Sample Stay',
                'location' => 'Dubai, United Arab Emirates',
                'summary' => 'Sample premium city stay card for demonstrating the hotel experience.',
                'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=85',
                'amenities' => ['Free Wi-Fi', 'Breakfast', 'Pool'],
                'sample_rating' => '4.8',
                'sample_price' => 'USD 145',
                'sample_price_note' => 'Sample from price / night',
                'demo' => true,
            ],
            [
                'id' => 'demo-bali-resort',
                'name' => 'Bali Garden Sample Resort',
                'location' => 'Bali, Indonesia',
                'summary' => 'Sample tropical resort card with family-friendly facilities and a relaxed setting.',
                'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=85',
                'amenities' => ['Pool', 'Airport transfer', 'Family rooms'],
                'sample_rating' => '4.7',
                'sample_price' => 'USD 118',
                'sample_price_note' => 'Sample from price / night',
                'demo' => true,
            ],
            [
                'id' => 'demo-london-central',
                'name' => 'London Central Sample Hotel',
                'location' => 'London, United Kingdom',
                'summary' => 'Sample central-city hotel card designed to preview a future live supplier result.',
                'image' => 'https://images.unsplash.com/photo-1455587734955-081b22074882?auto=format&fit=crop&w=1200&q=85',
                'amenities' => ['Breakfast', 'City centre', '24/7 desk'],
                'sample_rating' => '4.6',
                'sample_price' => 'GBP 132',
                'sample_price_note' => 'Sample from price / night',
                'demo' => true,
            ],
            [
                'id' => 'demo-singapore-bay',
                'name' => 'Singapore Bay Sample Stay',
                'location' => 'Singapore',
                'summary' => 'Sample modern stay with business-friendly amenities and quick city access.',
                'image' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=85',
                'amenities' => ['Gym', 'Workspace', 'Wi-Fi'],
                'sample_rating' => '4.5',
                'sample_price' => 'SGD 188',
                'sample_price_note' => 'Sample from price / night',
                'demo' => true,
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function tours(): array
    {
        return [
            [
                'id' => 'demo-dubai-city',
                'title' => 'Sample Dubai City Highlights Tour',
                'location' => 'Dubai, United Arab Emirates',
                'summary' => 'Sample guided city experience showing how live activities will appear after provider connection.',
                'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1200&q=85',
                'duration' => 'Sample: 4 hours',
                'category' => 'City Tours',
                'sample_price' => 'USD 42',
                'demo' => true,
            ],
            [
                'id' => 'demo-bali-culture',
                'title' => 'Sample Bali Culture & Temple Experience',
                'location' => 'Bali, Indonesia',
                'summary' => 'Sample cultural activity card with a flexible itinerary preview.',
                'image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1200&q=85',
                'duration' => 'Sample: 7 hours',
                'category' => 'Cultural',
                'sample_price' => 'USD 55',
                'demo' => true,
            ],
            [
                'id' => 'demo-london-family',
                'title' => 'Sample London Family Discovery Tour',
                'location' => 'London, United Kingdom',
                'summary' => 'Sample family-friendly sightseeing card for demonstrating category and duration details.',
                'image' => 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&w=1200&q=85',
                'duration' => 'Sample: 3 hours',
                'category' => 'Family',
                'sample_price' => 'GBP 38',
                'demo' => true,
            ],
            [
                'id' => 'demo-desert-adventure',
                'title' => 'Sample Desert Adventure Experience',
                'location' => 'United Arab Emirates',
                'summary' => 'Sample adventure activity card. No live seat, vehicle or schedule is represented.',
                'image' => 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?auto=format&fit=crop&w=1200&q=85',
                'duration' => 'Sample: 6 hours',
                'category' => 'Adventure',
                'sample_price' => 'USD 68',
                'demo' => true,
            ],
        ];
    }

    /** @return array<int, array<string, string>> */
    public function visaServices(): array
    {
        return [
            [
                'title' => 'Tourist Visa Assistance',
                'summary' => 'Checklist guidance, document review and application preparation support for leisure travel.',
                'icon' => '✈',
            ],
            [
                'title' => 'Business Visa Assistance',
                'summary' => 'Preparation support for business visits, meetings, conferences and trade travel.',
                'icon' => '▣',
            ],
            [
                'title' => 'Student Visa Guidance',
                'summary' => 'Document and process guidance for study and academic travel routes where applicable.',
                'icon' => '◆',
            ],
        ];
    }

    /** @return array<int, array<string, string>> */
    public function visaSteps(): array
    {
        return [
            ['number' => '01', 'title' => 'Share your trip', 'summary' => 'Select passport nationality, destination and planned travel dates.'],
            ['number' => '02', 'title' => 'Review requirements', 'summary' => 'We organise the applicable checklist and explain what evidence may be needed.'],
            ['number' => '03', 'title' => 'Prepare documents', 'summary' => 'Documents are checked for completeness before any supported submission step.'],
            ['number' => '04', 'title' => 'Authority decision', 'summary' => 'Visa approval or refusal is decided only by the relevant government authority.'],
        ];
    }

    /** @return array<int, string> */
    public function visaDocuments(): array
    {
        return [
            'Valid passport and passport-size photographs',
            'Travel itinerary and accommodation evidence where required',
            'Financial or employment evidence where required',
            'Supporting letters based on the visa category',
        ];
    }

    /** @param array<string, mixed> $criteria @return array<string, mixed> */
    public function visaRequirementPreview(array $criteria): array
    {
        return [
            'summary' => 'Demo Preview: sample content shown for demonstration. Live, trip-specific requirements will appear when the visa information provider is connected.',
            'requirements' => [
                'Sample requirement: passport validity rules vary by destination and travel purpose.',
                'Sample requirement: evidence of funds, travel plans or host details may be requested.',
                'Sample requirement: processing rules and eligibility are set by the relevant authority.',
            ],
            'documents' => $this->visaDocuments(),
            'demo' => true,
        ];
    }

    /** @return array<int, array<string, string>> */
    public function workVisaDestinations(): array
    {
        return [
            ['name' => 'Canada', 'note' => 'Sample destination', 'image' => 'https://images.unsplash.com/photo-1517935706615-2717063c2225?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Australia', 'note' => 'Sample destination', 'image' => 'https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'United Kingdom', 'note' => 'Sample destination', 'image' => 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Germany', 'note' => 'Sample destination', 'image' => 'https://images.unsplash.com/photo-1560969184-10fe8719e047?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'United Arab Emirates', 'note' => 'Sample destination', 'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'New Zealand', 'note' => 'Sample destination', 'image' => 'https://images.unsplash.com/photo-1469521669194-babb45599def?auto=format&fit=crop&w=1000&q=85'],
        ];
    }

    /** @return array<int, array<string, string>> */
    public function workVisaServices(): array
    {
        return [
            ['title' => 'Skilled Worker', 'summary' => 'Eligibility-document preparation for skilled migration routes where applicable.'],
            ['title' => 'Employer Sponsored', 'summary' => 'Document preparation for genuine employer-supported applications; sponsorship is never promised.'],
            ['title' => 'Job Visa & Work Permits', 'summary' => 'Application-preparation support for work permits and job-visa pathways.'],
            ['title' => 'Document Assistance', 'summary' => 'Checklist review, translation and attestation guidance where required.'],
        ];
    }
}
