<template>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <template v-if="groupLabel">
            <span class="truncate">{{ groupLabel }}</span>
            <Icon name="chevron-right" class="size-3.5! shrink-0 text-gray-700 dark:text-white/70" aria-hidden="true" />
        </template>
        <span class="truncate">{{ __(item.title) }}</span>
    </span>
</template>

<script>
import { Icon } from '@/components/ui';

export default {
    components: { Icon },

    props: {
        item: { type: Object, required: true },
        groupLabel: { type: String, default: null },
    },
};
</script>
