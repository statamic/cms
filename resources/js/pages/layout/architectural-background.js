import { nextTick, onMounted, onUnmounted } from 'vue';

const className = 'bg-architectural-lines';

function target() {
    return document.querySelector('#content-card .content-card') ?? document.getElementById('content-card');
}

function add() {
    nextTick(() => target()?.classList.add(className));
}

function remove() {
    nextTick(() => target()?.classList.remove(className));
}

export default function useArchitecturalBackground() {
    onMounted(() => add());
    onUnmounted(() => remove());
}

export function toggleArchitecturalBackground(enable) {
    enable ? add() : remove();
    onUnmounted(() => remove());
}
