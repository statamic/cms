<script setup>
import { onMounted, ref } from 'vue';

import useLengthLimiter from '../../composables/useLengthLimiter';

const $emit = defineEmits(['keydown', 'focus', 'blur'])

const props = defineProps({
    name: {},
    disabled: { default: false },
    classes: { default: null },
    id: { default: null },
    isReadOnly: { type: Boolean, default: false },
    placeholder: { required: false },
    type: { default: 'text' },
    step: {},
    prepend: { default: null },
    append: { default: null },
    focus: { type: Boolean },
    autocomplete: { default: null },
    autoselect: { type: Boolean },
    min: { type: Number, default: undefined },
    direction: { type: String },
    limit: { type: Number, required: false }
});

const modelValue = defineModel()
const input = ref(null)

onMounted(() => {
    if (props.autoselect) {
        input.value.select();
    }

    if (props.focus) {
        input.value.focus();
    }
});

const { currentLength, limitIndicatorColor } = useLengthLimiter({
    value: modelValue,
    limit: props.limit,
});
</script>

<template>
    <div class="flex items-center">
        <div class="input-group">
            <slot name="prepend" v-if="prepend">
                <div class="input-group-prepend">
                    {{ __(prepend) }}
                </div>
            </slot>
            <input
                :value="modelValue"
                @input="modelValue = $event.target.value"
                ref="input"
                class="input-text"
                :class="classes"
                :id="id"
                :name="name"
                :type="type"
                :step="step"
                :disabled="disabled"
                :readonly="isReadOnly"
                :placeholder="__(placeholder)"
                :autocomplete="autocomplete"
                :autofocus="focus"
                :min="min"
                :dir="direction"
                @keydown="$emit('keydown', $event)"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
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
