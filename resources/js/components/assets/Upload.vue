<template>
    <div class="p-3 overflow-hidden dark:border-gray-700 dark:bg-gray-800 text-sm text-gray-600 dark:text-gray-400">
        <div class="flex flex-1 items-center gap-2 sm:gap-3">
            <div class="size-7 flex items-center justify-center">
                <ui-icon name="warning-diamond" class="size-5 text-red-600" v-tooltip="error" v-if="status === 'error'" />
                <Icon v-else name="loading" />
            </div>

            <div class="truncate">{{ basename }}</div>

            <div v-if="status !== 'error'" class="h-1.5 flex-1 rounded-lg bg-gray-100">
                <div class="h-full rounded-sm bg-blue-500" :style="{ width: percent + '%' }" />
            </div>
            <div class="flex-1" v-else />

            <div class="flex items-center gap-2" v-if="status === 'error'">
                <span v-if="errorStatus === 409" class="text-xs text-gray-500 dark:text-gray-300">
                    {{ __('messages.asset_conflict_pending') }}
                </span>
                <Button size="xs" @click="clear" :text="__('Discard')" />
            </div>
        </div>
    </div>
</template>

<script>
import { Button, Icon } from '@/components/ui';

export default {
    components: {
        Button,
        Icon,
    },

    props: {
        extension: String,
        basename: String,
        percent: Number,
        error: String,
        errorStatus: Number,
    },

    data() {
        return {};
    },

    computed: {
        status() {
            if (this.error) {
                return 'error';
            } else if (this.percent === 100) {
                return 'pending';
            } else {
                return 'uploading';
            }
        },
    },

    methods: {
        clear() {
            this.$emit('clear');
        },
    },
};
</script>
