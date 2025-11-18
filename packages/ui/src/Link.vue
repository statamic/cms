<script setup>
import { computed, shallowRef, onMounted } from 'vue';

const props = defineProps({
    href: { type: String },
    method: { type: String },
    data: { type: Object },
    replace: { type: Boolean },
    preserveScroll: { type: [Boolean, Function] },
    preserveState: { type: [Boolean, Function] },
    only: { type: Array },
    headers: { type: Object },
    queryStringArrayFormat: { type: String },
    target: { type: String },
});

const InertiaLink = shallowRef(null);
const loading = shallowRef(true);

onMounted(async () => {
    try {
        const inertia = await import('@inertiajs/vue3');
        InertiaLink.value = inertia.Link;
    } catch (e) {
        // Inertia not available, will use anchor tag
    } finally {
        loading.value = false;
    }
});

const useAnchor = computed(() => {
    return !InertiaLink.value ||
           props.target === '_blank' ||
           (props.href && (props.href.startsWith('http://') || props.href.startsWith('https://') || props.href.startsWith('//')));
});

const component = computed(() => useAnchor.value ? 'a' : InertiaLink.value);
const componentProps = computed(() => {
    if (useAnchor.value) {
        return { href: props.href, target: props.target };
    }
    return props;
});
</script>

<template>
    <component
        :is="component"
        v-bind="componentProps"
    >
        <slot />
    </component>
</template>
