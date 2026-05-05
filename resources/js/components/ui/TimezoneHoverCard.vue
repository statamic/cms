<script setup>
import { useAttrs } from 'vue';
import HoverCard from './HoverCard.vue';
import Timezones from './Timezones.vue';

defineOptions({ inheritAttrs: false });

const props = defineProps({
    /** The date to display across timezones. Forwarded to `<Timezones>`. Accepts a `Date`, ISO string, or epoch number. */
    date: { type: [String, Date, Number], required: true },
    /** The preferred alignment against the trigger. May change when collisions occur. <br><br> Options: `start`, `center`, `end` */
    align: { type: String, default: 'center' },
    /** The delay in milliseconds before the hover card opens. */
    delay: { type: Number, default: 200 },
    /** The distance in pixels from the trigger. */
    offset: { type: Number, default: 25 },
    /** The preferred side of the trigger to render against when open. <br><br> Options: `top`, `bottom`, `left`, `right` */
    side: { type: String, default: 'left' },
});

// `HoverCardTrigger` uses `as-child`, so the default slot must render a
// single root element. Unknown attrs (e.g. `class`) flow to this wrapper.
const attrs = useAttrs();
</script>

<template>
    <HoverCard :side :align :delay :offset inset>
        <template #trigger>
            <span v-bind="attrs"><slot /></span>
        </template>
        <Timezones :date />
    </HoverCard>
</template>
