<template>
    <div>
        <textarea
            class="input-text"
            ref="textarea"
            :value="modelValue"
            :id="id"
            :disabled="disabled"
            :readonly="isReadOnly"
            :placeholder="placeholder"
            :autofocus="focus"
            @input.stop="$emit('update:model-value', $event.target.value)"
            @focus.stop="$emit('focus')"
            @blur.stop="$emit('blur')"
        />
        <div class="rtl:text-left ltr:text-right text-xs -mb-3 @sm:-mb-5 @lg:-mb-5" :class="limitIndicatorColor" v-if="limit">
            <span v-text="currentLength"></span>/<span v-text="limit"></span>
        </div>
    </div>

</template>

<script>
import LengthLimiter from '../LengthLimiter.vue'
import autosize from 'autosize';

export default {
    mixins: [LengthLimiter],

    props: {
        disabled: { default: false },
        isReadOnly: { type: Boolean, default: false },
        placeholder: { required: false },
        modelValue: { required: true },
        id: { default: null },
        focus: { type: Boolean, default: false }

    },
    mounted() {
        autosize(this.$refs.textarea);

        if (this.focus) {
            this.$refs.textarea.focus();
        }

        setTimeout(() => {
            this.updateSize();
        }, 1);
        this.$events.$on('tab-switched', this.updateSize);
    },

    beforeUnmount() {
        autosize.destroy(this.$refs.textarea);
    },

    methods: {
        updateSize() {
            this.$nextTick(function() {
                autosize.update(this.$refs.textarea)
            })
        }
    }
}
</script>
