<script setup>
import { Badge } from '@ui';

defineProps({
    handle: { type: String, required: true },
    value: { required: true },
    values: { required: true },
});
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <template v-for="item in value" :key="item.id">
            <Badge size="sm" :href="item.edit_url">
                <span v-text="item.title" />
            </Badge>
            <Badge v-if="item.submissions_url" size="sm" icon="eye" :href="item.submissions_url">
                <span v-text="__('View Submissions')" />
            </Badge>
        </template>
    </div>
</template>
