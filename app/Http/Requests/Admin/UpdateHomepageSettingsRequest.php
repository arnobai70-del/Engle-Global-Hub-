<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomepageSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:120'],
            'site_tagline' => ['nullable', 'string', 'max:180'],
            'phone' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email:rfc', 'max:180'],
            'address' => ['nullable', 'string', 'max:255'],
            'hours' => ['nullable', 'string', 'max:120'],
            'facebook_url' => ['nullable', 'url:http,https', 'max:500'],
            'instagram_url' => ['nullable', 'url:http,https', 'max:500'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:500'],
            'youtube_url' => ['nullable', 'url:http,https', 'max:500'],
            'footer_description' => ['nullable', 'string', 'max:1000'],
            'news_enabled' => ['nullable', 'boolean'],
            'news_label' => ['nullable', 'string', 'max:80'],
            'news_text' => ['nullable', 'string', 'max:500'],
            'news_url' => ['nullable', 'url:http,https', 'max:500'],
            'news_link_label' => ['nullable', 'string', 'max:80'],
            'hero_eyebrow' => ['nullable', 'string', 'max:160'],
            'hero_title' => ['required', 'string', 'max:180'],
            'hero_accent' => ['nullable', 'string', 'max:120'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],
            'hero_side_text' => ['nullable', 'string', 'max:255'],
            'services_title' => ['nullable', 'string', 'max:160'],
            'services_subtitle' => ['nullable', 'string', 'max:500'],
            'destinations_title' => ['nullable', 'string', 'max:160'],
            'destinations_subtitle' => ['nullable', 'string', 'max:500'],
            'benefits_title' => ['nullable', 'string', 'max:160'],
            'benefits_subtitle' => ['nullable', 'string', 'max:500'],
            'testimonials_title' => ['nullable', 'string', 'max:160'],
            'testimonials_subtitle' => ['nullable', 'string', 'max:500'],
            'app_title' => ['nullable', 'string', 'max:160'],
            'app_subtitle' => ['nullable', 'string', 'max:500'],
            'google_play_url' => ['nullable', 'url:http,https', 'max:500'],
            'app_store_url' => ['nullable', 'url:http,https', 'max:500'],
            'newsletter_title' => ['nullable', 'string', 'max:160'],
            'newsletter_subtitle' => ['nullable', 'string', 'max:500'],
            'journey_title' => ['nullable', 'string', 'max:180'],
            'journey_copy' => ['nullable', 'string', 'max:600'],
            'seo_title' => ['required', 'string', 'max:70'],
            'seo_description' => ['required', 'string', 'max:180'],
            'canonical_url' => ['nullable', 'url:http,https', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:100'],
            'og_description' => ['nullable', 'string', 'max:250'],
            'robots' => ['nullable', 'string', 'max:120'],
            'twitter_card' => ['nullable', 'string', 'in:summary,summary_large_image'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,avif', 'max:4096'],
            'hero_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,avif', 'max:8192'],
            'og_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,avif', 'max:8192'],
            'journey_background' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,avif', 'max:8192'],
        ];
    }
}