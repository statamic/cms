<template>
    <Combobox
        class="w-full"
        :clearable="config.clearable"
        :disabled="config.disabled"
        :model-value="value"
        :multiple="true"
        :options="options"
        :placeholder="__(config.placeholder)"
        :read-only="isReadOnly"
        :taggable="true"
        @update:modelValue="update"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import HasInputOptions from './HasInputOptions.js';
import { Combobox } from '@/components/ui';

export default {
    mixins: [Fieldtype, HasInputOptions],

    components: { Combobox },

    computed: {
        options() {
            return this.normalizeInputOptions(this.config.options || []);
        },
    },
};
</script>
