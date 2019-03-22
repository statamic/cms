<template>
    <div class="flex items-center">
        <input
            ref="input"
            class="input-text"
            :name="name"
            :value="value"
            :type="type"
            :disabled="disabled"
            :readonly="isReadOnly"
            :placeholder="placeholder"
            @input="$emit('input', $event.target.value)"
        >
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
        isReadOnly: { type: Boolean, default: false },
        placeholder: { required: false },
        type: { default: "text" },
        value: { required: true },
        autofocus: { type: Boolean }
    },
    mounted() {
        if (this.autofocus) this.$refs.input.focus();
    }
}
</script>
