<template>
    <Input
        type="number"
        :tabindex="0"
        :name="name"
        :focus="focus"
        :model-value="value"
        :read-only="isReadOnly"
        :disabled="config.disabled"
        :id="fieldId"
        :prepend="__(config.prepend)"
        :append="__(config.append)"
        :min="config.min"
        :max="config.max"
        :step="config.step"
        @update:model-value="updateDebounced"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { Input } from '@/components/ui';

export default {
    mixins: [Fieldtype],

    components: { Input },

    props: ['focus'],
};
</script>
