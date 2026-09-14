import { nextTick, onMounted, onUnmounted } from 'vue';

const className = 'bg-architectural-lines';
const id = 'content-card';

function add() {
    nextTick(() => document.getElementById(id).classList.add(className));
}

function remove() {
    nextTick(() => document.getElementById(id).classList.remove(className));
}

export default function useArchitecturalBackground() {
    onMounted(() => add());
    onUnmounted(() => remove());
}

export function toggleArchitecturalBackground(enable) {
    enable ? add() : remove();
    onUnmounted(() => remove());
}
