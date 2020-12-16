<div class="logo pt-7">
    @if (Statamic::pro() && config('statamic.cp.logo_url'))
        <img src="{{ config('statamic.cp.logo_url') }}" alt="{{ config('statamic.cp.cms_name') }}" class="white-label-logo">
    @else
        @svg('statamic-wordmark')
    @endif
</div>
