@extends('layouts.admin')
@section('title','Homepage Content')
@section('page_title','Homepage Content')
@push('head')
<style>
.home-admin{--home-ink:#172c49;--home-muted:#64748b;--home-border:#e2e9f3;--home-primary:#2459db;display:grid;gap:20px;color:var(--home-ink)}
.home-workspace-header{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.home-workspace-header h1{font-size:25px;letter-spacing:-.7px;margin:0}.home-workspace-header p{margin:5px 0 0;color:var(--home-muted)}
.home-visit{display:inline-flex;padding:10px 15px;border:1px solid var(--home-border);border-radius:11px;color:#1d4bbd;text-decoration:none;font-weight:800;background:#fff}
.home-shell{display:grid;grid-template-columns:214px minmax(0,1fr);align-items:start;gap:20px}
.home-aside{position:sticky;top:20px;background:#fff;border:1px solid var(--home-border);border-radius:15px;padding:13px}
.home-aside-title{font-size:11px;text-transform:uppercase;font-weight:900;letter-spacing:.1em;color:#8897ad;padding:9px 12px}
.home-aside a{display:block;padding:11px 12px;border-radius:9px;text-decoration:none;color:#425473;font-size:13px;font-weight:750}
.home-aside a:hover,.home-aside a:focus-visible{background:#eff5ff;color:#1d4bbd}
.home-main{min-width:0;display:grid;gap:20px}
.home-card{border:1px solid var(--home-border);border-radius:16px;background:#fff;padding:23px;box-shadow:0 5px 26px rgba(20,50,100,.035)}
.home-card h2{margin:0 0 6px;font-size:18px;letter-spacing:-.3px}.home-card>p{margin:0 0 16px;color:var(--home-muted);font-size:13px}
.home-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px;margin-top:19px}
.home-field{display:grid;gap:7px;min-width:0}.home-field.wide{grid-column:1/-1}
.home-field label{font-size:12px;font-weight:800;color:#344866}
.home-field input,.home-field textarea,.home-field select{width:100%;min-height:43px;border:1px solid #dbe3ee;border-radius:10px;padding:10px 12px;color:#162b4a;background:#fff;font:inherit;font-size:13px}
.home-field input:focus,.home-field textarea:focus,.home-field select:focus{border-color:#4779f4;outline:3px solid #e3edff}
.home-field textarea{min-height:96px;resize:vertical}
.home-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:19px;padding-top:15px;border-top:1px solid #eef2f7}
.home-btn{border:0;border-radius:10px;padding:11px 18px;min-height:42px;background:var(--home-primary);color:#fff;font-weight:800;cursor:pointer}
.home-btn:hover{background:#1745b3}.home-btn.danger{background:#b42318}
.home-status{padding:12px;border-radius:10px;background:#ecfdf3;color:#067647}.home-errors{padding:12px;border-radius:10px;background:#fef3f2;color:#b42318}
.home-block{margin-top:10px;border:1px solid #e4eaf3;border-radius:11px;overflow:hidden}
.home-block summary{padding:13px 15px;background:#f8fafc;cursor:pointer;font-size:13px;font-weight:800}
.home-block[open] summary{background:#edf4ff;color:#1b4cb6}.home-block>div{padding:17px}
.home-section{margin-top:17px;padding-top:16px;border-top:1px solid #edf1f6}
.home-section h3{margin:0 0 12px;font-size:15px}
.home-check{display:flex!important;gap:8px;align-items:center}.home-check input{width:auto!important;min-height:0}
.home-field-note{font-size:12px;color:#64748b}
.home-subsection{grid-column:1/-1;margin-top:4px;padding-top:14px;border-top:1px solid #edf1f6}
.home-subsection h3{margin:0 0 4px;font-size:15px}.home-subsection p{margin:0;color:#64748b;font-size:12px}
.home-tabbar{display:flex;flex-wrap:wrap;gap:8px;padding:10px;border:1px solid #e5ebf5;border-radius:12px;background:#f7f9fd}
.home-tab{border:1px solid transparent;border-radius:9px;padding:10px 13px;background:transparent;color:#4a5e7b;font-weight:800;cursor:pointer;font-size:12px}
.home-tab[aria-selected=true]{background:#fff;border-color:#d8e3f6;color:#1c4fc0;box-shadow:0 1px 3px #dae4f5}
.home-toolbar{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:14px;border-radius:12px;background:#f7f9fd;border:1px solid #e8edf5}
.home-toolbar label{font-size:12px;font-weight:800;display:grid;gap:7px}
.home-toolbar input,.home-toolbar select{min-width:0;width:100%;padding:10px;border:1px solid #d6e1ef;border-radius:9px;background:#fff;font:inherit;font-size:13px}
.home-empty{padding:18px;border:1px dashed #cbd5e1;border-radius:10px;color:#64748b}
.home-block-count{font-weight:700;color:#64748b;font-size:12px}
.home-help{font-size:12px;color:#64748b;margin:10px 0 0}
.home-pill{font-size:11px;font-weight:800;padding:4px 8px;border-radius:7px;background:#e7f7ee;color:#12723f}
.home-pill.is-hidden{background:#f2f4f7;color:#667085}
.home-block summary{display:flex;align-items:center;justify-content:space-between;gap:12px}
.home-admin [hidden]{display:none!important}
@media(max-width:1100px){.home-shell{grid-template-columns:1fr}.home-aside{position:static;display:flex;flex-wrap:wrap;gap:3px}.home-aside-title{width:100%}}
@media(max-width:700px){.home-card{padding:15px}.home-grid,.home-toolbar{grid-template-columns:1fr}.home-field.wide,.home-subsection{grid-column:auto}.home-workspace-header h1{font-size:21px}.home-tabbar{gap:3px}.home-tab{padding:9px}}
</style>
@endpush
@section('content')
<div class="home-admin">
<header class="home-workspace-header"><div><h1>Homepage Workspace</h1><p>Manage website content in organized sections. Edit real data, not mockups.</p></div><a class="home-visit" href="{{ route('home') }}" target="_blank" rel="noopener">View live homepage ↗</a></header>
@if(session('status'))<div class="home-status" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="home-errors" role="alert"><strong>Please fix these fields.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="home-shell"><aside class="home-aside" aria-label="Homepage editor navigation">
<div class="home-aside-title">Workspace</div>
<a href="#homepage-settings">Site & homepage settings</a>
<a href="#homepage-blocks">Content blocks</a>
<a href="#homepage-newsletter">Newsletter subscribers</a>
<div class="home-aside-title">Quick tips</div>
<p class="home-help" style="padding:0 12px">Choose a settings tab or a content category. Save each block separately.</p>
</aside><div class="home-main">
<section class="home-card" id="homepage-settings"><h2>Site & Homepage Settings</h2><p>Choose a category below. All fields retain their existing saved values.</p>
<div class="home-tabbar" role="tablist" aria-label="Settings categories">
<button type="button" class="home-tab" data-home-tab="general" aria-selected="true">General & Contact</button>
<button type="button" class="home-tab" data-home-tab="ticker" aria-selected="false">News Ticker</button>
<button type="button" class="home-tab" data-home-tab="visibility" aria-selected="false">Visibility & Motion</button>
<button type="button" class="home-tab" data-home-tab="content" aria-selected="false">Hero & Sections</button>
<button type="button" class="home-tab" data-home-tab="seo" aria-selected="false">SEO & Social Sharing</button>
</div>
<form method="POST" action="{{ route('admin.homepage.settings.update') }}" enctype="multipart/form-data">@csrf @method('PATCH')
<div class="home-grid">
<div class="home-field"><label>Site name</label><input name="site_name" required value="{{ old('site_name',$general['site_name']??'Eagle Global Hub LTD') }}"></div>
<div class="home-field"><label>Tagline</label><input name="site_tagline" value="{{ old('site_tagline',$general['site_tagline']??'') }}"></div>
<div class="home-field"><label>Logo</label><input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/avif"></div>
<div class="home-field"><label>Phone</label><input name="phone" value="{{ old('phone',$contact['phone']??'') }}"></div>
<div class="home-field"><label>Email</label><input type="email" name="email" value="{{ old('email',$contact['email']??'') }}"></div>
<div class="home-field"><label>Address</label><input name="address" value="{{ old('address',$contact['address']??'') }}"></div>
<div class="home-field"><label>Support hours</label><input name="hours" value="{{ old('hours',$contact['hours']??'') }}"></div>
<div class="home-field wide"><label>Footer description</label><textarea name="footer_description">{{ old('footer_description',$general['footer_description']??'') }}</textarea></div>
@foreach(['facebook'=>'Facebook','instagram'=>'Instagram','linkedin'=>'LinkedIn','youtube'=>'YouTube'] as $key=>$label)<div class="home-field"><label>{{ $label }} URL</label><input type="url" name="{{ $key }}_url" value="{{ old($key.'_url',$social[$key.'_url']??'') }}"></div>@endforeach
<div class="home-subsection"><h3>Homepage News Ticker</h3><p>Controls the scrolling news / announcement bar directly below the main navigation.</p></div>
<div class="home-field wide"><input type="hidden" name="news_enabled" value="0"><label class="home-check"><input type="checkbox" name="news_enabled" value="1" @checked(!in_array(strtolower((string) old('news_enabled',$homepage['news_enabled']??'1')),['0','false','off','no',''],true))> Show news ticker on homepage</label></div>
<div class="home-field"><label>News label</label><input name="news_label" maxlength="80" value="{{ old('news_label',$homepage['news_label']??'Latest News') }}"></div>
<div class="home-field"><label>News link label</label><input name="news_link_label" maxlength="80" value="{{ old('news_link_label',$homepage['news_link_label']??'Learn More') }}"></div>
<div class="home-field"><label for="news-ticker-duration">Ticker loop duration (seconds)</label><input id="news-ticker-duration" type="number" name="news_ticker_duration" min="8" max="120" step="1" required value="{{ old('news_ticker_duration',$homepage['news_ticker_duration']??'20') }}"><span class="home-field-note">8 = fastest, 120 = slowest. Applies on desktop and mobile.</span></div>
<div class="home-field wide"><label>News text</label><textarea name="news_text" maxlength="500">{{ old('news_text',$homepage['news_text']??'Welcome to Eagle Global Hub LTD — Travel & Visa Services.') }}</textarea><span class="home-field-note">This text scrolls across the homepage ticker.</span></div>
<div class="home-field wide"><label>Optional news link URL</label><input type="url" name="news_url" value="{{ old('news_url',$homepage['news_url']??'') }}" placeholder="https://..."><span class="home-field-note">Leave blank when the announcement does not need a destination link.</span></div>

<div class="home-subsection"><h3>Homepage section visibility & motion</h3><p>Show or hide entire sections without deleting their content. All sections are visible by default.</p></div>
<div class="home-field"><label for="destination-autoplay-interval">Destination slider interval (milliseconds)</label><input id="destination-autoplay-interval" type="number" min="1000" max="15000" step="100" name="destination_autoplay_interval" value="{{ old('destination_autoplay_interval',$homepage['destination_autoplay_interval']??'2200') }}"><span class="home-field-note">1000 = faster, 15000 = slower; default 2200.</span></div>
@foreach (['hero'=>'Hero banner','flight_search'=>'Flight search','assurances'=>'Assurance strip','promotions'=>'Promotion banners','services'=>'Service cards','destinations'=>'Destination slider','service_panels'=>'Service panels','benefits'=>'Benefits','testimonials'=>'Testimonials','app'=>'Mobile app','newsletter'=>'Newsletter'] as $sectionKey=>$sectionLabel)
<div class="home-field"><input type="hidden" name="show_{{ $sectionKey }}" value="0"><label class="home-check"><input type="checkbox" name="show_{{ $sectionKey }}" value="1" @checked(!in_array(strtolower((string)old('show_'.$sectionKey,$homepage['show_'.$sectionKey]??'1')),['0','false','off','no'],true))> Show {{ $sectionLabel }}</label></div>
@endforeach
<div class="home-subsection"><h3>Hero & Homepage Sections</h3></div>
<div class="home-field"><label>Hero eyebrow</label><input name="hero_eyebrow" value="{{ old('hero_eyebrow',$homepage['hero_eyebrow']??'') }}"></div>
<div class="home-field"><label>Hero image</label><input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp,image/avif"></div>
<div class="home-field"><label>Hero title</label><input name="hero_title" required value="{{ old('hero_title',$homepage['hero_title']??'Travel the World with') }}"></div>
<div class="home-field"><label>Hero accent</label><input name="hero_accent" value="{{ old('hero_accent',$homepage['hero_accent']??'Eagle Global Hub') }}"></div>
<div class="home-field wide"><label>Hero subtitle</label><textarea name="hero_subtitle">{{ old('hero_subtitle',$homepage['hero_subtitle']??'') }}</textarea></div>
<div class="home-field wide"><label>Hero side text</label><textarea name="hero_side_text">{{ old('hero_side_text',$homepage['hero_side_text']??'') }}</textarea></div>
@foreach(['services'=>'Services','destinations'=>'Destinations','benefits'=>'Why Choose','testimonials'=>'Testimonials'] as $key=>$label)<div class="home-field"><label>{{ $label }} title</label><input name="{{ $key }}_title" value="{{ old($key.'_title',$homepage[$key.'_title']??'') }}"></div><div class="home-field"><label>{{ $label }} subtitle</label><input name="{{ $key }}_subtitle" value="{{ old($key.'_subtitle',$homepage[$key.'_subtitle']??'') }}"></div>@endforeach
<div class="home-field"><label>App title</label><input name="app_title" value="{{ old('app_title',$appSettings['app_title']??'') }}"></div><div class="home-field"><label>App subtitle</label><input name="app_subtitle" value="{{ old('app_subtitle',$appSettings['app_subtitle']??'') }}"></div>
<div class="home-field"><label>Google Play URL</label><input type="url" name="google_play_url" value="{{ old('google_play_url',$appSettings['google_play_url']??'') }}"></div><div class="home-field"><label>App Store URL</label><input type="url" name="app_store_url" value="{{ old('app_store_url',$appSettings['app_store_url']??'') }}"></div>
<div class="home-field"><label>Newsletter title</label><input name="newsletter_title" value="{{ old('newsletter_title',$homepage['newsletter_title']??'') }}"></div><div class="home-field"><label>Newsletter subtitle</label><input name="newsletter_subtitle" value="{{ old('newsletter_subtitle',$homepage['newsletter_subtitle']??'') }}"></div>
<div class="home-field"><label>Journey title</label><input name="journey_title" value="{{ old('journey_title',$homepage['journey_title']??'') }}"></div><div class="home-field"><label>Journey background</label><input type="file" name="journey_background" accept="image/jpeg,image/png,image/webp,image/avif"></div><div class="home-field wide"><label>Journey copy</label><textarea name="journey_copy">{{ old('journey_copy',$homepage['journey_copy']??'') }}</textarea></div>
<div class="home-field wide"><label>Meta title</label><input name="seo_title" maxlength="70" required value="{{ old('seo_title',$seo['home_meta_title']??'') }}"></div><div class="home-field wide"><label>Meta description</label><textarea name="seo_description" maxlength="180" required>{{ old('seo_description',$seo['home_meta_description']??'') }}</textarea></div>
<div class="home-field"><label>Canonical URL</label><input type="url" name="canonical_url" value="{{ old('canonical_url',$seo['home_canonical_url']??'') }}"></div><div class="home-field"><label>Robots meta</label><input name="robots" value="{{ old('robots',$seo['home_robots']??'index,follow') }}"></div>
<div class="home-field"><label>Open Graph title</label><input name="og_title" value="{{ old('og_title',$seo['home_og_title']??'') }}"></div><div class="home-field"><label>Open Graph image</label><input type="file" name="og_image" accept="image/jpeg,image/png,image/webp,image/avif"></div><div class="home-field wide"><label>Open Graph description</label><textarea name="og_description">{{ old('og_description',$seo['home_og_description']??'') }}</textarea></div><div class="home-field"><label>Twitter card</label><select name="twitter_card"><option value="summary_large_image">Large image</option><option value="summary">Summary</option></select></div>
</div><div class="home-actions"><button class="home-btn">Save Settings</button></div></form></section>

<section class="home-card" id="homepage-blocks"><h2>Content Block Manager</h2><p>Browse and edit a category without scrolling through every block.</p>
<div class="home-toolbar">
<label for="home-block-filter">Content category<select id="home-block-filter"><option value="all">All categories</option>@foreach($sections as $sectionKey=>$sectionLabel)<option value="{{ $sectionKey }}" @selected($loop->first)>{{ $sectionLabel }} ({{ ($blocks[$sectionKey]??collect())->count() }})</option>@endforeach</select></label>
<label for="home-block-search">Search block title<input type="search" id="home-block-search" placeholder="e.g. Dubai, Flights, Visa..." autocomplete="off"></label>
</div><p class="home-help" id="home-filter-count" aria-live="polite"></p>
<details class="home-block"><summary>+ Add new content block</summary><div><form method="POST" action="{{ route('admin.homepage.blocks.store') }}" enctype="multipart/form-data">@csrf @include('admin.homepage.partials.block-form',['block'=>null])<div class="home-actions"><button class="home-btn">Add Block</button></div></form></div></details>
@foreach($sections as $sectionKey=>$sectionLabel)<div class="home-section" data-block-section="{{ $sectionKey }}"><h3>{{ $sectionLabel }} <span class="home-block-count">({{ ($blocks[$sectionKey]??collect())->count() }})</span></h3>@forelse($blocks[$sectionKey]??collect() as $block)<details class="home-block" data-block-title="{{ Str::lower($block->title ?: $block->key) }}"><summary><span>{{ $block->title ?: $block->key }}</span><span class="home-pill {{ $block->is_active ? '' : 'is-hidden' }}">{{ $block->is_active?'Published':'Hidden' }}</span></summary><div><form method="POST" action="{{ route('admin.homepage.blocks.update',$block) }}" enctype="multipart/form-data">@csrf @method('PATCH') @include('admin.homepage.partials.block-form',['block'=>$block])<div class="home-actions"><button class="home-btn">Save</button></div></form><form method="POST" action="{{ route('admin.homepage.blocks.destroy',$block) }}" onsubmit="return confirm('Delete this block?')">@csrf @method('DELETE')<div class="home-actions"><button class="home-btn danger">Delete</button></div></form></div></details>@empty<p>No records yet.</p>@endforelse</div>@endforeach
</section>
<section class="home-card" id="homepage-newsletter"><h2>Newsletter Subscribers</h2><p>{{ number_format($newsletterCount) }} subscriber(s).</p>@foreach($recentSubscribers as $subscriber)<span style="display:inline-block;margin:5px;padding:6px 10px;background:#f1f5f9;border-radius:999px">{{ $subscriber->email }}</span>@endforeach</section>
</div></div></div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const grid = document.querySelector('#homepage-settings .home-grid');
    if (grid) {
        // Associate existing fields with their corresponding settings tab without
        // changing field names, form actions or saved data.
        let category = 'general';
        grid.querySelectorAll(':scope > .home-field, :scope > .home-subsection').forEach(function (field) {
            if (field.classList.contains('home-subsection')) {
                const heading = field.querySelector('h3')?.textContent || '';
                if (heading.includes('News Ticker')) category = 'ticker';
                else if (heading.includes('visibility & motion')) category = 'visibility';
                else if (heading.includes('Hero & Homepage')) category = 'content';
            } else if (field.querySelector('[name="seo_title"]')) {
                category = 'seo';
            }
            field.dataset.settingsTab = category;
        });
        const tabs = Array.from(document.querySelectorAll('[data-home-tab]'));
        function selectTab(name) {
            tabs.forEach(function (button) {
                const active = button.dataset.homeTab === name;
                button.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            grid.querySelectorAll('[data-settings-tab]').forEach(function (field) {
                const active = field.dataset.settingsTab === name;
                field.hidden = !active;
                // Hidden HTML required inputs cannot be focused by browser validation.
                field.querySelectorAll('[required]').forEach(function (input) {
                    if (!input.dataset.initiallyRequired) input.dataset.initiallyRequired = '1';
                    input.required = active;
                });
            });
        }
        tabs.forEach(function (button) {
            button.addEventListener('click', function () { selectTab(button.dataset.homeTab); });
        });
        selectTab('general');
        @if($errors->any())
        const firstError = document.querySelector('#homepage-settings [name="{{ $errors->keys()[0] ?? '' }}"]');
        if (firstError && firstError.closest('[data-settings-tab]')) selectTab(firstError.closest('[data-settings-tab]').dataset.settingsTab);
        @endif
    }
    const filter = document.getElementById('home-block-filter');
    const search = document.getElementById('home-block-search');
    const counter = document.getElementById('home-filter-count');
    function refreshBlocks() {
        if (!filter || !search) return;
        const term = search.value.trim().toLocaleLowerCase();
        let total = 0;
        document.querySelectorAll('[data-block-section]').forEach(function (section) {
            const categoryMatches = filter.value === 'all' || section.dataset.blockSection === filter.value;
            let shown = 0;
            section.querySelectorAll('[data-block-title]').forEach(function (block) {
                const matches = categoryMatches && block.dataset.blockTitle.includes(term);
                block.hidden = !matches;
                if (matches) shown++;
            });
            section.hidden = !categoryMatches || (term !== '' && shown === 0);
            total += shown;
        });
        if (counter) counter.textContent = total + ' matching content block' + (total === 1 ? '' : 's');
    }
    filter?.addEventListener('change', refreshBlocks);
    search?.addEventListener('input', refreshBlocks);
    refreshBlocks();
});
</script>
@endpush