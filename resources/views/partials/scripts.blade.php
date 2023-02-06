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

@php
    $start = 'Statamic.config('.json_encode(array_merge(Statamic::jsonVariables(request()), [
        'wrapperClass' => $__env->getSection('wrapper_class', 'max-w-xl')
    ])).'); Statamic.start()';
@endphp

{{-- Deferred to allow Vite modules to load first --}}
<script src="data:text/javascript;base64,{{ base64_encode($start) }}" defer></script>
