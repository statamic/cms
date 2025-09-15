@inject('licenses', 'Statamic\Licensing\LicenseManager')

@php
$requestFailed = $licenses->requestFailed();
$onPublicDomain = $licenses->isOnPublicDomain();

$base = 'relative inline-flex items-center justify-center gap-1 font-normal antialiased whitespace-nowrap no-underline not-prose [button]:cursor-pointer group [&_svg]:opacity-60 [&_svg]:group-hover:opacity-80 dark:[&_svg]:group-hover:opacity-70';
$sizeDefault = 'text-xs leading-5.5 px-2 rounded-sm [&_svg]:size-3.5 gap-2';
$sizeSm = 'text-2xs leading-normal px-1.25 rounded-[0.1875rem] [&_svg]:size-2.5';
$flat = 'border-0 shadow-none!';
$yellow = 'bg-yellow-100 border-yellow-400 text-yellow-700 dark:bg-yellow-300/6 dark:text-yellow-300 [a]:hover:bg-yellow-200/80 [button]:hover:bg-yellow-200/80 dark:[a]:hover:bg-yellow-300/15';
$red = 'bg-red-100/80 border-red-400/80 text-red-700 dark:bg-red-300/6 dark:text-red-300 [a]:hover:bg-red-200/60 [button]:hover:bg-red-200/60 dark:[a]:hover:bg-red-300/15';
$green = 'bg-green-100/80 border-green-400 text-green-700 dark:bg-green-300/6 dark:text-green-300 [a]:hover:bg-green-200/60 [button]:hover:bg-green-200/60 dark:[a]:hover:bg-green-300/15';
@endphp

@if ($licenses->valid())
    <div class="{{ $base }} {{ $flat }} {{ $sizeSm }} bg-white/15!">
        {{ __('Pro') }}
    </div>
@else

    {{--
        Rendered twice:
        - Once without being wrapped in a tooltip to prevent vue pop-in.
        - Again surrounded by the tooltip so we can... have the tooltip.
    --}}
    <div v-if="false">
        <x-statamic::pro-badge.problematic
            :$requestFailed :$onPublicDomain
            :$base :$sizeDefault :$flat :$yellow :$red :$green
        />
    </div>

    <ui-tooltip v-cloak :text="{{ $requestFailed ? "'".$licenses->requestFailureMessage()."'" : 'null' }}">
        <x-statamic::pro-badge.problematic
            :$requestFailed :$onPublicDomain
            :$base :$sizeDefault :$flat :$yellow :$red :$green
        />
    </ui-tooltip>

@endif
