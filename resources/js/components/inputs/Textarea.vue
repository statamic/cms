<template>
    <div>
        <textarea
            class="input-text"
            ref="textarea"
            :value="value"
            :id="id"
            :disabled="disabled"
            :readonly="isReadOnly"
            :placeholder="placeholder"
            :autofocus="focus"
            @input="$emit('input', $event.target.value)"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />
        <div class="text-right text-xs" :class="limitIndicatorColor" v-if="limit">
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
        value: { required: true },
        id: { default: null },
        focus: { type: Boolean, default: false }

    },
    mounted() {
        autosize(this.$refs.textarea);

        setTimeout(() => {
            this.updateSize();
        }, 1);
        this.$events.$on('tab-switched', this.updateSize);
    },
    
    beforeDestroy() {
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
