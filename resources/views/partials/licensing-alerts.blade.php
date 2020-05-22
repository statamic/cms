@inject('licenses', 'Statamic\Licensing\LicenseManager')

@if ($licenses->requestFailed())
    <div class="p-1 px-4 text-sm w-full bg-yellow">
        @if ($licenses->requestErrorCode() === 422)
            Error communicating with statamic.com: {{ join(' ', $licenses->requestValidationErrors()->unique()) }}
        @elseif ($licenses->requestErrorCode() === 429)
            Too many requests to statamic.com.
            Try again in {{$licenses->failedRequestRetrySeconds() }} seconds.
        @else
            Could not communicate with statamic.com. Please try again later.
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
                <b>Trial Mode:</b>
                You have licensing issues to resolve before moving to production.
                <a href="{{ cp_route('licensing') }}" class="text-blue-200">View details</a>
            @else
                Please purchase and enter your license key or risk violating the License Agreement.
                <a href="{{ cp_route('licensing') }}" class="text-white underline">View details</a>
            @endif
        </div>
    @endif
@endif
