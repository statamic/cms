<template>
    <div class="range-fieldtype-wrapper rounded border bg-gray-200 px-4 py-2 dark:border-dark-900 dark:bg-dark-700">
        <div class="flex items-center">
            <div v-if="config.prepend" v-text="__(config.prepend)" class="ltr:mr-2 rtl:ml-2" />
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
                class="min-w-0 flex-1"
            />
            <div
                class="mx-2 rounded border bg-gray-100 px-2 py-1 dark:border-dark-900 dark:bg-dark-600 dark:shadow-inner-dark"
            >
                {{ val }}
            </div>
            <div v-if="config.append" v-text="__(config.append)" />
        </div>
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
