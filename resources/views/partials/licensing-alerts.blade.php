@php
    use function Statamic\trans as __;
@endphp

@inject('licenses', 'Statamic\Licensing\LicenseManager')

@if ($alert = $licenses->licensingAlert())
    <licensing-alert
        message="{{ $alert['message'] }}"
        :testing="{{ \Statamic\Support\Str::bool($alert['testing']) }}"
        @can('access licensing utility')
        manage-url="{{ cp_route('utilities.licensing') }}"
        @endcan
    ></licensing-alert>
@endif
