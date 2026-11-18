{{-- Partner logo strip: publicly listed institutions with a logo (Admin › Institutions). Hidden when there is none. --}}
@php($partners = rescue(fn () => \App\Models\Institution::query()->publiclyListed()->whereNotNull('logo_media_id')->with('logo:id,path,alt_text,width,height')->limit(18)->get(['id', 'name', 'website_url', 'logo_media_id'])->filter(fn ($partner) => $partner->logo !== null), collect(), false))
@if($partners->isNotEmpty())
<section class="v2-section-tight v2-partners" aria-labelledby="partners-title">
    <div class="v2-shell">
        <h2 id="partners-title" class="v2-partners-title">{{ __('Institutions we work with') }}</h2>
        <ul>
            @foreach($partners as $partner)
                <li><img src="{{ $partner->logo->url() }}" alt="{{ $partner->logo->alt_text ?: $partner->name }}" width="{{ $partner->logo->width ?: 160 }}" height="{{ $partner->logo->height ?: 64 }}" loading="lazy" decoding="async"></li>
            @endforeach
        </ul>
    </div>
</section>
@endif
