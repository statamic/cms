<template>
    <div class="flex items-center">
        <div class="input-group">
            <slot name="prepend" v-if="prepend">
                <div class="input-group-prepend">
                    {{ __(prepend) }}
                </div>
            </slot>
            <input
                ref="input"
                class="input-text"
                :class="classes"
                :id="id"
                :name="name"
                :value="modelValue"
                :type="type"
                :step="step"
                :disabled="disabled"
                :readonly="isReadOnly"
                :placeholder="__(placeholder)"
                :autocomplete="autocomplete"
                :autofocus="focus"
                :min="min"
                :dir="direction"
                @input.stop="$emit('updated:model-value', $event.target.value)"
                @keydown.stop="$emit('keydown', $event)"
                @focus.stop="$emit('focus')"
                @blur.stop="$emit('blur')"
            >
            <slot name="append" v-if="append">
                <div class="input-group-append">
                    {{ __(append) }}
                </div>
            </slot>
        </div>
        <div class="text-xs rtl:mr-2 ltr:ml-2" :class="limitIndicatorColor" v-if="limit">
            <span v-text="currentLength"></span>/<span v-text="limit"></span>
        </div>
    </div>
</template>

<script>
import LengthLimiter from '../LengthLimiter.vue'

export default {
    emits: ['updated:model-value', 'keydown', 'focus', 'blur'],
    mixins: [LengthLimiter],
    props: {
        name: {},
        disabled: { default: false },
        classes: { default: null },
        id: { default: null },
        isReadOnly: { type: Boolean, default: false },
        placeholder: { required: false },
        type: { default: "text" },
        step: {},
        modelValue: { required: true },
        prepend: { default: null },
        append: { default: null },
        focus: { type: Boolean },
        autocomplete: { default: null },
        autoselect: { type: Boolean },
        min: { type: Number, default: undefined },
        direction: { type: String }
    },
    mounted() {
        if (this.autoselect) {
            this.$refs.input.select();
        }
        if (this.focus) {
            this.$refs.input.focus();
        }
    }
}
</script>
