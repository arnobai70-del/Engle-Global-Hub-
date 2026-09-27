<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateHomepageSettingsRequest;
use App\Http\Requests\Admin\UpsertHomepageBlockRequest;
use App\Models\HomepageBlock;
use App\Models\NewsletterSubscriber;
use App\Services\SettingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomepageContentController extends Controller
{
    private const SECTIONS = [
        'assurance' => 'Trust / assurance strip',
        'promotion' => 'Promotion banners',
        'service' => 'Our Services cards',
        'destination' => 'Popular destinations',
        'service_panel' => 'Visa / Work Visa / Holiday panels',
        'benefit' => 'Why choose us',
        'testimonial' => 'Testimonials',
        'app_feature' => 'Mobile app feature chips',
        'footer_link' => 'Additional footer links',
    ];

    public function __construct(private readonly SettingService $settings) {}

    public function index(): View
    {
        return view('admin.homepage.index', [
            'sections' => self::SECTIONS,
            'blocks' => HomepageBlock::query()->orderBy('section')->orderBy('sort_order')->orderBy('id')->get()->groupBy('section'),
            'general' => $this->settings->group('general'),
            'contact' => $this->settings->group('contact'),
            'social' => $this->settings->group('social'),
            'homepage' => $this->settings->group('homepage'),
            'appSettings' => $this->settings->group('app'),
            'seo' => $this->settings->group('seo'),
            'newsletterCount' => NewsletterSubscriber::query()->count(),
            'recentSubscribers' => NewsletterSubscriber::query()->latest('subscribed_at')->limit(10)->get(),
        ]);
    }

    public function updateSettings(UpdateHomepageSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->saveGroup('general', [
            'site_name' => $data['site_name'],
            'site_tagline' => $data['site_tagline'] ?? null,
            'footer_description' => $data['footer_description'] ?? null,
        ]);
        $this->saveGroup('contact', [
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'hours' => $data['hours'] ?? null,
        ]);
        $this->saveGroup('social', [
            'facebook_url' => $data['facebook_url'] ?? null,
            'instagram_url' => $data['instagram_url'] ?? null,
            'linkedin_url' => $data['linkedin_url'] ?? null,
            'youtube_url' => $data['youtube_url'] ?? null,
        ]);
        $this->saveGroup('homepage', [
            'hero_eyebrow' => $data['hero_eyebrow'] ?? null,
            'hero_title' => $data['hero_title'],
            'hero_accent' => $data['hero_accent'] ?? null,
            'hero_subtitle' => $data['hero_subtitle'] ?? null,
            'hero_side_text' => $data['hero_side_text'] ?? null,
            'services_title' => $data['services_title'] ?? null,
            'services_subtitle' => $data['services_subtitle'] ?? null,
            'destinations_title' => $data['destinations_title'] ?? null,
            'destinations_subtitle' => $data['destinations_subtitle'] ?? null,
            'benefits_title' => $data['benefits_title'] ?? null,
            'benefits_subtitle' => $data['benefits_subtitle'] ?? null,
            'testimonials_title' => $data['testimonials_title'] ?? null,
            'testimonials_subtitle' => $data['testimonials_subtitle'] ?? null,
            'newsletter_title' => $data['newsletter_title'] ?? null,
            'newsletter_subtitle' => $data['newsletter_subtitle'] ?? null,
            'journey_title' => $data['journey_title'] ?? null,
            'journey_copy' => $data['journey_copy'] ?? null,
        ]);
        $this->saveGroup('app', [
            'app_title' => $data['app_title'] ?? null,
            'app_subtitle' => $data['app_subtitle'] ?? null,
            'google_play_url' => $data['google_play_url'] ?? null,
            'app_store_url' => $data['app_store_url'] ?? null,
        ]);
        $this->saveGroup('seo', [
            'home_meta_title' => $data['seo_title'],
            'home_meta_description' => $data['seo_description'],
            'home_canonical_url' => $data['canonical_url'] ?? null,
            'home_og_title' => $data['og_title'] ?? null,
            'home_og_description' => $data['og_description'] ?? null,
            'home_robots' => $data['robots'] ?? 'index,follow',
            'home_twitter_card' => $data['twitter_card'] ?? 'summary_large_image',
        ]);

        $fileSettings = [
            'logo' => ['general', 'logo_path', 'homepage/site'],
            'hero_image' => ['homepage', 'hero_image', 'homepage/hero'],
            'og_image' => ['seo', 'home_og_image', 'homepage/seo'],
            'journey_background' => ['homepage', 'journey_background', 'homepage/journey'],
        ];

        foreach ($fileSettings as $field => [$group, $key, $directory]) {
            if (! $request->hasFile($field)) continue;
            $oldPath = $this->settings->get($group, $key);
            $newPath = $request->file($field)->store($directory, 'public');
            $this->settings->set($group, $key, $newPath, 'string', true);
            $this->deleteManagedFile(is_string($oldPath) ? $oldPath : null);
        }

        return back()->with('status', 'Homepage and site content updated.');
    }

    public function store(UpsertHomepageBlockRequest $request): RedirectResponse
    {
        HomepageBlock::query()->create($this->blockPayload($request, $request->validated()));
        return back()->with('status', 'Homepage block created.');
    }

    public function update(UpsertHomepageBlockRequest $request, HomepageBlock $homepageBlock): RedirectResponse
    {
        $oldImage = $homepageBlock->image_path;
        $payload = $this->blockPayload($request, $request->validated(), $homepageBlock);
        $homepageBlock->update($payload);
        if (($payload['image_path'] ?? $oldImage) !== $oldImage) $this->deleteManagedFile($oldImage);
        return back()->with('status', 'Homepage block updated.');
    }

    public function destroy(HomepageBlock $homepageBlock): RedirectResponse
    {
        $image = $homepageBlock->image_path;
        $homepageBlock->delete();
        $this->deleteManagedFile($image);
        return back()->with('status', 'Homepage block deleted.');
    }

    /** @param array<string, mixed> $values */
    private function saveGroup(string $group, array $values): void
    {
        foreach ($values as $key => $value) $this->settings->set($group, $key, $value, 'string', true);
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function blockPayload(UpsertHomepageBlockRequest $request, array $data, ?HomepageBlock $existing = null): array
    {
        $imagePath = $existing?->image_path;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('homepage/blocks', 'public');
        } elseif (! empty($data['image_url'])) {
            $imagePath = $data['image_url'];
        }

        $key = $data['key'] ?? null;
        if (($key === null || $key === '') && ! empty($data['title'])) {
            $key = Str::slug($data['title']).'-'.Str::lower(Str::random(5));
        }

        return [
            'section' => $data['section'], 'key' => $key,
            'title' => $data['title'] ?? null, 'subtitle' => $data['subtitle'] ?? null,
            'body' => $data['body'] ?? null, 'image_path' => $imagePath,
            'image_alt' => $data['image_alt'] ?? null, 'icon' => $data['icon'] ?? null,
            'url' => $data['url'] ?? null, 'cta_label' => $data['cta_label'] ?? null,
            'meta' => ! empty($data['meta']) ? json_decode($data['meta'], true, 512, JSON_THROW_ON_ERROR) : null,
            'sort_order' => (int) $data['sort_order'], 'is_active' => $request->boolean('is_active'),
        ];
    }

    private function deleteManagedFile(?string $path): void
    {
        if (! $path || Str::startsWith($path, ['http://', 'https://'])) return;
        if (Str::startsWith($path, 'homepage/')) Storage::disk('public')->delete($path);
    }
}
