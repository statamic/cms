@inject('licenses', 'Statamic\Licensing\LicenseManager')

@if ($licenses->valid())
    <ui-badge size="sm" variant="flat" text="{{ __('Pro') }}" class="hidden sm:block select-none bg-white/15!" />
@else
    <ui-tooltip :text="{{ $licenses->requestFailed() ? "'".$licenses->requestFailureMessage()."'" : 'null' }}">
        <ui-badge
            @if ($licenses->requestFailed())
                color="yellow"
                icon="alert-warning-exclamation-mark"
            @elseif ($licenses->isOnPublicDomain())
                color="red"
            @else
                color="green"
            @endif
            href="{{ cp_route('utilities.licensing') }}"
            text="{{ __('Pro') }} – {{ $licenses->isOnPublicDomain() ? __('statamic::messages.licensing_error_unlicensed') : __('Trial Mode') }}"
        ></ui-badge>
    </ui-tooltip>
@endif
