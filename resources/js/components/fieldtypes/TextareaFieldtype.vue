<template>
    <Textarea
        :focus="config.focus"
        :name="name"
        :id="fieldId"
        :read-only="isReadOnly"
        :limit="config.character_limit || null"
        :placeholder="__(config.placeholder)"
        :model-value="value"
        :dir="contentDirection"
        :rows="config.rows || 3"
        @blur="$emit('blur')"
        @focus="$emit('focus')"
        @update:model-value="updateDebounced"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { Textarea } from '@/components/ui';
import { useContentDirection } from '@/composables/content-direction';

export default {
    mixins: [Fieldtype],
    components: { Textarea },

    setup() {
        const { direction: contentDirection } = useContentDirection();

        return { contentDirection };
    },
};
</script>
