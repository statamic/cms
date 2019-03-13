<script src="{{ Statamic::assetUrl('js/manifest.js') }}?v={{ Statamic::version() }}"></script>
<script src="{{ Statamic::assetUrl('js/vendor.js') }}?v={{ Statamic::version() }}"></script>
<script src="{{ Statamic::assetUrl('js/bootstrap.js') }}?v={{ Statamic::version() }}"></script>

@foreach (Statamic::availableScripts(request()) as $name => $path)
    <script src="{{ Statamic::url("vendor/$name/js/$path") }}"></script>
@endforeach

<script>
    Statamic.booting(function (Statamic) {
        Statamic.$store.commit('statamic/config', @json(Statamic::jsonVariables(request())));
    });
</script>

<script src="{{ Statamic::assetUrl('js/app.js') }}?v={{ Statamic::version() }}"></script>
