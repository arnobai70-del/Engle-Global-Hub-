@extends('layouts.admin')
@section('title', 'Business facts')
@section('content')
<x-admin.page-header title="Business facts & numbers" description="The facts and numbers the website shows: WhatsApp, office hours, trade licence, founding year, ratings, costs and fees. Fill in only values you can prove. An empty field is simply not shown on the website." icon="F" eyebrow="Website CMS" />
@if(session('status'))<div class="egho-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egho-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('admin.facts.update') }}">@csrf @method('PATCH')
    @foreach($sections as $section => $fields)
        <section class="egh-card" aria-labelledby="facts-{{ \Illuminate\Support\Str::slug($section) }}">
            <h2 id="facts-{{ \Illuminate\Support\Str::slug($section) }}">{{ $section }}</h2>
            <div class="stu-form-grid">
                @foreach($fields as $key => [$label, $help, $max])
                    <label @class(['stu-full' => $max > 100])>{{ $label }}
                        @if($max > 300)
                            <textarea name="{{ $key }}" maxlength="{{ $max }}" rows="3">{{ old($key, $values[$key] ?? '') }}</textarea>
                        @else
                            <input name="{{ $key }}" maxlength="{{ $max }}" value="{{ old($key, $values[$key] ?? '') }}">
                        @endif
                        @if($help !== '')<small class="stu-muted">{{ $help }}</small>@endif
                    </label>
                @endforeach
            </div>
        </section>
    @endforeach
    <section class="egh-card" aria-labelledby="facts-fees">
        <h2 id="facts-fees">Service fees ("from BDT …" on each service page)</h2>
        <p class="stu-muted">Enter the lowest fee you actually charge, as a number or range, for example 3,500. Leave empty to show "Free quote" instead.</p>
        <div class="stu-form-grid">
            @foreach($feeFields as $key => $label)
                <label>{{ $label }}<input name="{{ $key }}" maxlength="30" value="{{ old($key, $values[$key] ?? '') }}"></label>
            @endforeach
        </div>
    </section>
    <div class="admin-form-actions"><button class="egh-button" type="submit">Save facts</button></div>
</form>
@endsection
