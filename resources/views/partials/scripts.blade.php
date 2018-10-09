<script src="{{ cp_resource_url('js/manifest.js') }}?v={{ Statamic::version() }}"></script>
<script src="{{ cp_resource_url('js/vendor.js') }}?v={{ Statamic::version() }}"></script>
<script src="{{ cp_resource_url('js/bootstrap.js') }}?v={{ Statamic::version() }}"></script>

@foreach (Statamic::availableScripts(request()) as $name => $path)
    <script src="{{ resource_url("vendor/$name/js/$path") }}"></script>
@endforeach

<script src="{{ cp_resource_url('js/app.js') }}?v={{ Statamic::version() }}"></script>
