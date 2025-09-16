@php
    use function Statamic\trans as __;
@endphp

<header class="h-14 bg-global-header-bg dark:bg-dark-global-header-bg flex justify-between space-x-2 items-center text-white px-4 fixed overflow-x-auto top-0 inset-x-0 z-[3]">
    <a class="c-skip-link z-(--z-index-header) px-4 py-2 bg-blue-800 text-sm top-2.5 left-2.25 fixed opacity-0 -translate-y-24 focus:translate-y-0 focus:opacity-100 rounded-md" href="#main">
        {{ __('Skip to sidebar') }}
    </a>
    <a class="c-skip-link z-(--z-index-header) px-4 py-2 bg-blue-800 text-sm top-2.5 left-2.25 fixed opacity-0 -translate-y-24 focus:translate-y-0 focus:opacity-100 rounded-md" href="#main-content">
        {{ __('Skip to content') }}
    </a>
    <div class="dark flex items-center gap-2 text-[0.8125rem] text-white/85 w-full">
         {{-- Logo --}}
        @if ($customDarkLogo)
            <button class="flex items-center group cursor-pointer text-white/85 hover:text-white" type="button" @click="toggleNav" aria-label="{{ __('Toggle Nav') }}">
                <div class="p-1 size-7 inset-0 flex items-center justify-center">
                    @cp_svg('icons/burger-menu', 'size-5')
                </div>
            </button>
            <img src="{{ $customDarkLogo }}" alt="{{ config('statamic.cp.custom_cms_name') }}" class="max-w-[260px] max-h-9">
        @else
        <div class="flex items-center gap-2 relative">
            <button class="flex items-center group rounded-full cursor-pointer" type="button" @click="toggleNav" aria-label="{{ __('Toggle Nav') }}" style="--focus-outline-offset: 0.2rem;">
                <div class="opacity-0 group-hover:opacity-100 p-1 size-7 transition-opacity duration-150 absolute inset-0 flex items-center justify-center">
                    @cp_svg('icons/burger-menu', 'size-5')
                </div>
                @cp_svg('statamic-mark-lime', 'size-7 group-hover:opacity-0 transition-opacity duration-150')
            </button>
            <a href="{{ route('statamic.cp.index') }}" class="hidden sm:block text-white/85 rounded-xs whitespace-nowrap" style="--focus-outline-offset: var(--outline-offset-button);">
                {{ $customLogoText ?? config('app.name') }}
            </a>
            @if (Statamic::pro())
                <x-statamic::pro-badge />
            @endif
        </div>
        @endif

        <x-statamic::breadcrumbs :$breadcrumbs />
    </div>

    <div class="dark flex-1 flex gap-1 md:gap-3 items-center justify-end shrink-0">
        @if (Statamic\Facades\Site::authorized()->count() > 1)
            <x-statamic::global-site-selector />
        @endif
        <div class="flex items-center">
            <x-statamic::command-palette />
        </div>
        <x-statamic::view-site-button />
        <x-statamic::user-dropdown />
    </div>
</header>
