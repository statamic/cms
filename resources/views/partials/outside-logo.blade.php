<div class="logo pt-20 relative z-10">
    @if ($customLogo)
        <img src="{{ $customLogo }}" alt="{{ config('statamic.cp.custom_cms_name') }}" class="white-label-logo dark:hidden">
        <img src="{{ $customDarkLogo }}" alt="{{ config('statamic.cp.custom_cms_name') }}" class="white-label-logo hidden dark:block">
    @elseif ($customLogoText)
        <div class="max-w-xs mx-auto mb-8 text-lg font-medium text-center opacity-50">{{ $customLogoText }}</div>
    @else
        @cp_svg('statamic-wordmark')
    @endif
</div>
