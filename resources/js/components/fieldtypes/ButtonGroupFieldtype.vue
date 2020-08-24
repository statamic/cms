<template>
    <div class="button-group-fieldtype-wrapper" :class="{'inline-mode': config.inline}">
        <div class="btn-group">
            <button class="btn px-2"
                v-for="(option, $index) in options"
                :key="$index"
                ref="button"
                :name="name"
                @click="update($event.target.value)"
                :value="option.value"
                :disabled="isReadOnly"
                :class="{'active': value === option.value}"
                v-text="option.label || option.value"
            />
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
            this.$refs.button[0].focus();
        }

    }
};
</script>
