<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype';
import { Alert } from '@/components/ui';
import markdown from '@/util/markdown';
import { computed } from 'vue';

const props = defineProps(Fieldtype.props);

const variant = computed(() => {
    return {
        notice: 'default',
        tip: 'tip',
        warning: 'warning',
        important: 'error',
        success: 'success',
    }[props.config.state] ?? 'default';
});

const html = computed(() => markdown(__(props.config.content), { openLinksInNewTabs: true }));
</script>

<template>
    <Alert :variant="variant" :icon="config.icon">
        <div
            class="st-text-trim-start [&_a]:font-medium [&_a]:underline [&_ol]:list-decimal [&_ol]:ps-5 [&_ul]:list-disc [&_ul]:ps-5"
            v-html="html"
        />
    </Alert>
</template>
