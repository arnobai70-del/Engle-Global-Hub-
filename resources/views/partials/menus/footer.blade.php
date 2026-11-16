{{-- Admin-managed footer columns (Admin › Menus). Each top-level item is a column heading. --}}
@foreach($items as $column)
    <div><h3>{{ $column['label'] }}</h3><ul>
        @if($column['url'] !== null && $column['children'] === [])<li><a href="{{ $column['url'] }}">{{ $column['label'] }}</a></li>@endif
        @foreach($column['children'] as $link)<li><a href="{{ $link['url'] }}" @if($link['new_tab']) target="_blank" rel="noopener noreferrer" @endif>{{ $link['label'] }}</a></li>@endforeach
        @if($loop->first)@foreach($footerLinks ?? [] as $extra)@if(!empty($extra['url'])&&!empty($extra['title']))<li><a href="{{ $extra['url'] }}">{{ $extra['title'] }}</a></li>@endif @endforeach @endif
    </ul></div>
@endforeach
