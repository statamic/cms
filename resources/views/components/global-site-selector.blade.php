<global-site-selector></global-site-selector>

<div
    :class="{'absolute inset-0 animate-out fade-out duration-1200 fill-mode-forwards':true}"
    class="
        antialiased bg-transparent border-none cursor-pointer pointer-events-none
        dark:hover:bg-white/15 dark:hover:text-gray-200 dark:text-gray-300
        flex focus-within:focus-outline h-8 hover:bg-gray-400/10
        items-center justify-between px-3 rounded-lg text-[0.8125rem] text-gray-900
    "
    data-ui-combobox-trigger
    tabindex="-1"
>
    <div class="flex-1 min-w-0">
        <button
            type="button"
            class="w-full text-start bg-transparent truncate flex items-center gap-2 cursor-pointer focus:outline-none"
            data-ui-combobox-selected-option
        >
            @cp_svg('icons/globe-arrow', 'size-4 shrink-0 text-white/85 dark:text-white dark:opacity-50 text-white/85 dark:text-white dark:opacity-50')
            <span>{{ Statamic\Facades\Site::selected()->name() }}</span>
        </button>
    </div>
    <div class="flex gap-1.5 items-center shrink-0 ms-1.5">
        @cp_svg('icons/chevron-down', 'size-4 shrink-0 text-gray-400 dark:text-white/40 text-gray-400 dark:text-white/40')
    </div>
</div>
