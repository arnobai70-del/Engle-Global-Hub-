<?php

namespace App\Services\Travel;

/**
 * Dedicated service pages for services that previously had only an enquiry form.
 * Every page reuses an existing backend: the service enquiry form, the student
 * consultation form, managed destination lists and provider status. No booking,
 * pricing or availability is invented here.
 */
class ServiceCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return [
            'student-visa' => [
                'title' => 'Student Visa', 'group' => 'study', 'icon' => 'cap', 'image' => '1541339907198-e08756dedf3f',
                'summary' => 'Study abroad consultancy with Malaysia as our main destination, then Singapore, plus Bulgaria, the UK, Japan, South Korea and Cyprus.',
                'points' => ['Course and institution shortlisting', 'Admission application and document checklist', 'Student visa or Student\'s Pass preparation', 'Air ticket, accommodation and arrival support'],
                'countries' => 'student_visa', 'country_link' => 'consultation',
                'actions' => [['label' => 'Free Consultation', 'route' => ['consultancy.singapore'], 'fragment' => 'consultation', 'primary' => true], ['label' => 'Study in Singapore', 'route' => ['consultancy.singapore']], ['label' => 'Singapore Guides', 'route' => ['consultancy.guides.index']]],
                'mode' => 'enquiry', 'apis' => [],
            ],
            'tourist-visa' => [
                'title' => 'Tourist Visa', 'group' => 'visa', 'icon' => 'passport', 'image' => '1488646953014-85cb44e25828',
                'summary' => 'Checklist guidance, document review and application preparation for tourist visas to 21 destinations.',
                'points' => ['Destination-specific document checklist', 'Review of your documents before submission', 'Appointment and application preparation', 'Travel itinerary and hotel booking support'],
                'countries' => 'tourist_visa', 'country_link' => 'enquiry', 'enquiry' => 'tourist-visa',
                'mode' => 'enquiry', 'apis' => ['visa'],
            ],
            'business-visa' => [
                'title' => 'Business Visa', 'group' => 'visa', 'icon' => 'briefcase', 'image' => '1497366216548-37526070297c',
                'summary' => 'Preparation support for business visits, meetings, conferences and trade travel.',
                'points' => ['Invitation and supporting letter checklist', 'Document review before submission', 'Travel and hotel arrangements for the trip'],
                'countries' => 'tourist_visa', 'country_link' => 'enquiry', 'enquiry' => 'business-visa',
                'mode' => 'enquiry', 'apis' => ['visa'],
            ],
            'air-ticket' => [
                'title' => 'Air Ticket', 'group' => 'travel', 'icon' => 'plane', 'image' => '1436491865332-7a61a109cc05',
                'summary' => 'Domestic and international air tickets: search fares when the flight provider is live, or ask our team for a quote.',
                'points' => ['One-way, return and multi-city trips', 'Student and family travel', 'Business class quotes', 'Changes and booking support from our team'],
                'mode' => 'provider', 'capability' => 'flights', 'apis' => ['flights', 'payments'],
                'actions' => [['label' => 'Send a flight enquiry', 'route' => ['flights.enquiries.create'], 'primary' => true, 'auth' => true]],
            ],
            'business-class' => [
                'title' => 'Business Class', 'group' => 'travel', 'icon' => 'seat', 'image' => '1436491865332-7a61a109cc05',
                'summary' => 'Business class fares are quoted personally by our team through a flight enquiry.',
                'points' => ['Fare comparison across airlines', 'Lounge, baggage and seat details explained', 'Corporate and family travel'],
                'mode' => 'enquiry', 'enquiry' => 'business-class', 'apis' => ['flights'],
            ],
            'holiday-packages' => [
                'title' => 'Holiday Packages', 'group' => 'travel', 'icon' => 'sun', 'image' => '1507525428034-b723cf961d3e',
                'summary' => 'Holiday packages planned around your dates, budget and destination, with flights, hotels and tours together.',
                'points' => ['Family and honeymoon holidays', 'Flights, hotels and transfers together', 'Visa support where required'],
                'mode' => 'enquiry', 'enquiry' => 'tour-package', 'category' => 'holiday_package', 'apis' => ['tours'],
            ],
            'group-tours' => [
                'title' => 'Group Tours', 'group' => 'travel', 'icon' => 'group', 'image' => '1529156069898-49953e39b3ac',
                'summary' => 'Tours planned for groups of friends, families, students and companies.',
                'points' => ['Group flights and accommodation', 'Itinerary planning', 'One contact person for the whole group'],
                'mode' => 'enquiry', 'enquiry' => 'tour-package', 'category' => 'group_tour', 'apis' => ['tours'],
            ],
            'private-tours' => [
                'title' => 'Private Tours', 'group' => 'travel', 'icon' => 'mountain', 'image' => '1469474968028-56623f02e42e',
                'summary' => 'Private tours with your own schedule, transport and guide.',
                'points' => ['Flexible day plans', 'Private transport', 'Hotels chosen for you'],
                'mode' => 'enquiry', 'enquiry' => 'tour-package', 'category' => 'private_tour', 'apis' => ['tours'],
            ],
            'trains' => [
                'title' => 'Trains', 'group' => 'transport', 'icon' => 'train', 'image' => '1474487548417-781cb71495f3',
                'summary' => 'Train ticket support for your route and dates.',
                'points' => ['Route and timetable advice', 'Ticket booking support by our team'],
                'mode' => 'enquiry', 'enquiry' => 'train', 'apis' => ['transport'],
            ],
            'bus' => [
                'title' => 'Bus', 'group' => 'transport', 'icon' => 'bus', 'image' => '1544620347-c4fd4a3d5957',
                'summary' => 'Bus ticket support for local and cross-border routes.',
                'points' => ['Operator and route advice', 'Ticket booking support by our team'],
                'mode' => 'enquiry', 'enquiry' => 'bus', 'apis' => ['transport'],
            ],
            'cars' => [
                'title' => 'Cars', 'group' => 'transport', 'icon' => 'car', 'image' => '1549317661-bd32c8ce0db2',
                'summary' => 'Car hire and airport transfers with a driver.',
                'points' => ['Airport pick-up and drop-off', 'Day hire and city transfers'],
                'mode' => 'enquiry', 'enquiry' => 'car', 'apis' => ['transport'],
            ],
            'bike' => [
                'title' => 'Bike', 'group' => 'transport', 'icon' => 'bike', 'image' => '1558981403-c5f9899a28bc',
                'summary' => 'Bike rental support for local trips.',
                'points' => ['Rental arrangements through our team'],
                'mode' => 'enquiry', 'enquiry' => 'bike', 'apis' => ['transport'],
            ],
            'foreigner-support' => [
                'title' => 'Foreigner Support', 'group' => 'business', 'icon' => 'globe', 'image' => '1488646953014-85cb44e25828',
                'summary' => 'Help for foreign visitors and students in Bangladesh: travel, accommodation, local transport and visa information.',
                'points' => ['Travel assistance', 'Accommodation', 'Local transport', 'Visa information', 'Student support'],
                'mode' => 'enquiry', 'enquiry' => 'foreigner-support', 'apis' => [],
            ],
            'export-import' => [
                'title' => 'Export & Import', 'group' => 'business', 'icon' => 'box', 'image' => '1494412574643-ff11b0a5c1c3',
                'summary' => 'Enquiries for export and import services. Our team replies personally.',
                'points' => ['Export enquiries', 'Import enquiries'],
                'mode' => 'enquiry', 'enquiry' => 'export-import', 'cms' => 'export-import', 'apis' => [],
            ],
            'civil-construction' => [
                'title' => 'Civil & Construction Work', 'group' => 'business', 'icon' => 'build', 'image' => '1503387762-592deb58ef4e',
                'summary' => 'Enquiries for civil construction and land-related work. Our team replies personally.',
                'points' => ['Civil construction', 'Land-related work'],
                'mode' => 'enquiry', 'enquiry' => 'civil-construction', 'cms' => 'civil-construction', 'apis' => [],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $slug): ?array
    {
        return $this->all()[$slug] ?? null;
    }
}
