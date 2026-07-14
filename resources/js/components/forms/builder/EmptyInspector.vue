<script setup>
    import { Icon } from '@ui';
</script>

<template>
<div class="@container relative pt-6 pb-40 max-[1000px]:pb-12 px-2.5 pe-4.5">
    <div class="space-y-6 pt-5">
        <div class="flex items-center gap-2">
            <div class="size-4">
                <Icon name="forms" class="size-4 text-gray-500 dark:text-gray-300" />
            </div>
            <span class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">
                <span class="truncate">{{ __('Edit this form') }}</span>
            </span>
        </div>
        <p class="text-zinc-600 dark:text-zinc-400">{{ __('statamic::messages.form_builder_empty_inspector') }}</p>
    </div>
</div>
</template>
