<script setup>
import { Card, Heading, Description } from '@ui';
import Blank from '@/pages/layout/Blank.vue';
import useBodyClasses from '@/pages/layout/body-classes.js';

defineOptions({ layout: Blank })
defineProps(['status'])
useBodyClasses('bg-gray-50 dark:bg-gray-900');
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center">
        <Card class="text-center flex flex-col items-center space-y-2">
            <Heading size="2xl" :text="__('Whoops!')" />
            <Description :text="`${__('Something went wrong')} (${status})`" />
        </Card>
    </div>
</template>
