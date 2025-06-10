<template>
    <Combobox
        class="w-full"
        :clearable="config.clearable"
        :options="options"
        :disabled="config.disabled || isReadOnly"
        :multiple="true"
        :placeholder="__(config.placeholder)"
        :taggable="true"
        :model-value="value"
        @update:modelValue="update"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import HasInputOptions from './HasInputOptions.js';
import { Combobox } from '@statamic/ui';

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
