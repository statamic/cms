<template>
    <text-input
        ref="input"
        :model-value="value"
        :classes="config.classes"
        :focus="config.focus || name === 'title' || name === 'alt'"
        :autocomplete="config.autocomplete"
        :autoselect="config.autoselect"
        :type="config.input_type"
        :isReadOnly="isReadOnly"
        :prepend="__(config.prepend)"
        :append="__(config.append)"
        :limit="config.character_limit"
        :placeholder="__(config.placeholder)"
        :name="name"
        :id="fieldId"
        :direction="config.direction"
        @update:model-value="inputUpdated"
        @focus="$emit('focus')"
        @blur="$emit('blur-sm')"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype],

    methods: {
        inputUpdated(value) {
            if (!this.config.debounce) {
                return this.update(value);
            }

            this.updateDebounced(value);
        },
    },
};
</script>
