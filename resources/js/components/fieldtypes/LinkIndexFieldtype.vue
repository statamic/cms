<script setup>
import IndexFieldtype from '@/components/fieldtypes/index-fieldtype.js';
import { Icon } from '@ui';

const props = defineProps(IndexFieldtype.props);
</script>

<template>
    <a v-if="value" :key="value.url" :href="value.url" target="_blank" rel="noopener noreferrer" class="flex items-center space-x-2 text-ellipsis">
        <Icon :name="value.icon" class="size-3 flex-shrink-0" />
        <span v-text="value.url" />
    </a>
</template>
