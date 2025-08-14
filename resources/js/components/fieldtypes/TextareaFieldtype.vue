<template>
    <Textarea
        :focus="config.focus"
        :name="name"
        :id="fieldId"
        :read-only="isReadOnly"
        :limit="config.character_limit || null"
        :placeholder="__(config.placeholder)"
        :model-value="value"
        @blur="$emit('blur')"
        @focus="$emit('focus')"
        @update:model-value="updateDebounced"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { Textarea } from '@statamic/cms/ui';

export default {
    mixins: [Fieldtype],
    components: { Textarea },
};
</script>
