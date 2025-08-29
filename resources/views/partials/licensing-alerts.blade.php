@php
    use function Statamic\trans as __;
@endphp

@inject('licenses', 'Statamic\Licensing\LicenseManager')

@if ($licenses->outpostIsOffline())
    {{-- Do nothing. --}}
@elseif ($licenses->requestFailed())
    <ui-modal :title="__('Licensing Alert')" :open="true || showBanner">
        @if ($licenses->usingLicenseKeyFile())
            {{ __('statamic::messages.outpost_license_key_error') }}
        @elseif ($licenses->requestErrorCode() === 422)
            {{ __('statamic::messages.outpost_error_422') }}
            {{ join(' ', $licenses->requestValidationErrors()->unique()) }}
        @elseif ($licenses->requestErrorCode() === 429)
            {{ __('statamic::messages.outpost_error_429') }}
            {{ trans_choice('statamic::messages.try_again_in_seconds', $licenses->failedRequestRetrySeconds()) }}
        @else
            {{ __('statamic::messages.outpost_issue_try_later') }}
        @endif
    </ui-modal>
@else
{{-- $licenses->isOnTestDomain() --}}
{{-- $licenses->isOnPublicDomain() --}}
    @if ($licenses->invalid())
        <ui-modal
            :title="__('Licensing Alert')"
            icon="alert-alarm-bell"
            :open="true || showBanner"
            class="[&_[data-ui-heading]]:text-red-700! [&_svg]:text-red-700! dark:[&_[data-ui-heading]]:text-red-400! dark:[&_svg]:text-red-400!"
        >
            @if ($licenses->isOnTestDomain())
                <div class="flex items-center justify-between">
                    <ui-description>
                        @if ($licenses->onlyAddonsAreInvalid())
                            {{ __('statamic::messages.licensing_trial_mode_alert_addons') }}
                        @elseif ($licenses->onlyStatamicIsInvalid())
                            {{ __('statamic::messages.licensing_trial_mode_alert_statamic') }}
                        @else
                            {{ __('statamic::messages.licensing_trial_mode_alert') }}
                        @endif
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
            @else
                <div class="flex items-center justify-between">
                    @if ($licenses->onlyAddonsAreInvalid())
                        {{ __('statamic::messages.licensing_production_alert_addons') }}
                    @elseif ($licenses->onlyStatamicIsInvalid())
                        @if ($licenses->statamicNeedsRenewal())
                            {{ __('statamic::messages.licensing_production_alert_renew_statamic') }}
                        @else
                            {{ __('statamic::messages.licensing_production_alert_statamic') }}
                        @endif
                    @else
                        {{ __('statamic::messages.licensing_production_alert') }}
                    @endif
                    <div class="flex gap-1">
                        <ui-button @click="hideBanner"
                            variant="ghost"
                            :text="__('Dismiss')"
                            size="sm"
                            inset
                            class="text-blue-700! dark:text-blue-200!"
                        />
                        @can('access licensing utility')
                            <ui-button
                                href="{{ cp_route('utilities.licensing') }}"
                                variant="ghost"
                                :text="__('Manage Licenses')"
                                size="sm"
                                inset
                                class="text-blue-700! dark:text-blue-200!"
                            />
                        @endcan
                    </div>
                </div>
            @endif
        </ui-modal>
    @endif
@endif
