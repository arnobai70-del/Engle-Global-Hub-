@php
$editing=$block!==null;$prefix=$editing?'block_'.$block->id.'_':'new_';$metaValue=$editing&&$block->meta?json_encode($block->meta,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE):'';
@endphp
<div class="home-grid">
<div class="home-field"><label>Section</label><select name="section" required>@foreach($sections as $value=>$label)<option value="{{ $value }}" @selected(old('section',$block?->section)===$value)>{{ $label }}</option>@endforeach</select></div>
<div class="home-field"><label>Key</label><input name="key" value="{{ old('key',$block?->key) }}" placeholder="unique-section-key"></div>
<div class="home-field"><label>Sort order</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order',$block?->sort_order??0) }}" required></div>
<div class="home-field"><label>Title / name</label><input name="title" value="{{ old('title',$block?->title) }}"></div>
<div class="home-field"><label>Subtitle / country / role</label><input name="subtitle" value="{{ old('subtitle',$block?->subtitle) }}"></div>
<div class="home-field"><label>Icon</label><select name="icon"><option value="">Default</option>@foreach(['plane','hotel','globe','document','briefcase','gift','train','bus','car','shield','star','user','check','card','headset','lock','list'] as $icon)<option value="{{ $icon }}" @selected(old('icon',$block?->icon)===$icon)>{{ str($icon)->headline() }}</option>@endforeach</select></div>
<div class="home-field wide"><label>Body / quote</label><textarea name="body">{{ old('body',$block?->body) }}</textarea></div>
<div class="home-field"><label>Upload image</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/avif"></div><div class="home-field"><label>Or image URL</label><input type="url" name="image_url" placeholder="https://…"></div><div class="home-field"><label>Image alt text</label><input name="image_alt" value="{{ old('image_alt',$block?->image_alt) }}"></div>
<div class="home-field"><label>Destination URL</label><input name="url" value="{{ old('url',$block?->url) }}"></div><div class="home-field"><label>CTA label</label><input name="cta_label" value="{{ old('cta_label',$block?->cta_label) }}"></div>
<div class="home-field wide"><label>Metadata JSON</label><textarea name="meta" placeholder='{"feature":"flights","items":["One","Two"]}'>{{ old('meta',$metaValue) }}</textarea><small>Examples: destination {"tag":"UAE"}; promotion {"badge":"Flights","feature":"flights"}; panel {"class":"is-visa","service":"visa","items":["Tourist Visa"]}.</small></div>
<label class="home-check wide"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$editing?$block->is_active:true))> Published</label>
</div>
