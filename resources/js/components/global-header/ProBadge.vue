<script setup>
import { Badge, Tooltip } from '@ui';
import { computed } from 'vue';
import useStatamicPageProps from '@/composables/page-props.js';

const { licensing } = useStatamicPageProps();

const problemBadgeColor = computed(() => {
    if (licensing.requestFailed) {
        return 'yellow';
    } else if (licensing.isOnPublicDomain) {
        return 'red';
    }
    return 'green';
});
</script>

<template>
    <Badge v-if="licensing.valid" :text="__('Pro')" variant="flat" size="sm" class="bg-white/15!" />

    <Tooltip
        v-else
        :text="licensing.requestFailureMessage"
    >
        <Badge
            variant="flat"
            :color="problemBadgeColor"
            class="max-[500px]:hidden"
        >
            {{ __('Pro') }} – {{ licensing.isOnPublicDomain ? __('statamic::messages.licensing_error_unlicensed') : __('Trial Mode') }}
        </Badge>
    </Tooltip>
</template>
