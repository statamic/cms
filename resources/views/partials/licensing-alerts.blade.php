@php
    use function Statamic\trans as __;
@endphp

@inject('licenses', 'Statamic\Licensing\LicenseManager')

@if ($licenses->outpostIsOffline())
    {{-- Do nothing. --}}
@elseif ($licenses->requestFailed())
    <div class="fixed bottom-0 z-20 w-full p-2">
        <div
            class="w-full rounded-lg bg-yellow-200 border border-yellow-400 px-4 py-2 text-sm dark:border-none dark:bg-blue-500"
        >
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
        </div>
    </div>
@else
    @if ($licenses->invalid())
        <div class="fixed bottom-0 z-20 w-full p-2" v-cloak v-show="showBanner">
            <div
                @class([
                    'w-full rounded-lg px-4 py-2 text-sm',
                    'bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-200' => $licenses->isOnTestDomain(),
                    'bg-red-500 text-white' => $licenses->isOnPublicDomain(),
                ])
            >
                @if ($licenses->isOnTestDomain())
                    <div class="flex items-center justify-between">
                        <span>
                            <b class="me-2">{{ __('Trial Mode') }}:</b>
                            @if ($licenses->onlyAddonsAreInvalid())
                                {{ __('statamic::messages.licensing_trial_mode_alert_addons') }}
                            @elseif ($licenses->onlyStatamicIsInvalid())
                                {{ __('statamic::messages.licensing_trial_mode_alert_statamic') }}
                            @else
                                {{ __('statamic::messages.licensing_trial_mode_alert') }}
                            @endif
                        </span>
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
            </div>
        </div>
    @endif
@endif
