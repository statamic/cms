<template>
    <div class="flex items-center">
        <div class="input-group">
            <div class="input-group-prepend" v-if="prepend" v-text="prepend" />
            <input
                ref="input"
                class="input-text"
                :class="classes"
                :id="id"
                :name="name"
                :value="value"
                :type="type"
                :step="step"
                :disabled="disabled"
                :readonly="isReadOnly"
                :placeholder="placeholder"
                :autofocus="focus"
                :min="min"
                @input="$emit('input', $event.target.value)"
                @keydown="$emit('keydown', $event)"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
            >
            <div class="input-group-append" v-if="append" v-text="append" />
        </div>
        <div class="text-xs ml-1" :class="limitIndicatorColor" v-if="limit">
            <span v-text="currentLength"></span>/<span v-text="limit"></span>
        </div>
    </div>
</template>

<script>
import LengthLimiter from '../LengthLimiter.vue'

export default {
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
        value: { required: true },
        prepend: { default: null },
        append: { default: null },
        focus: { type: Boolean },
        autoselect: { type: Boolean },
        min: { type: Number, default: undefined }
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
