<template>
    <Select
        :options="sites"
        option-label="name"
        option-value="handle"
        :model-value="value"
        @update:model-value="$emit('input', $event)"
    />
</template>

<script>
import { Select } from '@statamic/ui';

export default {
    components: { Select },

    props: {
        sites: { type: Array, required: true },
        value: { type: String, required: true },
    },
};
</script>
