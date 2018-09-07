<template>
    <div class="flex items-center">
        <input
            class="input-text"
            :name="name"
            :value="value"
            :type="type"
            :disabled="disabled"
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
        placeholder: { required: false },
        type: { default: "text" },
        value: { required: true }
    },
}
</script>
