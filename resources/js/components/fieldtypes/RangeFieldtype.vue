<template>
    <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800 rounded-lg p-2 @lg:px-4 @lg:py-3">
        <ui-subheading size="lg" v-if="config.prepend" :text="__(config.prepend)" class="whitespace-nowrap" />
        <input
            type="range"
            v-model="val"
            :name="name"
            :min="config.min"
            :max="config.max"
            :step="config.step"
            :width="config.width"
            :readonly="isReadOnly"
            :disabled="isReadOnly"
            :id="fieldId"
            class="min-w-0 flex-1 w-full"
        />
        <ui-badge :text="val"  />
        <ui-subheading size="lg" v-if="config.append" :text="__(config.append)" />
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype],

    data() {
        return {
            val: this.getValue(),
        };
    },

    methods: {
        getDefault() {
            // Spec: https://html.spec.whatwg.org/multipage/input.html#range-state-(type=range)
            if (this.config.max < this.config.min) return this.config.min;

            var val = this.config.min + (this.config.max - this.config.min) / 2;

            // make sure on a valid step
            if (this.config.step) {
                val = Math.floor(val / this.config.step) * this.config.step;
            }

            return val;
        },

        getValue() {
            if (typeof this.value === 'number') {
                return this.value;
            }

            if (typeof this.config.default === 'number') {
                return this.config.default;
            }

            return this.getDefault();
        },
    },

    watch: {
        value(value) {
            this.val = value;
        },
        val(value) {
            this.updateDebounced(value);
        },
    },
};
</script>
