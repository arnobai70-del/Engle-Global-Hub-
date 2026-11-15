<?php

namespace App\Enums;

/**
 * Document categories collected for a study application.
 *
 * This is a checklist of categories staff can request. It does not state which
 * documents an institution or ICA requires; staff confirm that per application.
 */
enum StudentDocumentType: string
{
    case PassportBioPage = 'passport_bio_page';
    case Photograph = 'photograph';
    case AcademicCertificate = 'academic_certificate';
    case AcademicTranscript = 'academic_transcript';
    case EnglishTestResult = 'english_test_result';
    case BirthCertificate = 'birth_certificate';
    case FinancialDocument = 'financial_document';
    case OfferLetter = 'offer_letter';
    case StudentsPassForm = 'students_pass_form';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PassportBioPage => 'Passport (bio-data page)',
            self::Photograph => 'Passport-size photograph',
            self::AcademicCertificate => 'Academic certificate',
            self::AcademicTranscript => 'Academic transcript / mark sheet',
            self::EnglishTestResult => 'English test result (e.g. IELTS)',
            self::BirthCertificate => 'Birth certificate',
            self::FinancialDocument => 'Financial / sponsorship document',
            self::OfferLetter => 'Offer letter',
            self::StudentsPassForm => "Student's Pass form / supporting document",
            self::Other => 'Other document',
        };
    }
}
