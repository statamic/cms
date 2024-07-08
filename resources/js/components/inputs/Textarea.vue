<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import autosize from 'autosize';

import useLengthLimiter from '../../composables/useLengthLimiter';
import useEventBus from '../../composables/useEventBus';

const $events = useEventBus()

const props = defineProps({
    disabled: { default: false },
    isReadOnly: { type: Boolean, default: false },
    placeholder: { type: String, required: false },
    id: { default: null },
    focus: { type: Boolean, default: false },
    limit: { type: Number, required: false }
});

defineEmits(['focus', 'blur'])

const modelValue = defineModel<string>();

const textarea = ref<HTMLTextAreaElement>(null)

const { currentLength, limitIndicatorColor } = useLengthLimiter({
    value: modelValue,
    limit: props.limit,
});

onMounted(() => {
    autosize(textarea.value);

    if (props.focus) {
        textarea.value.focus();
    }

    setTimeout(() => {
        updateSize();
    }, 1);

    $events.$on('tab-switched', updateSize);
})

onBeforeUnmount(() => {
    autosize.destroy(textarea.value);
})

function updateSize() {
    nextTick(() => {
        autosize.update(textarea.value);
    })
}
</script>

<template>
    <div>
        <textarea
            class="input-text"
            ref="textarea"
            :id="id"
            :disabled="disabled"
            :readonly="isReadOnly"
            :placeholder="placeholder"
            :autofocus="focus"
            v-model="modelValue"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />
        <div
            class="rtl:text-left ltr:text-right text-xs -mb-3 @sm:-mb-5 @lg:-mb-5" :class="limitIndicatorColor"
            v-if="limit"
        >
            <span v-text="currentLength"></span>/<span v-text="limit"></span>
        </div>
    </div>
</template>
