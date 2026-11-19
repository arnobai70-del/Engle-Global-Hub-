@extends('layouts.admin')
@section('title', 'Study in '.$destination->country?->name)
@section('content')
@php
    $blockers = $destination->publishBlockers();
    $value = fn (string $field) => old($field, $destination->{$field});
    $labels = ['title' => 'Title', 'body' => 'Text', 'amount' => 'Amount (local currency, e.g. 8,000–12,000)'];
    $sectionHelp = [
        'why' => 'Short reasons, in your own words. No rankings or claims you cannot prove.',
        'subject' => 'Subjects and study levels Bangladeshi students often choose.',
        'requirement' => 'Academic, English or other language test requirements. Title = the requirement (for example "English test"), text = what is needed.',
        'document' => 'One document per row. Title only.',
        'visa_step' => 'One step per row, in order.',
        'cost' => 'Tuition and living cost ranges per year or month, in the local currency. Write the period in the title, for example "Tuition per year".',
        'work_rule' => 'What the student visa allows, for example hours per week during term.',
        'faq' => 'Question as the title, answer as the text.',
        'source' => 'Official pages this page is based on. Suggested links are marked "verify before publishing": open them, check they are the right official page, then untick it.',
    ];
@endphp
<x-admin.page-header :title="'Study in '.$destination->country?->name" :description="'/study-in-'.$destination->slug.' · '.($destination->isPublic() ? 'Public' : ($destination->status === 'published' ? 'Published, hidden until complete' : 'Draft'))" icon="S" eyebrow="Study destinations">
    <a class="egh-button secondary" href="{{ route('admin.study-destinations.index') }}">All destinations</a>
</x-admin.page-header>
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="egh-card" data-publish-panel>
    @if($blockers === [])
        <p><strong>Complete.</strong> {{ $destination->status === 'published' ? 'This page is public.' : 'You can publish it.' }}</p>
    @else
        <p><strong>Hidden until these are done:</strong></p>
        <ul class="admin-error-list" data-publish-blockers>@foreach($blockers as $blocker)<li>{{ $blocker }}</li>@endforeach</ul>
    @endif
    @if($destination->factsWithoutSource()->isNotEmpty())
        <p class="egh-alert is-error" role="status" data-missing-sources>{{ $destination->factsWithoutSource()->count() }} fact(s) have no source: {{ $destination->factsWithoutSource()->pluck('title')->implode(', ') }}. Add the official link for each fact.</p>
    @endif
    <form method="POST" action="{{ route('admin.study-destinations.status', $destination) }}">@csrf @method('PATCH')
        @if($destination->status === 'published')
            <button class="egh-button secondary" name="status" value="draft">Unpublish</button>
        @else
            <button class="egh-button" name="status" value="published" @disabled($blockers !== [])>Publish</button>
        @endif
    </form>
</div>

<form method="POST" action="{{ route('admin.study-destinations.update', $destination) }}">
@csrf @method('PATCH')
<div class="egh-card">
    <h2>Page</h2>
    <p>Write plain text. Leave a blank line between paragraphs. Bengali fields are optional; English is shown when a Bengali field is empty.</p>
    <div class="stu-form-grid">
        <label>URL slug (/study-in-…)<input name="slug" value="{{ $value('slug') }}" maxlength="80" required></label>
        <label>Name in sentences (English, optional)<input name="name_in_sentence" value="{{ $value('name_in_sentence') }}" maxlength="120" placeholder="{{ $destination->country?->name }}"><span class="stu-muted">Only for countries that need "the", for example "the United Kingdom" or "the Netherlands". Used in "Why study in …" and other sentences.</span></label>
        <label>Country name in Bengali<input name="name_bn" lang="bn" value="{{ $value('name_bn') }}" maxlength="120"></label>
        <label>Hero title<input name="hero_title" value="{{ $value('hero_title') }}" maxlength="160" placeholder="Study in {{ $destination->country?->name }} from Bangladesh"></label>
        <label>Hero title (Bengali)<input name="hero_title_bn" lang="bn" value="{{ $value('hero_title_bn') }}" maxlength="160"></label>
        <label class="stu-full">Introduction (required to publish)<textarea name="intro" rows="4" maxlength="3000">{{ $value('intro') }}</textarea></label>
        <label class="stu-full">Introduction (Bengali)<textarea name="intro_bn" lang="bn" rows="4" maxlength="3000">{{ $value('intro_bn') }}</textarea></label>
        <label>Hero image (from Media Library)<select name="hero_media_id"><option value="">None</option>@foreach($media as $asset)<option value="{{ $asset->id }}" @selected((string) old('hero_media_id', $destination->hero_media_id) === (string) $asset->id)>{{ $asset->collection }} · {{ $asset->alt_text ?: basename($asset->path) }}</option>@endforeach</select></label>
        <label>Hero image alt text (required with an image)<input name="hero_alt" value="{{ $value('hero_alt') }}" maxlength="200" placeholder="What the photo shows"></label>
        <label>Hero image alt text (Bengali)<input name="hero_alt_bn" lang="bn" value="{{ $value('hero_alt_bn') }}" maxlength="200"></label>
        <label>Intakes<input name="intakes" value="{{ $value('intakes') }}" maxlength="200" placeholder="For example: April · October"></label>
        <label>Intakes (Bengali)<input name="intakes_bn" lang="bn" value="{{ $value('intakes_bn') }}" maxlength="200"></label>
    </div>
</div>

<div class="egh-card">
    <h2>Student visa</h2>
    <div class="stu-form-grid">
        <label>Visa name<input name="visa_name" value="{{ $value('visa_name') }}" maxlength="120"></label>
        <label>Visa name (Bengali)<input name="visa_name_bn" lang="bn" value="{{ $value('visa_name_bn') }}" maxlength="120"></label>
        <label>Deciding authority<input name="visa_authority" value="{{ $value('visa_authority') }}" maxlength="160"></label>
        <label>Deciding authority (Bengali)<input name="visa_authority_bn" lang="bn" value="{{ $value('visa_authority_bn') }}" maxlength="160"></label>
        <label>Processing time<input name="processing_time" value="{{ $value('processing_time') }}" maxlength="200"></label>
        <label>Processing time (Bengali)<input name="processing_time_bn" lang="bn" value="{{ $value('processing_time_bn') }}" maxlength="200"></label>
        <label>Processing time source (https)<input type="url" name="processing_source_url" value="{{ $value('processing_source_url') }}" maxlength="2048"></label>
        <label>Processing time source label<input name="processing_source_label" value="{{ $value('processing_source_label') }}" maxlength="160" placeholder="For example: GOV.UK"></label>
        @if(filled($destination->processing_time) && blank($destination->processing_source_url))<p class="stu-full egh-alert is-error">The processing time has no source.</p>@endif
    </div>
</div>

<div class="egh-card">
    <h2>Costs: currency and Taka rate</h2>
    <div class="stu-form-grid">
        <label>Currency code (3 letters)<input name="currency_code" value="{{ $value('currency_code') }}" maxlength="3" placeholder="For example GBP"></label>
        <label>1 {{ $destination->currency_code ?: 'unit' }} in Taka (BDT)<input name="bdt_rate" value="{{ $value('bdt_rate') }}" inputmode="decimal" placeholder="Leave empty to show no Taka estimate"></label>
        <label>Rate checked on<input type="date" name="bdt_rate_checked_on" value="{{ old('bdt_rate_checked_on', $destination->bdt_rate_checked_on?->format('Y-m-d')) }}" max="{{ now()->toDateString() }}"></label>
    </div>
</div>

@foreach(\App\Models\StudyDestination::SECTIONS as $section => [$heading, $isFact, $fields])
    @php($rows = $destination->section($section)->map(fn ($item) => $item->toArray())->values()->all())
    @php($rows = array_merge(old('items.'.$section) !== null ? array_values(old('items.'.$section)) : $rows, [[], []]))
    <fieldset class="egh-card" data-admin-section="{{ $section }}">
        <legend><h2>{{ $heading }}</h2></legend>
        <p class="stu-muted">{{ $sectionHelp[$section] }} Empty rows are ignored. Use "Add row" for more (up to {{ \App\Http\Requests\Admin\UpdateStudyDestinationRequest::MAX_ROWS }} per section); without JavaScript, two empty rows are added after each save.</p>
        @foreach($rows as $index => $row)
            <div class="stu-form-grid" data-item-row data-row-index="{{ $index }}">
                @if(filled($row['id'] ?? null))<input type="hidden" name="items[{{ $section }}][{{ $index }}][id]" value="{{ $row['id'] }}">@endif
                @foreach($fields as $field)
                    @if($field === 'body')
                        <label class="stu-full">{{ $labels[$field] }}<textarea name="items[{{ $section }}][{{ $index }}][body]" rows="2" maxlength="3000">{{ $row['body'] ?? '' }}</textarea></label>
                        <label class="stu-full">{{ $labels[$field] }} (Bengali)<textarea name="items[{{ $section }}][{{ $index }}][body_bn]" lang="bn" rows="2" maxlength="3000">{{ $row['body_bn'] ?? '' }}</textarea></label>
                    @elseif($field === 'amount')
                        <label>{{ $labels[$field] }}<input name="items[{{ $section }}][{{ $index }}][amount]" value="{{ $row['amount'] ?? '' }}" maxlength="80"></label>
                    @else
                        <label>{{ $labels[$field] }}<input name="items[{{ $section }}][{{ $index }}][title]" value="{{ $row['title'] ?? '' }}" maxlength="300"></label>
                        <label>{{ $labels[$field] }} (Bengali)<input name="items[{{ $section }}][{{ $index }}][title_bn]" lang="bn" value="{{ $row['title_bn'] ?? '' }}" maxlength="300"></label>
                    @endif
                @endforeach
                @if($isFact || $section === 'source')
                    <label>Source link (https){{ $isFact ? '' : ' — required' }}<input type="url" name="items[{{ $section }}][{{ $index }}][source_url]" value="{{ $row['source_url'] ?? '' }}" maxlength="2048"></label>
                    <label>Source label, shown as "Source: …"<input name="items[{{ $section }}][{{ $index }}][source_label]" value="{{ $row['source_label'] ?? '' }}" maxlength="160"></label>
                    @if($isFact && filled($row['title'] ?? null) && blank($row['source_url'] ?? null))<p class="stu-full egh-alert is-error" data-missing-source>No source for this fact yet.</p>@endif
                @endif
                @if($section === 'source')
                    <label class="stu-check"><input type="checkbox" name="items[{{ $section }}][{{ $index }}][needs_verification]" value="1" @checked((bool) ($row['needs_verification'] ?? false))> Verify before publishing (does not count as a checked source)</label>
                @endif
                @if(filled($row['id'] ?? null))<label class="stu-check"><input type="checkbox" name="items[{{ $section }}][{{ $index }}][remove]" value="1"> Remove this row</label>@endif
            </div>
            @if(! $loop->last)<hr>@endif
        @endforeach
        <button type="button" class="egh-button secondary" data-add-row data-max-rows="{{ \App\Http\Requests\Admin\UpdateStudyDestinationRequest::MAX_ROWS }}" hidden>Add row</button>
    </fieldset>
@endforeach

<div class="egh-card"><button class="egh-button">Save</button></div>
</form>
@endsection
