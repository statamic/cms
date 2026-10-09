<template>
    <Badge v-if="parts.length" size="sm" color="white" pill class="ps-0.75 pe-1.75">
        <span class="inline-flex max-w-56 items-center gap-0.5 overflow-hidden">
            <template v-for="(part, i) in parts" :key="i">
                <Icon name="chevron-left" class="size-2.5 shrink-0" />
                <span class="st-text-trim-cap truncate py-1.25" v-text="part" />
            </template>
        </span>
    </Badge>
</template>

<script>
import { Badge, Icon } from '@/components/ui';

export default {
    components: {
        Badge,
        Icon,
    },

    props: {
        breadcrumb: {
            type: Array,
            default: null,
        },
    },

    computed: {
        parts() {
            return this.breadcrumb ?? [];
        },
    },
};
</script>
