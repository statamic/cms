<template>
    <div class="radio-fieldtype-wrapper" :class="{'inline-mode': config.inline}">
        <div
            v-for="(option, $index) in options"
            :key="$index"
            class="option"
            :class="{
                'selected': value === option.value,
                'disabled': isReadOnly
            }"
        >
            <label>
                <svg-icon
                    name="regular/radio-deselected"
                    class="radio-icon"
                    :aria-hidden="value == option.value"
                    @click="update($event.target.value)"
                    v-show="value != option.value"
                    v-cloak
                />
                <svg-icon
                    name="regular/radio-selected"
                    class="radio-icon"
                    :aria-hidden="value != option.value"
                    @click="update($event.target.value)"
                    v-show="value == option.value"
                    v-cloak
                />
                <input type="radio"
                    ref="radio"
                    :name="name"
                    @input.stop="update($event.target.value)"
                    :value="option.value"
                    :disabled="isReadOnly"
                    :checked="value == option.value"
                />
                {{ option.label || option.value }}
            </label>
        </div>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import HasInputOptions from './HasInputOptions.js'

export default {
    mixins: [Fieldtype, HasInputOptions],

    computed: {
        options() {
            return this.normalizeInputOptions(this.meta.options || this.config.options);
        },

        replicatorPreview() {
            if (! this.showFieldPreviews || ! this.config.replicator_preview) return;

            var option = _.findWhere(this.options, {value: this.value});
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
