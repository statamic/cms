<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype';
import { computed } from 'vue';
import DOMPurify from 'dompurify';

const props = defineProps(Fieldtype.props);

const html = computed(() => props.config.sanitize ? DOMPurify.sanitize(props.config.html) : props.config.html);
</script>

<template>
    <div v-html="html" />
</template>
