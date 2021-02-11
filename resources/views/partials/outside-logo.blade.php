<div class="logo pt-7">
    @if (Statamic::pro() && config('statamic.cp.custom_logo_url'))
        <img src="{{ config('statamic.cp.custom_logo_url.outside') ?? config('statamic.cp.custom_logo_url') }}" alt="{{ config('statamic.cp.custom_cms_name') }}" class="white-label-logo">
    @else
        @cp_svg('statamic-wordmark')
    @endif
</div>
