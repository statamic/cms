<script setup>
import { computed } from 'vue';
import { Button, Icon } from '@ui';

const props = defineProps({
    container: {
        type: Object,
        default: null,
    },
    asset: {
        type: Object,
        default: null,
    },
    showContainers: {
        type: Boolean,
        default: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['containers', 'assets']);
const items = computed(() => {
    const items = [];

    if (props.showContainers) {
        items.push({
            label: __('All containers'),
            icon: 'home',
            action: 'containers',
        });
    }

    if (props.container) {
        items.push({
            label: props.container.title,
            icon: 'folder-open',
            action: 'assets',
        });
    }

    if (props.asset) {
        items.push({
            label: props.asset.title,
            title: props.asset.path,
        });
    }

    return items;
});
</script>

<template>
    <nav :aria-label="__('Breadcrumbs')" class="flex min-h-8 items-center overflow-x-auto">
        <div v-for="(item, index) in items" :key="index" class="flex shrink-0 items-center">
            <Icon v-if="index" name="chevron-right" class="size-4 text-gray-500" />
            <Button
                :text="item.label"
                :icon="item.icon"
                :title="item.title"
                :aria-current="index === items.length - 1 ? 'page' : undefined"
                variant="ghost"
                class="h-8! gap-2"
                :disabled="disabled"
                @click="item.action && emit(item.action)"
            />
        </div>
    </nav>
</template>
