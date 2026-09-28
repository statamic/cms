<script setup>
import { Card, Heading, Description, Button } from '@ui';
import Blank from '@/pages/layout/Blank.vue';
import useBodyClasses from '@/pages/layout/body-classes.js';

defineOptions({ layout: Blank });
useBodyClasses('bg-gray-50 dark:bg-gray-900');
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center">
        <Card class="text-center flex flex-col items-center space-y-2">
            <Heading size="2xl" :text="__('404')" />
            <Description :text="__('The page you are looking for could not be found.')" />
            <Button as="a" :href="cp_url('/')" size="xs" variant="filled" class="mt-4" :text="__('Return to Control Panel')" />
        </Card>
    </div>
</template>
