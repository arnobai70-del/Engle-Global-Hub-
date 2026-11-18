<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

/**
 * Tidies menus seeded by earlier releases (the live site was first seeded with the
 * original menu, and later releases added items next to it):
 * - each visa link only under "Visa Services", travel links only under "Travel";
 * - "About" keeps About Us, Why Us and Contact (business links stay in the footer);
 * - footer: "Study in Singapore" column becomes "Study Abroad" with Malaysia first and
 *   "Student Consultancy" pointing to Malaysia; the duplicate "Hotels & tours" and the
 *   old "Quick Links" column (all repeated in other columns) are hidden;
 * - the header button goes to Study in Malaysia.
 *
 * Only items that still exactly match a seeded default (label, link and fragment) are
 * changed, and they are hidden, never deleted. An item an admin edited is left as it is
 * and listed in the console so the change can be made in Admin › Menus. Safe to re-run.
 */
class MenuTidySeeder extends Seeder
{
    /** @var list<string> */
    private array $manual = [];

    public function run(): void
    {
        if (! MenuItem::query()->exists()) {
            return;
        }

        $this->keepVisasUnderVisaServices();
        $this->trimAboutMenu();
        $this->tidyFooter();
        $this->pointHeaderButtonsToMalaysia();
        $this->putMalaysiaTileFirst();

        foreach ($this->manual as $line) {
            $this->command?->warn('Menu not changed (edited in admin), change it in Admin › Menus if wanted: '.$line);
        }
    }

    private function keepVisasUnderVisaServices(): void
    {
        $visa = $this->item('header', 'visa', null);
        $travel = $this->item('header', 'travel', null);
        if ($visa === null || $travel === null || ! $visa->is_active || $visa->parent_id !== null) {
            return;
        }

        foreach ([
            'travel-tourist-visa' => ['Tourist Visa', 'route:services.show|tourist-visa'],
            'travel-business-visa' => ['Business Visa', 'route:services.show|business-visa'],
            'travel-work-visa' => ['Work Visa', 'route:work-visa.index'],
        ] as $key => [$label, $target]) {
            if ($this->hasActiveChild($visa, $target)) {
                $this->hideIfDefault('header', $key, $label, $target, null, 'remove "'.$label.'" from "'.$travel->label_en.'" (it is under "'.$visa->label_en.'")');
            }
        }

        // Foreigner support is travel help for visitors, so it stays under Travel.
        if ($this->hasActiveChild($travel, 'route:services.show|foreigner-support')) {
            $this->hideIfDefault('header', 'visa-foreigner', 'Foreigner Support', 'route:services.show|foreigner-support', null, 'remove "Foreigner Support" from "'.$visa->label_en.'" (it is under "'.$travel->label_en.'")');
        }
    }

    private function trimAboutMenu(): void
    {
        foreach ([
            'about-export' => ['Export & Import', 'route:services.show|export-import'],
            'about-civil' => ['Civil & Construction Work', 'route:services.show|civil-construction'],
            'about-b2b' => ['Egabe B2B', 'route:b2b.register'],
            'about-company' => ['Company Profile', 'route:company.index'],
            'about-app' => ['Mobile App', 'route:mobile-app'],
            'about-terms' => ['Terms & Conditions', 'route:terms'],
        ] as $key => [$label, $target]) {
            $this->hideIfDefault('header', $key, $label, $target, null, 'remove "'.$label.'" from the About menu (footer has it)');
        }
    }

    private function tidyFooter(): void
    {
        $study = $this->item('footer', 'footer-study', null);
        if ($study !== null && in_array($study->label_en, ['Study in Singapore', 'Study'], true)) {
            $study->forceFill(['label_en' => 'Study Abroad', 'label_bn' => 'বিদেশে পড়াশোনা'])->save();
        } elseif ($study !== null && $study->label_en !== 'Study Abroad') {
            $this->manual[] = 'footer column "'.$study->label_en.'": rename to "Study Abroad"';
        }

        $consultancy = $this->item('footer', 'footer-consultancy', null);
        if ($consultancy !== null && $consultancy->label_en === 'Student Consultancy' && $consultancy->target === 'route:consultancy.singapore') {
            $consultancy->forceFill(['target' => 'route:consultancy.malaysia'])->save();
        } elseif ($consultancy !== null && $consultancy->target !== 'route:consultancy.malaysia') {
            $this->manual[] = 'footer "'.$consultancy->label_en.'": point it to Study in Malaysia';
        }

        $malaysia = $this->item('footer', 'footer-malaysia', null);
        if ($study !== null && $malaysia !== null && (int) $malaysia->parent_id === (int) $study->id && $malaysia->label_en === 'Study in Malaysia' && $malaysia->target === 'route:consultancy.malaysia') {
            $first = (int) MenuItem::query()->where('parent_id', $study->id)->whereKeyNot($malaysia->id)->min('sort_order');
            if ((int) $malaysia->sort_order >= $first) {
                $malaysia->forceFill(['sort_order' => max(0, $first - 1)])->save();
            }
        }

        // "Hotels & tours" repeats Hotels and Tours when the footer has those links.
        if (MenuItem::query()->where('location', 'footer')->whereIn('key', ['footer-hotels', 'footer-tours'])->where('is_active', true)->exists()) {
            $this->hideIfDefault('footer', 'footer-hotels-tours', 'Hotels & tours', 'route:travel-services.index', null, 'remove "Hotels & tours" (Hotels and Tours are listed)');
        }

        // The first release's "Quick Links" column only repeats links of the Company and More columns.
        $quick = $this->item('footer', 'quick-links', null);
        if ($quick !== null && $quick->is_active && $quick->label_en === 'Quick Links' && MenuItem::query()->where('location', 'footer')->where('key', 'footer-company')->where('is_active', true)->exists()) {
            $seededChildren = ['quick-home', 'quick-about', 'quick-contact', 'quick-terms', 'quick-map', 'quick-app', 'quick-search'];
            $onlySeeded = $quick->children()->where('is_active', true)->get()->every(fn (MenuItem $child): bool => in_array($child->key, $seededChildren, true));
            $onlySeeded ? $quick->forceFill(['is_active' => false])->save() : $this->manual[] = 'footer column "Quick Links": its links repeat the Company column';
        }
    }

    private function pointHeaderButtonsToMalaysia(): void
    {
        foreach ([
            'cta-apply' => ['Apply Now', 'route:consultancy.singapore', 'consultation'],
            'cta-consultation' => ['Free Consultation', 'route:consultancy.singapore', 'apply'],
        ] as $key => [$label, $target, $fragment]) {
            $button = $this->item('header_cta', $key, null);
            if ($button === null || ($button->target === 'route:consultancy.malaysia' && $button->fragment === 'apply')) {
                continue;
            }
            if ($button->label_en === $label && $button->target === $target && $button->fragment === $fragment) {
                $button->forceFill(['target' => 'route:consultancy.malaysia', 'fragment' => 'apply'])->save();
            } elseif ($button->target === 'route:consultancy.singapore') {
                $this->manual[] = 'header button "'.$button->label_en.'": point it to Study in Malaysia #apply';
            }
        }
    }

    /**
     * The first release's "Study in Singapore" tile (tile-study) sorts before "Study in Malaysia".
     */
    private function putMalaysiaTileFirst(): void
    {
        $singapore = $this->item('home_tiles', 'tile-study', null);
        $malaysia = $this->item('home_tiles', 'tile-malaysia', null);
        if ($singapore === null || $malaysia === null || ! $malaysia->is_active || (int) $singapore->sort_order > (int) $malaysia->sort_order) {
            return;
        }
        if ($singapore->label_en === 'Study in Singapore' && $singapore->target === 'route:consultancy.singapore') {
            $singapore->forceFill(['sort_order' => (int) $malaysia->sort_order + 5])->save();
        } else {
            $this->manual[] = 'homepage tile "'.$singapore->label_en.'": move it after "Study in Malaysia"';
        }
    }

    private function hideIfDefault(string $location, string $key, string $label, string $target, ?string $fragment, string $instruction): void
    {
        $item = $this->item($location, $key, null);
        if ($item === null || ! $item->is_active) {
            return;
        }
        if ($item->label_en === $label && $item->target === $target && $item->fragment === $fragment) {
            $item->forceFill(['is_active' => false])->save();

            return;
        }
        $this->manual[] = $instruction;
    }

    private function hasActiveChild(MenuItem $parent, string $target): bool
    {
        return $parent->children()->where('is_active', true)->where('target', $target)->exists();
    }

    private function item(string $location, string $key, mixed $default): ?MenuItem
    {
        return MenuItem::query()->where('location', $location)->where('key', $key)->first() ?? $default;
    }
}
