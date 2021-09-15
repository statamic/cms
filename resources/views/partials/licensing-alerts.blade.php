@inject('licenses', 'Statamic\Licensing\LicenseManager')

@if ($licenses->requestFailed())
    <div class="p-1 w-full fixed bottom-0 z-20">
        <div class="py-1.5 px-2 text-sm w-full rounded-md bg-yellow border border-yellow-dark">
        @if ($licenses->requestErrorCode() === 422)
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
        <div class="p-1 w-full fixed bottom-0 z-20" v-show="showBanner">
            <div class="
                py-1.5 px-2 text-sm w-full rounded-md
                @if ($licenses->isOnTestDomain()) bg-grey-80 text-grey-30 @endif
                @if ($licenses->isOnPublicDomain()) bg-red text-white @endif
            ">
                @if ($licenses->isOnTestDomain())
                    <div class="flex items-center justify-between">
                        <span>
                            <b class="mr-1">{{ __('Trial Mode') }}:</b>
                            @if ($licenses->onlyAddonsAreInvalid())
                                This site is using commercial addons. Make sure to buy licenses before launching. Thanks!
                            @elseif ($licenses->onlyStatamicIsInvalid())
                                This site is using Statamic Pro. Make sure to buy a license before launching. Thanks!
                            @else
                                This site is using Statamic Pro and commercial addons. Make sure to buy licenses before launching. Thanks!
                            @endif
                        </span>
                        <div class="flex">
                            <button @click="hideBanner" class="mr-2 text-2xs opacity-50 hover:opacity-75">{{ __('Dismiss') }}</button>
                            @can('access licensing utility')
                            <a href="{{ cp_route('utilities.licensing') }}" class="text-2xs text-white hover:text-yellow flex items-center" aria-label="{{ __('Manage Licenses') }}">
                                {{ __('Manage Licenses') }} &rarr;
                            </a>
                            @endcan
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        @if ($licenses->onlyAddonsAreInvalid())
                            This site is using commercial addons. Please purchase appropriate licenses.
                        @elseif ($licenses->onlyStatamicIsInvalid())
                            This site is using Statamic Pro. Please purchase a license.
                        @else
                            This site is using Statamic Pro and commercial addons. Please purchase appropriate licenses.
                        @endif
                        <div class="flex">
                            <button @click="hideBanner" class="mr-2 text-2xs opacity-50 hover:opacity-75">{{ __('Dismiss') }}</button>
                            @can('access licensing utility')
                                <a href="{{ cp_route('utilities.licensing') }}" class="text-2xs text-white hover:text-yellow flex items-center" aria-label="{{ __('Manage Licenses') }}">
                                    {{ __('Manage Licenses') }} &rarr;
                                </a>
                            @endcan
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif
