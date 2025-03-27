<template>
    <Input
        ref="input"
        type="number"
        :name="name"
        :focus="config.focus"
        :model-value="value"
        :is-read-only="isReadOnly"
        :id="fieldId"
        :min="config.min"
        :prepend="__(config.prepend)"
        :append="__(config.append)"
        :placeholder="__(config.placeholder)"
        @update:model-value="updateDebounced"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import Input from '@statamic/components/ui/Input/Index.vue';

export default {
    mixins: [Fieldtype],
    components: { Input },
    props: {
        prepend: { default: null },
        append: { default: null },
    },
};
</script>
