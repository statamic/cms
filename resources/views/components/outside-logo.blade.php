<div class="logo relative z-10 pt-20">
    @if (isset($customLogo))
        <img
            src="{{ $customLogo }}"
            alt="{{ config('statamic.cp.custom_cms_name') }}"
            class="white-label-logo dark:hidden"
        />
        <img
            src="{{ $customDarkLogo }}"
            alt="{{ config('statamic.cp.custom_cms_name') }}"
            class="white-label-logo hidden dark:block"
        />
    @elseif (isset($customLogoText))
        <div class="mx-auto mb-8 max-w-xs text-center text-lg font-medium opacity-50">{{ $customLogoText }}</div>
    @else
        @cp_svg('statamic-wordmark')
    @endif
</div>
