<command-palette>
    <div class="
            data-[focus-visible]:outline-focus hover flex cursor-text items-center gap-x-1.5 group h-8
            rounded-lg [button:has(>&)]:rounded-md bg-black/40 text-xs text-white/60 outline-none
            border-b border-b-white/20 inset-shadow-sm inset-shadow-black/20
            md:w-32 md:py-[calc(5/16*1rem)] md:px-2
            hover:bg-black/45 hover:text-white/70
        ">
        @cp_svg('icons/magnifying-glass', 'size-5 flex-none text-white/50 group-hover:text-white/70')
        <span class="sr-only leading-none md:not-sr-only st-text-trim-cap">{{ __('Search') }}</span>
        <kbd class="ml-auto hidden self-center rounded bg-white/5 px-[0.3125rem] py-[0.0625rem] text-[0.625rem]/4 font-medium text-white/60 group-hover:text-white/70 ring-1 ring-white/7.5 [word-spacing:-0.15em] ring-inset md:block">
            <kbd class="font-sans">⌘ </kbd><kbd class="font-sans">K</kbd>
        </kbd>
    </div>
</command-palette>
