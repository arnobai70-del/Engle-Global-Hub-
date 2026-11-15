<?php

namespace App\Enums;

/**
 * Lifecycle of a study-abroad application handled by Eagle Global Hub staff.
 *
 * Statuses are manual staff records. They never imply that an institution or
 * Singapore's ICA has made a decision unless staff record that decision after
 * receiving it from the official channel.
 */
enum StudentApplicationStatus: string
{
    case NewEnquiry = 'new_enquiry';
    case ConsultationScheduled = 'consultation_scheduled';
    case EligibilityReview = 'eligibility_review';
    case DocumentsPending = 'documents_pending';
    case DocumentsVerified = 'documents_verified';
    case AdmissionApplied = 'admission_applied';
    case OfferReceived = 'offer_received';
    case StudentsPassPreparation = 'students_pass_preparation';
    case ApplicationSubmitted = 'application_submitted';
    case IpaIssued = 'ipa_issued';
    case FurtherActionRequired = 'further_action_required';
    case FormalitiesPending = 'formalities_pending';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::NewEnquiry => 'New Enquiry',
            self::ConsultationScheduled => 'Consultation Scheduled',
            self::EligibilityReview => 'Eligibility Review',
            self::DocumentsPending => 'Documents Pending',
            self::DocumentsVerified => 'Documents Verified',
            self::AdmissionApplied => 'Admission Applied',
            self::OfferReceived => 'Offer Received',
            self::StudentsPassPreparation => "Student's Pass Preparation",
            self::ApplicationSubmitted => "Student's Pass Application Submitted",
            self::IpaIssued => 'IPA Issued',
            self::FurtherActionRequired => 'Further Action Required',
            self::FormalitiesPending => 'Formalities Pending',
            self::Completed => 'Completed',
            self::Rejected => 'Rejected',
            self::Withdrawn => 'Withdrawn',
        };
    }

    /**
     * The workflow the status belongs to. Admission and Student's Pass are
     * tracked as separate stages of one connected application.
     */
    public function stage(): string
    {
        return match ($this) {
            self::NewEnquiry, self::ConsultationScheduled, self::EligibilityReview => 'Consultation',
            self::DocumentsPending, self::DocumentsVerified, self::AdmissionApplied, self::OfferReceived => 'Admission',
            self::StudentsPassPreparation, self::ApplicationSubmitted, self::IpaIssued, self::FormalitiesPending => "Student's Pass",
            self::FurtherActionRequired => 'Action Required',
            self::Completed, self::Rejected, self::Withdrawn => 'Closed',
        };
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::NewEnquiry => [self::ConsultationScheduled, self::EligibilityReview, self::Withdrawn],
            self::ConsultationScheduled => [self::EligibilityReview, self::Withdrawn],
            self::EligibilityReview => [self::DocumentsPending, self::Rejected, self::Withdrawn],
            self::DocumentsPending => [self::DocumentsVerified, self::FurtherActionRequired, self::Withdrawn],
            self::DocumentsVerified => [self::AdmissionApplied, self::DocumentsPending, self::Withdrawn],
            self::AdmissionApplied => [self::OfferReceived, self::FurtherActionRequired, self::Rejected, self::Withdrawn],
            self::OfferReceived => [self::StudentsPassPreparation, self::Withdrawn],
            self::StudentsPassPreparation => [self::ApplicationSubmitted, self::FurtherActionRequired, self::Withdrawn],
            self::ApplicationSubmitted => [self::IpaIssued, self::FurtherActionRequired, self::Rejected, self::Withdrawn],
            self::FurtherActionRequired => [
                self::DocumentsPending, self::DocumentsVerified, self::AdmissionApplied,
                self::StudentsPassPreparation, self::ApplicationSubmitted, self::Withdrawn,
            ],
            self::IpaIssued => [self::FormalitiesPending, self::Withdrawn],
            self::FormalitiesPending => [self::Completed, self::Withdrawn],
            self::Completed, self::Rejected, self::Withdrawn => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isClosed(): bool
    {
        return $this->allowedTransitions() === [];
    }
}
