<script src="{{ Statamic::cpAssetUrl('js/manifest.js') }}?v={{ Statamic::version() }}"></script>
<script src="{{ Statamic::cpAssetUrl('js/vendor.js') }}?v={{ Statamic::version() }}"></script>
<script src="{{ Statamic::cpAssetUrl('js/app.js') }}?v={{ Statamic::version() }}"></script>

@foreach (Statamic::availableScripts(request()) as $name => $path)
    <script src="{{ Statamic::vendorAssetUrl("$name/js/$path") }}"></script>
@endforeach

<script>
    Statamic.config(@json(Statamic::jsonVariables(request())));
    Statamic.start();
</script>

