<div class="items-center gap-2 hidden md:flex" data-global-header-breadcrumbs>
    @foreach($breadcrumbs as $breadcrumb)
        <div class="items-center gap-2 md:flex relative">
            <span class="text-white/30">/</span>
            <a
                class="
                    inline-flex items-center justify-center whitespace-nowrap shrink-0
                    font-medium antialiased cursor-pointer no-underline
                    disabled:text-gray-400 dark:disabled:text-gray-600 disabled:cursor-not-allowed
                    bg-transparent hover:bg-gray-400/10 text-gray-900 dark:text-gray-300 dark:hover:bg-white/15 dark:hover:text-gray-200 px-3 h-8
                    text-[0.8125rem] leading-tight gap-2 rounded-lg
                    dark:text-white/85! hover:text-white! px-2! mr-1.75
                "
                href="{{ $breadcrumb->url() }}"
            >
                {{ __($breadcrumb->text()) }}
            </a>

            @if($breadcrumb->hasLinks() || $breadcrumb->createUrl())
                <x-statamic::breadcrumbs.dropdown :$breadcrumb />
            @endif
        </div>
    @endforeach
</div>
