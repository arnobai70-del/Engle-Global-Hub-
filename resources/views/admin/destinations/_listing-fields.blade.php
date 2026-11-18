{{-- Fields of a hotel / restaurant / experience listing. @var \App\Models\DestinationListing|null $listing --}}
@php($old = static fn (string $key, mixed $default): mixed => old('listing_form') ? old($key, $default) : $default)
<input type="hidden" name="listing_form" value="1">
<div class="stu-form-grid">
    <label>Type<select name="type" required>@foreach(\App\Models\DestinationListing::TYPES as $value => $label)<option value="{{ $value }}" @selected($old('type', $listing?->type) === $value)>{{ ['hotel' => 'Hotel (Where to stay)', 'restaurant' => 'Restaurant (Where to eat)', 'experience' => 'Experience (Things to do)'][$value] }}</option>@endforeach</select></label>
    <label>Name<input name="name" value="{{ $old('name', $listing?->name) }}" maxlength="150" required></label>
    <label>Area (optional)<input name="area" value="{{ $old('area', $listing?->area) }}" maxlength="150" placeholder="For example: Kolatoli"></label>
    <label>Price range (optional)<select name="price_range"><option value="">Not shown</option>@foreach(\App\Models\DestinationListing::PRICE_RANGES as $value => $label)<option value="{{ $value }}" @selected($old('price_range', $listing?->price_range) === $value)>{{ $label }}</option>@endforeach</select></label>
    <label class="stu-full">Short description (optional)<textarea name="description" maxlength="2000" rows="3">{{ $old('description', $listing?->description) }}</textarea></label>
    <label>Official website (https, optional)<input type="url" name="website_url" value="{{ $old('website_url', $listing?->website_url) }}" maxlength="2048" placeholder="https://"></label>
    <label>Map link (Google Maps or OpenStreetMap, optional)<input type="url" name="map_url" value="{{ $old('map_url', $listing?->map_url) }}" maxlength="2048"></label>
    <label>Display order<input type="number" name="sort_order" min="0" max="65535" required value="{{ $old('sort_order', $listing?->sort_order ?? 0) }}"></label>
    <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="is_published" value="1" @checked($old('is_published', $listing?->is_published))> Published (verified, shown on the destination page)</label>
</div>
