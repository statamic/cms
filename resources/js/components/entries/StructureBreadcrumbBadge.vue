<template>
    <Badge v-if="breadcrumb" size="sm" color="white" pill class="ps-0.75 pe-1.75">
        <span class="inline-flex items-center gap-0.5">
            <template v-for="(part, i) in parts" :key="i">
                <Icon name="chevron-left" class="size-2.5 opacity-60" />
                <span class="st-text-trim-cap py-1.25" v-text="part" />
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
        breadcrumb: String,
    },

    computed: {
        parts() {
            return this.breadcrumb?.split(/\s*[‹›]\s*/).filter(Boolean) ?? [];
        },
    },
};
</script>
