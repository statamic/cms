<script setup>
import HoverCard from './HoverCard.vue';
import Timezones from './Timezones.vue';

const props = defineProps({
    /** The date to display across timezones. Forwarded to `<Timezones>`. Accepts a `Date`, ISO string, or epoch number. */
    date: { type: [String, Date, Number], required: true },
});
</script>

<template>
    <HoverCard>
        <template #trigger>
            <span><slot /></span>
        </template>
        <Timezones :date />
    </HoverCard>
</template>
