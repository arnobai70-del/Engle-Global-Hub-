@extends('layouts.admin')
@section('title','Homepage Content')
@section('page_title','Homepage Content')
@push('head')
<style>
.home-admin{display:grid;gap:22px}.home-card{border:1px solid #e2e8f0;border-radius:16px;background:#fff;padding:20px}.home-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:16px}.home-field{display:grid;gap:6px}.home-field.wide{grid-column:1/-1}.home-field label{font-size:12px;font-weight:800;color:#344866}.home-field input,.home-field textarea,.home-field select{width:100%;border:1px solid #dbe3ee;border-radius:10px;padding:10px 12px}.home-field textarea{min-height:90px}.home-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:16px}.home-btn{border:0;border-radius:10px;padding:10px 16px;background:#155eef;color:#fff;font-weight:800;cursor:pointer}.home-btn.danger{background:#b42318}.home-status{padding:12px;border-radius:10px;background:#ecfdf3;color:#067647}.home-errors{padding:12px;border-radius:10px;background:#fef3f2;color:#b42318}.home-block{margin-top:12px;border:1px solid #e4eaf3;border-radius:12px;overflow:hidden}.home-block summary{padding:14px;background:#f8fafc;cursor:pointer;font-weight:800}.home-block>div{padding:16px}.home-section{margin-top:22px;padding-top:18px;border-top:1px solid #edf1f6}.home-check{display:flex;gap:8px;align-items:center}.home-check input{width:auto}@media(max-width:700px){.home-grid{grid-template-columns:1fr}.home-field.wide{grid-column:auto}}
</style>
@endpush
@section('content')
<div class="home-admin">
@if(session('status'))<div class="home-status" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="home-errors" role="alert"><strong>Please fix these fields.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<section class="home-card"><h2>Site, Homepage & SEO</h2><p>Update public brand, contact, homepage copy, images, app links and homepage metadata.</p>
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

<section class="home-card"><h2>Repeatable Homepage Blocks</h2><p>Manage promotions, services, destinations, panels, benefits, testimonials and footer links.</p>
<details class="home-block" open><summary>Add block</summary><div><form method="POST" action="{{ route('admin.homepage.blocks.store') }}" enctype="multipart/form-data">@csrf @include('admin.homepage.partials.block-form',['block'=>null])<div class="home-actions"><button class="home-btn">Add Block</button></div></form></div></details>
@foreach($sections as $sectionKey=>$sectionLabel)<div class="home-section"><h3>{{ $sectionLabel }}</h3>@forelse($blocks[$sectionKey]??collect() as $block)<details class="home-block"><summary>{{ $block->title ?: $block->key }} · {{ $block->is_active?'Published':'Hidden' }}</summary><div><form method="POST" action="{{ route('admin.homepage.blocks.update',$block) }}" enctype="multipart/form-data">@csrf @method('PATCH') @include('admin.homepage.partials.block-form',['block'=>$block])<div class="home-actions"><button class="home-btn">Save</button></div></form><form method="POST" action="{{ route('admin.homepage.blocks.destroy',$block) }}" onsubmit="return confirm('Delete this block?')">@csrf @method('DELETE')<div class="home-actions"><button class="home-btn danger">Delete</button></div></form></div></details>@empty<p>No records yet.</p>@endforelse</div>@endforeach
</section>
<section class="home-card"><h2>Newsletter Subscribers</h2><p>{{ number_format($newsletterCount) }} subscriber(s).</p>@foreach($recentSubscribers as $subscriber)<span style="display:inline-block;margin:5px;padding:6px 10px;background:#f1f5f9;border-radius:999px">{{ $subscriber->email }}</span>@endforeach</section>
</div>
@endsection
