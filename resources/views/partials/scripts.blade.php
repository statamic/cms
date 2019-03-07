<script>
    window.Statamic = @json(Statamic::jsonVariables(request()))

    @if(session()->has('success'))
        Statamic.flash = [{
            type:    'success',
            message: '{{ session()->get('success') }}',
        }];
    @endif
</script>

<script src="{{ Statamic::assetUrl('js/manifest.js') }}?v={{ Statamic::version() }}"></script>
<script src="{{ Statamic::assetUrl('js/vendor.js') }}?v={{ Statamic::version() }}"></script>
<script src="{{ Statamic::assetUrl('js/bootstrap.js') }}?v={{ Statamic::version() }}"></script>

@foreach (Statamic::availableScripts(request()) as $name => $path)
    <script src="{{ Statamic::url("vendor/$name/js/$path") }}"></script>
@endforeach

<script src="{{ Statamic::assetUrl('js/app.js') }}?v={{ Statamic::version() }}"></script>
