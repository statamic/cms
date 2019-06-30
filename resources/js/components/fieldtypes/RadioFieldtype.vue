<template>
    <div class="radio-fieldtype-wrapper" :class="{'inline-mode': config.inline}">
        <div
            v-for="(option, $index) in options"
            :key="$index"
            class="option"
        >
            <input type="radio"
                :name="name"
                @input="update($event.target.value)"
                :value="option.value"
                :id="name + $index"
                :checked="value === option.value"
            />
            <label :for="name + $index">{{ option.label || option.value }}</label>
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
        }
    },

    methods: {

        focus() {
            document.getElementById(`${this.name}-0`).focus();
        },

        getReplicatorPreviewText() {
            var option = _.findWhere(this.config.options, {value: this.value});
            return (option) ? option.text : this.value;
        },

    }
};
</script>
