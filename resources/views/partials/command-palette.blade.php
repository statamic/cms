@php
    use function Statamic\trans as __;
    ray($commandPalette);
@endphp

@section('command-palette')
<command-palette
    :initial-data="{{ $commandPalette->toJson() }}"
></command-palette>
@stop

@yield('command-palette')
