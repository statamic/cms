<div class="items-center gap-2 hidden md:flex" data-global-header-breadcrumbs v-cloak>
    @foreach($breadcrumbs as $breadcrumb)
        <div class="items-center gap-2 md:flex entry-animate-in entry-animate-in--quick">
            <span class="text-white/30">/</span>
            <ui-button href="{{ $breadcrumb->url() }}" text="{{ __($breadcrumb->text()) }}" size="sm" variant="ghost" class="dark:text-white/85! hover:text-white! px-2! mr-1.75"></ui-button>
            @if($breadcrumb->hasLinks() || $breadcrumb->createUrl())
                <x-statamic::breadcrumbs.dropdown :$breadcrumb />
            @endif
        </div>
    @endforeach
</div>
