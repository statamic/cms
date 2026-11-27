<template>
    <span
        v-if="moving"
        class="pointer-events-none absolute top-3 flex items-center gap-[3px]"
        :class="side === 'start' ? 'end-full me-2' : 'start-full ms-2'"
        aria-hidden="true"
    >
        <!-- In a tree, ← and → only show when they'd do something: ← when it can move out a level, → when it can nest under the page above. -->
        <!-- Arrows drawn in SF Mono's style (a straight shaft and a 45° head, all one weight), with ← and → shorter than ↑ and ↓ as they are
             in the font. As text they were in whichever monospace font the browser had (Menlo in Firefox, tiny and low) and sat off centre.
             Drawn at a whole 8px, so they land on the same pixels in every browser, in keys that are exactly square. -->
        <kbd
            v-for="key in keys"
            :key="key.name"
            :data-key="key.glyph"
            class="inline-flex size-4 items-center justify-center rounded border border-blue-300 bg-blue-50 text-blue-600 dark:border-blue-500 dark:bg-blue-950 dark:text-blue-300"
        >
            <svg viewBox="-7 0 74 74" class="size-2">
                <path :d="paths[key.name]" fill="none" stroke="currentColor" stroke-width="10.75" />
            </svg>
        </kbd>
    </span>
</template>

<script>
// Each arrow as a line, centred on 30, 37 in a 74-unit square (with the stroke, the arrows fill it, as the font's do)
const paths = {
    up: 'M30 73.5V10M4 34.3 30 8.3 56 34.3',
    down: 'M30 0.5V64M4 39.7 30 65.7 56 39.7',
    right: 'M1.35 37H49M24.9 11 50.9 37 24.9 63',
    left: 'M58.65 37H11M35.1 11 9.1 37 35.1 63',
};

export default {
    props: {
        /** Show ← → as well (trees), when `can` says they'd do something */
        horizontal: { type: Boolean, default: false },
        /** Which of ← → would do something right now: `in` nests under the item above, `out` moves out a level */
        can: { type: Object, default: () => ({ in: false, out: false }) },
        moving: {
            type: Boolean,
            default: false,
        },
        /** `start` = outside the left (handle) side; `end` = outside the right (menu) side */
        side: {
            type: String,
            default: 'end',
            validator: (value) => ['start', 'end'].includes(value),
        },
    },

    setup() {
        return { paths };
    },

    computed: {
        keys() {
            return [
                { name: 'left', glyph: '←', show: this.horizontal && this.can.out },
                { name: 'up', glyph: '↑', show: true },
                { name: 'down', glyph: '↓', show: true },
                { name: 'right', glyph: '→', show: this.horizontal && this.can.in },
            ].filter((key) => key.show);
        },
    },
};
</script>
