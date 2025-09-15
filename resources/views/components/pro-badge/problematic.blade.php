<a href="{{ cp_route('utilities.licensing') }}" class="
    {{ $base }} {{ $sizeDefault }} {{ $flat }}

    @if ($requestFailed)
        {{ $yellow }}
    @elseif ($onPublicDomain)
        {{ $red }}
    @else
        {{ $green }}
    @endif
">
    {{ __('Pro') }} – {{ $onPublicDomain ? __('statamic::messages.licensing_error_unlicensed') : __('Trial Mode') }}
</a>
