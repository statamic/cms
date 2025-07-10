@php
    use function Statamic\trans as __;
@endphp

@inject('licenses', 'Statamic\Licensing\LicenseManager')

@if ($licenses->requestFailed())
    <div class="fixed bottom-0 z-20 w-full p-2">
        <div
            class="w-full rounded-md border border-yellow-dark bg-yellow px-4 py-3 text-sm dark:border-none dark:bg-dark-blue-100"
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
        <div class="fixed bottom-0 z-2 w-full px-2" v-cloak v-show="showBanner">
            <div
                class="@if ($licenses->isOnTestDomain()) bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-200 @endif @if ($licenses->isOnPublicDomain()) bg-red-500 text-white @endif w-full rounded-t-xl px-4 py-3 text-sm"
            >
                @if ($licenses->isOnTestDomain())
                    <div class="flex items-center justify-between">
                        <span>
                            <b class="ltr:mr-2 rtl:ml-2">{{ __('Trial Mode') }}:</b>
                            @if ($licenses->onlyAddonsAreInvalid())
                                {{ __('statamic::messages.licensing_trial_mode_alert_addons') }}
                            @elseif ($licenses->onlyStatamicIsInvalid())
                                {{ __('statamic::messages.licensing_trial_mode_alert_statamic') }}
                            @else
                                {{ __('statamic::messages.licensing_trial_mode_alert') }}
                            @endif
                        </span>
                        <div class="flex">
                            <button @click="hideBanner" class="text-2xs opacity-60 hover:opacity-75 ltr:mr-4 rtl:ml-4">
                                {{ __('Dismiss') }}
                            </button>
                            @can('access licensing utility')
                                <a
                                    href="{{ cp_route('utilities.licensing') }}"
                                    class="flex items-center text-2xs underline-offset-1 hover:underline"
                                    aria-label="{{ __('Manage Licenses') }}"
                                >
                                    {{ __('Manage Licenses') }}
                                    @rarr
                                </a>
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
                        <div class="flex">
                            <button @click="hideBanner" class="text-2xs opacity-50 hover:opacity-75 ltr:mr-4 rtl:ml-4">
                                {{ __('Dismiss') }}
                            </button>
                            @can('access licensing utility')
                                <a
                                    href="{{ cp_route('utilities.licensing') }}"
                                    class="flex items-center text-2xs text-white hover:text-yellow"
                                    aria-label="{{ __('Manage Licenses') }}"
                                >
                                    {{ __('Manage Licenses') }}
                                    @rarr
                                </a>
                            @endcan
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif
