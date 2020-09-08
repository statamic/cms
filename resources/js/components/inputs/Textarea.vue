<template>
    <div>
        <textarea
            class="input-text"
            :value="value"
            :id="id"
            :disabled="disabled"
            :readonly="isReadOnly"
            :placeholder="placeholder"
            :autofocus="focus"
            @input="$emit('input', $event.target.value)"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
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
        isReadOnly: { type: Boolean, default: false },
        placeholder: { required: false },
        value: { required: true },
        id: { default: null },
        focus: { type: Boolean, default: false }
    }
}
</script>
