@foreach (Statamic::availableExternalScripts(request()) as $url)
    <script src="{!! $url !!}" defer></script>
@endforeach

@foreach (Statamic::availableScripts(request()) as $package => $paths)
    @foreach ($paths as $path)
        <script src="{{ Statamic::vendorPackageAssetUrl($package, $path, 'js') }}" defer></script>
    @endforeach
@endforeach

@foreach (Statamic::availableVites(request()) as $package => $vite)
    {{
        \Illuminate\Support\Facades\Vite::useHotFile($vite['hotFile'])
            ->useBuildDirectory($vite['buildDirectory'])
            ->withEntryPoints($vite['input'])
    }}
@endforeach

@foreach (Statamic::availableInlineScripts(request()) as $script)
    <script>
        {!! $script !!};
    </script>
@endforeach

<script>
    var StatamicConfig = {!! json_encode(Statamic::jsonVariables(request())) !!};
</script>

{{-- Deferred to allow Vite modules to load first --}}
<script
    src="data:text/javascript;base64,{{ base64_encode('Statamic.config(StatamicConfig); Statamic.start()') }}"
    defer
></script>
