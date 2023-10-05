@foreach (Statamic::availableExternalScripts(request()) as $url)
    <script src="{{ $url }}" defer></script>
@endforeach

@foreach (Statamic::availableScripts(request()) as $package => $paths)
    @foreach ($paths as $path)
        <script src="{{ Statamic::vendorPackageAssetUrl($package, $path, 'js') }}" defer></script>
    @endforeach
@endforeach

@foreach (Statamic::availableVites(request()) as $package => $vite)
    {{ \Illuminate\Support\Facades\Vite::useHotFile($vite['hotFile'])
           ->useBuildDirectory($vite['buildDirectory'])
           ->withEntryPoints($vite['input']) }}
@endforeach

<script>
var StatamicConfig = @json(array_merge(Statamic::jsonVariables(request()), [
    'wrapperClass' => $__env->getSection('wrapper_class', 'max-w-xl')
]));

{{-- Deferred to allow Vite modules to load first --}}
window.addEventListener('DOMContentLoaded', () => {
    Statamic.config(StatamicConfig); Statamic.start();
});
</script>
