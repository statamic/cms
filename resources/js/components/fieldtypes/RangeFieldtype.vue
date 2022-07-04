<template>
    <div class="range-fieldtype-wrapper bg-grey-20 rounded py-1 px-2 border">
        <div class="flex items-center">
            <div v-if="config.prepend" v-text="config.prepend" class="mr-1" />
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
                class="flex-1 min-w-0"
            />
            <div class="rounded border px-1 py-sm mx-1 bg-grey-10">{{ val }}</div>
            <div v-if="config.append" v-text="config.append" />
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
        }
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
            if (typeof(this.value) === 'number') {
                return this.value;
            }

            if (typeof(this.config.default) === 'number') {
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
        }
    }

}
</script>
