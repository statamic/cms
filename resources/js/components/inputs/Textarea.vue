<template>
    <div>
        <textarea
            class="input-text"
            :value="value"
            :disabled="disabled"
            :placeholder="placeholder"
            @input="$emit('input', $event.target.value)"
            v-elastic
        />
        <div class="text-right text-xs" :class="limitIndicatorColor" v-if="limit">
            <span v-text="currentLength"></span>/<span v-text="limit"></span>
        </div>
    </div>

</template>

<script>
import LengthLimiter from '../LengthLimiter.vue'

export default {
    mixins: [LengthLimiter],
    props: {
        disabled: { default: false },
        placeholder: { required: false },
        value: { required: true },
    }
}
</script>
