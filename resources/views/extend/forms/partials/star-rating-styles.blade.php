@once('statamic-star-rating-styles')
    @php
        $stylesheet = base_path('vendor/statamic/cms/resources/css/components/fieldtypes/star-rating.css');
    @endphp
    @if (is_file($stylesheet))
        <style>{!! \Illuminate\Support\Facades\File::get($stylesheet) !!}</style>
    @else
        <link rel="stylesheet" href="{{ asset('vendor/statamic/forms/star-rating.css') }}">
    @endif
@endonce
