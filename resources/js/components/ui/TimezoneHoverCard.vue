<script setup>
import HoverCard from './HoverCard.vue';
import Timezones from './Timezones.vue';

const props = defineProps({
    /** The date to display across timezones. Forwarded to `<Timezones>`. Accepts a `Date`, ISO string, epoch number, or a `{ start, end }` range object. */
    date: { type: [String, Date, Number, Object], required: true },
    /** Extra `{ timezone, label }` rows to render above the defaults. Forwarded to `<Timezones>`. */
    additionalTimezones: { type: Array, default: () => [] },
});
</script>

<template>
    <HoverCard>
        <template #trigger>
            <slot />
        </template>
        <Timezones :date :additional-timezones="additionalTimezones" />
    </HoverCard>
</template>
