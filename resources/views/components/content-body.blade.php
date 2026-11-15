@props(['text' => '', 'headingTag' => 'h2'])
{{--
    Renders admin-written plain text safely (everything is escaped):
    blank lines separate blocks; a block starting with "## " is a heading;
    a block whose lines all start with "- " is a bulleted list.
--}}
@foreach(preg_split('/\R{2,}/', trim((string) $text)) as $block)
    @php($lines = preg_split('/\R/', trim($block)))
    @if(trim($block) === '')
        @continue
    @elseif(str_starts_with($lines[0], '## ') && count($lines) === 1)
        <{{ $headingTag }}>{{ trim(substr($lines[0], 3)) }}</{{ $headingTag }}>
    @elseif(str_starts_with($lines[0], '## '))
        <{{ $headingTag }}>{{ trim(substr($lines[0], 3)) }}</{{ $headingTag }}>
        @php($rest = array_slice($lines, 1))
        @if(collect($rest)->every(fn ($line) => str_starts_with(ltrim($line), '- ')))
            <ul>@foreach($rest as $line)<li>{{ trim(substr(ltrim($line), 2)) }}</li>@endforeach</ul>
        @else
            <p>{!! nl2br(e(implode("\n", $rest))) !!}</p>
        @endif
    @elseif(collect($lines)->every(fn ($line) => str_starts_with(ltrim($line), '- ')))
        <ul>@foreach($lines as $line)<li>{{ trim(substr(ltrim($line), 2)) }}</li>@endforeach</ul>
    @else
        <p>{!! nl2br(e($block)) !!}</p>
    @endif
@endforeach
