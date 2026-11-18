{{-- "Our numbers": only figures the owner entered in Admin › Business facts. @var list<array{value: string, label: string}> $numbers --}}
@if($numbers !== [])
<dl class="v2-numbers">
    @foreach($numbers as $number)<div><dt>{{ $number['label'] }}</dt><dd>{{ $number['value'] }}</dd></div>@endforeach
</dl>
@endif
