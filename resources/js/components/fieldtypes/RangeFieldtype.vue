<template>
    <div
        class="
            flex items-center gap-2 rounded-lg bg-gray-50 p-2 @lg:px-4 @lg:py-3 dark:bg-gray-800
            with-contrast:border with-contrast:border-gray-500
            data-readonly:border data-readonly:border-dashed! data-readonly:border-gray-300 data-readonly:with-contrast:border-gray-100
            data-readonly:dark:border! data-readonly:dark:border-dashed! data-readonly:dark:border-gray-600!
            data-readonly:dark:bg-gray-900
        "
        :data-readonly="isReadOnly ? true : undefined"
    >
        <ui-subheading size="lg" v-if="config.prepend" :text="__(config.prepend)" class="whitespace-nowrap" />
        <input
            class="min-w-0 flex-1 w-full disabled:opacity-60"
            type="range"
            v-model="val"
            :disabled="config.disabled || isReadOnly"
            :id="fieldId"
            :max="config.max"
            :min="config.min"
            :name="name"
            :step="config.step"
            :width="config.width"
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
            if (this.isReadOnly) return;
            this.updateDebounced(value);
        },
    },
};
</script>
