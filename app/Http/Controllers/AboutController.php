<?php

namespace App\Http\Controllers;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\MediaAsset;
use App\Models\LegalDocument;
use App\Models\TeamMember;
use App\Services\Content\PageContent;
use App\Services\SettingService;
use Illuminate\Database\QueryException;
use Illuminate\View\View;

class AboutController extends Controller
{
    /**
     * The About page. Mission, vision and team sections appear only once
     * staff have written the text or published verified profiles.
     */
    public function __invoke(SettingService $settings, PageContent $content): View
    {
        try {
            // Published page sections (draft/review/publish, bilingual) take
            // precedence over the older single-language settings values.
            $company = [
                'mission' => trim((string) ($content->section('mission')?->localized('body') ?? $settings->get('company', 'mission', ''))),
                'vision' => trim((string) ($content->section('vision')?->localized('body') ?? $settings->get('company', 'vision', ''))),
                'why_choose_us_bn' => trim((string) ($content->section('why-different')?->localized('body') ?? $settings->get('company', 'why_choose_us_bn', ''))),
            ];
            // Only members with a real photo are shown; without any, the team section is hidden.
            $team = TeamMember::query()->published()->whereNotNull('photo_path')->where('photo_path', '!=', '')->get(['id', 'name', 'role_title', 'bio', 'photo_path']);
            $officeGallery = MediaAsset::query()->publishedIn('office_gallery')->get(['id', 'path', 'alt_text', 'caption', 'width', 'height']);
            // /company now redirects here, so published company profiles are listed on this page.
            $companyProfiles = ContentEntry::query()->published(ContentType::CompanyProfile)->get();
            $hasLegalDocuments = LegalDocument::query()->publiclyVisible()->exists();
        } catch (QueryException) {
            $company = ['mission' => '', 'vision' => '', 'why_choose_us_bn' => ''];
            $team = collect();
            $officeGallery = collect();
            $companyProfiles = collect();
            $hasLegalDocuments = false;
        }

        return view('public.about', compact('company', 'team', 'officeGallery', 'companyProfiles', 'hasLegalDocuments'));
    }
}
