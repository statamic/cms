@once('statamic-opinion-scale-styles')
    @php
        $stylesheet = base_path('vendor/statamic/cms/resources/css/components/fieldtypes/opinion-scale.css');
    @endphp
    @if (is_file($stylesheet))
        <style>{!! \Illuminate\Support\Facades\File::get($stylesheet) !!}</style>
    @else
        <link rel="stylesheet" href="{{ asset('vendor/statamic/forms/opinion-scale.css') }}">
    @endif
@endonce
