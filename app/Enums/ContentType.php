<?php

namespace App\Enums;

/**
 * Kinds of admin-managed website content. Each type declares the rules that
 * must hold before an entry can be published, so unverifiable claims stay
 * unpublished.
 */
enum ContentType: string
{
    case CompanyProfile = 'company_profile';
    case BusinessService = 'business_service';
    case Project = 'project';
    case Restaurant = 'restaurant';
    case Inspiration = 'inspiration';
    case SuccessStory = 'success_story';
    case Guide = 'guide';
    case Faq = 'faq';
    case PageSection = 'page_section';

    public function label(): string
    {
        return match ($this) {
            self::CompanyProfile => 'Company Profile',
            self::BusinessService => 'Business Service',
            self::Project => 'Construction Project',
            self::Restaurant => 'Restaurant',
            self::Inspiration => 'Get Inspired Article',
            self::SuccessStory => 'Success Story',
            self::Guide => 'Singapore Study Guide',
            self::Faq => 'FAQ',
            self::PageSection => 'Page Section',
        };
    }

    /**
     * Claims (registration, certificates, completed projects) need explicit
     * owner approval before they are shown publicly.
     */
    public function requiresOwnerApproval(): bool
    {
        return in_array($this, [self::CompanyProfile, self::Project], true);
    }

    /**
     * Immigration, admission and fee information must cite an official source.
     */
    public function requiresOfficialSource(): bool
    {
        return $this === self::Guide;
    }

    /**
     * Success stories are published only with the student's recorded consent.
     */
    public function requiresConsent(): bool
    {
        return $this === self::SuccessStory;
    }

    public function hasLocation(): bool
    {
        return in_array($this, [self::Restaurant, self::Project], true);
    }

    /**
     * Route name of the public page for one entry, if the type has its own pages.
     */
    public function publicRoute(): ?string
    {
        return match ($this) {
            self::CompanyProfile => 'company.show',
            self::BusinessService => 'business-services.show',
            self::Project => 'business-services.projects.show',
            self::Restaurant => 'restaurants.show',
            self::Inspiration => 'inspiration.show',
            self::SuccessStory => 'success-stories.show',
            self::Guide => 'consultancy.guides.show',
            self::Faq, self::PageSection => null,
        };
    }

    /**
     * Fixed slugs for page sections that replace built-in page text.
     *
     * @return array<string, string>
     */
    public static function pageSections(): array
    {
        return [
            'about-intro' => 'About page introduction',
            'terms' => 'Terms & Conditions body',
            'support-intro' => 'Support page introduction',
            'mission' => 'Mission statement (About page)',
            'vision' => 'Vision statement (About page)',
            'why-different' => 'কেন আমরা ব্যতিক্রম? / What makes us different (About page)',
        ];
    }
}
