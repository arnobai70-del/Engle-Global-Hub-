<?php

namespace App\Enums;

/**
 * The services Eagle Global Hub offers, from the business requirements
 * document. A service being listed does not mean it can be booked online.
 */
enum BusinessService: string
{
    case StudentVisa = 'student_visa';
    case TouristVisa = 'tourist_visa';
    case BusinessVisa = 'business_visa';
    case WorkVisa = 'work_visa';
    case AirTicket = 'air_ticket';
    case HotelBooking = 'hotel_booking';
    case TourPackage = 'tour_package';
    case Train = 'train';
    case Bus = 'bus';
    case Car = 'car';
    case Bike = 'bike';
    case ExportImport = 'export_import';
    case CivilConstruction = 'civil_construction';
    case ForeignerSupport = 'foreigner_support';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::StudentVisa => 'Student Visa',
            self::TouristVisa => 'Tourist Visa',
            self::BusinessVisa => 'Business Visa',
            self::WorkVisa => 'Job / Work Visa',
            self::AirTicket => 'Air Ticket',
            self::HotelBooking => 'Hotel Booking',
            self::TourPackage => 'Tour Packaging',
            self::Train => 'Trains',
            self::Bus => 'Bus',
            self::Car => 'Cars',
            self::Bike => 'Bike',
            self::ExportImport => 'Export & Import',
            self::CivilConstruction => 'Civil & Construction Work',
            self::ForeignerSupport => 'Foreigner Support',
            self::General => 'General question',
        };
    }

    /**
     * Whether staff manage a list of destination countries for this service.
     */
    public function hasDestinations(): bool
    {
        return in_array($this, [self::StudentVisa, self::TouristVisa, self::BusinessVisa, self::WorkVisa], true);
    }

    /**
     * @return list<self>
     */
    public static function withDestinations(): array
    {
        return array_values(array_filter(self::cases(), fn (self $service): bool => $service->hasDestinations()));
    }
}
