<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    initialMode: {
        type: String,
        default: 'code',
    },
    hasError: {
        default: false,
    },
});

const wrapper = ref(null);
const busy = ref(false);
const mode = ref(props.initialMode);

onMounted(() => {
    if (props.hasError) {
        wrapper.value.parentElement.parentElement.classList.add('animation-shake');
    }
});

function toggleMode() {
    mode.value = mode.value === 'code' ? 'recovery_code' : 'code';
}

function setBusy(state = null) {
    busy.value = state ?? true;
}
</script>

<template>
    <div ref="wrapper"><slot v-bind="{ busy, setBusy, mode, toggleMode, hasError }"></slot></div>
</template>
