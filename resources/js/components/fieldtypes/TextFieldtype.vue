<template>
    <text-input
        ref="input"
        :value="value"
        :classes="config.classes"
        :focus="shouldFocus"
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
        @input="inputUpdated"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype],

    computed: {
        shouldFocus() {
            if (this.config.focus === false) {
                return false;
            }

            return this.config.focus || this.name === 'title' || this.name === 'alt';
        }
    },

    methods: {
        inputUpdated(value) {
            if (! this.config.debounce) {
                return this.update(value)
            }

            this.updateDebounced(value)
        }
    }

}
</script>
