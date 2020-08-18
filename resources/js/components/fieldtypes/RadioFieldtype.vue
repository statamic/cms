<template>
    <div class="radio-fieldtype-wrapper" :class="{'inline-mode': config.inline}">
        <div
            v-for="(option, $index) in options"
            :key="$index"
            class="option"
        >
            <label>
                <input type="radio"
                    ref="radio"
                    :name="name"
                    @input="update($event.target.value)"
                    :value="option.value"
                    :disabled="isReadOnly"
                    :checked="value === option.value"
                />
                {{ option.label || option.value }}
            </label>
        </div>
    </div>
</template>

<script>
import HasInputOptions from './HasInputOptions.js'

export default {
    mixins: [Fieldtype, HasInputOptions],

    computed: {
        options() {
            return this.normalizeInputOptions(this.config.options);
        },

        replicatorPreview() {
            var option = _.findWhere(this.config.options, {value: this.value});
            return (option) ? option.label : this.value;
        },
    },

    methods: {

        focus() {
            this.$refs.radio[0].focus();
        }

    }
};
</script>
