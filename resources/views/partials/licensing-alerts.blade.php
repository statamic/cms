@php
    use function Statamic\trans as __;
@endphp

@inject('licenses', 'Statamic\Licensing\LicenseManager')

@php
    $alert = $licenses->getLicensingAlert();
@endphp

@if ($alert)
    <ui-modal
        :title="__('Licensing Alert')"
        :open="showBanner"
        icon="alert-alarm-bell"
        @if($alert['variant'] === 'danger') class="[&_[data-ui-heading]]:text-red-700! [&_svg]:text-red-700! dark:[&_[data-ui-heading]]:text-red-400! dark:[&_svg]:text-red-400!" @endif
    >
        <div class="flex items-center justify-between">
            <ui-description>
                {{ $alert['message'] }}
            </ui-description>
        </div>
        <template #footer>
            <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                <ui-button @click="hideBanner" :text="__('Snooze')" variant="ghost" tabindex="-1" />
                @can('access licensing utility')
                    <ui-button href="{{ cp_route('utilities.licensing') }}" :text="__('Manage Licenses')" />
                @endcan
            </div>
        </template>
    </ui-modal>
@endif
