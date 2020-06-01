@inject('licenses', 'Statamic\Licensing\LicenseManager')

@if ($licenses->requestFailed())
    <div class="p-1 px-4 text-sm w-full bg-yellow">
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
@else
    @if ($licenses->invalid())
        <div class="
            p-1 px-4 text-sm w-full
            @if ($licenses->isOnTestDomain()) bg-blue-900 text-grey-30 @endif
            @if ($licenses->isOnPublicDomain()) bg-red text-white @endif
        ">
            @if ($licenses->isOnTestDomain())
                <b>{{ __('Trial Mode') }}:</b>
                {{ __('statamic::messages.licensing_trial_mode_alert') }}
                @can('access licensing utility') <a href="{{ cp_route('utilities.licensing') }}" class="text-blue-200">{{ __('View details') }}</a> @endcan
            @else
                {{ __('statamic::messages.licensing_production_alert') }}
                @can('access licensing utility') <a href="{{ cp_route('utilities.licensing') }}" class="text-white underline">{{ __('View details') }}</a> @endcan
            @endif
        </div>
    @endif
@endif
