@once('statamic-image-choice-styles')
    @php
        $stylesheet = base_path('vendor/statamic/cms/resources/css/components/fieldtypes/image-choice.css');
    @endphp
    @if (is_file($stylesheet))
        <style>{!! \Illuminate\Support\Facades\File::get($stylesheet) !!}</style>
    @else
        <link rel="stylesheet" href="{{ asset('vendor/statamic/forms/image-choice.css') }}">
    @endif
@endonce
