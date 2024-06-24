<div class="logo pt-20">
    @if ($customLogo)
        <img src="{{ $customLogo }}" alt="{{ config('statamic.cp.custom_cms_name') }}" class="white-label-logo dark:hidden">
        <img src="{{ $customDarkLogo }}" alt="{{ config('statamic.cp.custom_cms_name') }}" class="white-label-logo hidden dark:block">
    @else
        @cp_svg('statamic-wordmark')
    @endif
</div>
