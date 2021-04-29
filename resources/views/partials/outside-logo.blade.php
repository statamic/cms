<div class="logo pt-7">
    @if ($customLogo)
        <img src="{{ $customLogo }}" alt="{{ config('statamic.cp.custom_cms_name') }}" class="white-label-logo">
    @else
        @cp_svg('statamic-wordmark')
    @endif
</div>
