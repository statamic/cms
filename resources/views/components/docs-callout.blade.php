@php
    use function Statamic\trans as __;
@endphp

@if (config('statamic.cp.link_to_docs'))
    <div class="mt-12 flex justify-center text-center starting-style-transition starting-style-transition--siblings">
        <ui-command-palette-item
            :text="[__('Statamic Documentation'), '{{ $topic }}']"
            icon="book-next-page"
            url="{{ $url }}"
            open-new-tab
            track-recent
            v-slot="{ url, icon }"
        >
            <ui-badge
                text="{{ __('Learn about :topic', ['topic' => $topic]) }}"
                icon-append="external-link"
                :href="url"
                target="_blank"
                pill
            />
        </ui-command-palette-item>
    </div>
@endif
