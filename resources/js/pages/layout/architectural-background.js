import { onMounted, onUnmounted } from 'vue';

const className = 'bg-architectural-lines';
const id = 'content-card';

function add() {
    document.getElementById(id).classList.add(className);
}

function remove() {
    document.getElementById(id).classList.remove(className);
}

export default function useArchitecturalBackground() {
    onMounted(() => add());
    onUnmounted(() => remove());
}

export function toggleArchitecturalBackground(enable) {
    enable ? add() : remove();
    onUnmounted(() => remove());
}
