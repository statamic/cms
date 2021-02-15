<template>

    <div class="select-input-container">

        <select
            v-if="display"
            class="select-input"
            :name="name"
            @change="change"
            :value="value"
            :disabled="isReadOnly"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        >

            <option
                v-if="placeholder"
                v-text="placeholder"
                value=""
                disabled
                :selected="value === null" />

            <option
                v-for="option in options"
                :key="option.value"
                v-text="option.label"
                :value="option.value"
                :selected="isOptionSelected(option)" />

        </select>

        <div class="select-input-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
            </svg>
        </div>

     </div>

</template>

<script>

export default {

    props: {
        name: {},
        disabled: { default: false },
        options: { default: []},
        placeholder: {
            required: false,
            default: () => __('Choose...')
        },
        value: {},
        isReadOnly: { type: Boolean },
        resetOnChange: { default: false }
    },

    data() {
        return {
            display: true,
        };
    },

    methods: {

        isOptionSelected(option) {
            return this.placeholder === false && this.value === undefined
                ? option.value == this.options[0].value
                : option.value == this.value;
        },

        change(event) {
            if (this.resetOnChange) {
                this.reset();
            }

            this.$emit('input', event.target.value)
        },

        reset() {
            this.display = false;
            this.$nextTick(() => this.display = true);
        }

    }

}
</script>
