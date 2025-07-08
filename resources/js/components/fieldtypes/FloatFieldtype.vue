<template>
    <Input
        type="text"
        :tabindex="0"
        :name="name"
        :focus="focus"
        :model-value="value"
        :is-read-only="isReadOnly"
        :id="fieldId"
        @update:model-value="updateDebounced"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { Input } from '@statamic/ui';

export default {
    mixins: [Fieldtype],

    components: { Input },

    props: ['focus'],
};
</script>
