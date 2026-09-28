import { onMounted, onUnmounted } from 'vue';

export default function useBodyClasses(bodyClasses) {
    bodyClasses = bodyClasses.split(' ');
    onMounted(() => document.body.classList.add(...bodyClasses));
    onUnmounted(() => document.body.classList.remove(...bodyClasses));
}
