{{--
    Testimonials: published Success Stories (Admin › Website Content), each published only with
    the student's recorded consent. Title = name, summary = quote, category = programme,
    location = university, intake, cover image = optional photo.
    @var \Illuminate\Support\Collection<int, \App\Models\ContentEntry> $stories  with "cover" loaded
--}}
@if($stories->isNotEmpty())
<div class="v2-stories">
    @foreach($stories as $story)
        @php($details = collect([$story->category, $story->location, $story->intake])->filter()->implode(' · '))
        <figure class="v2-story">
            <blockquote>“{{ \Illuminate\Support\Str::limit((string) ($story->localized('summary') ?: strip_tags((string) $story->localized('body'))), 220) }}”</blockquote>
            <figcaption>
                @if($story->cover)<img class="v2-story-photo" src="{{ $story->cover->url() }}" alt="{{ $story->cover->alt_text ?: $story->localized('title') }}" width="56" height="56" loading="lazy" decoding="async">@endif
                <span><strong>{{ $story->localized('title') }}</strong>@if($details !== '')<span>{{ $details }}</span>@endif</span>
            </figcaption>
        </figure>
    @endforeach
</div>
@endif
