<script src="{{ Statamic::cpAssetUrl('js/manifest.js') }}?v={{ Statamic::version() }}"></script>
<script src="{{ Statamic::cpAssetUrl('js/vendor.js') }}?v={{ Statamic::version() }}"></script>
<script src="{{ Statamic::cpAssetUrl('js/app.js') }}?v={{ Statamic::version() }}"></script>

@foreach (Statamic::availableExternalScripts(request()) as $url)
    <script src="{{ $url }}"></script>
@endforeach

@foreach (Statamic::availableScripts(request()) as $package => $paths)
    @foreach ($paths as $path)
        <script src="{{ Statamic::vendorPackageAssetUrl($package, $path, 'js') }}"></script>
    @endforeach
@endforeach

@foreach (Statamic::availableVites(request()) as $package => $vite)
    {{ \Illuminate\Support\Facades\Vite::useHotFile($vite['hotFile'])
           ->useBuildDirectory($vite['buildDirectory'])
           ->withEntryPoints($vite['input']) }}
@endforeach

<script>
    Statamic.config(@json(array_merge(Statamic::jsonVariables(request()), [
        'wrapperClass' => $__env->getSection('wrapper_class', 'max-w-xl')
    ])));
</script>

{{-- Deferred to allow Vite modules to load first --}}
<script src="data:text/javascript;base64,{{ base64_encode('Statamic.start()') }}" defer></script>
