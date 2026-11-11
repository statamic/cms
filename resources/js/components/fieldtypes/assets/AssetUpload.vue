<template>
    <div
        class="relative size-10 shrink-0"
        v-tooltip="error ? `${basename}: ${error}` : basename"
        :role="error ? 'group' : 'progressbar'"
        :aria-label="error ? `${basename}: ${error}` : basename"
        :aria-valuenow="error ? undefined : progress"
        :aria-valuemin="error ? undefined : 0"
        :aria-valuemax="error ? undefined : 100"
    >
        <template v-if="!error">
            <Button icon-only read-only size="base" :aria-label="basename">
                <Icon name="loading" class="opacity-70! motion-reduce:animate-none" />
            </Button>

            <svg
                class="pointer-events-none absolute inset-0 size-full fill-none stroke-blue-500 dark:stroke-blue-400"
                viewBox="0 0 40 40"
                aria-hidden="true"
            >
                <path
                    d="M20 1 H32 A7 7 0 0 1 39 8 V32 A7 7 0 0 1 32 39 H8 A7 7 0 0 1 1 32 V8 A7 7 0 0 1 8 1 H20"
                    pathLength="100"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-dasharray="100"
                    :stroke-dashoffset="100 - progress"
                />
            </svg>
        </template>

        <Dropdown v-else>
            <template #trigger>
                <Button icon-only size="base" :aria-label="basename">
                    <Icon name="warning-diamond" class="text-red-600!" />
                    <span
                        class="absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden"
                        aria-hidden="true"
                    />
                </Button>
            </template>
            <DropdownMenu>
                <template v-if="errorStatus === 409">
                    <DropdownItem
                        :text="__('messages.uploader_overwrite_existing')"
                        @select="retryAndOverwrite"
                    />
                    <DropdownItem
                        :text="`${__('messages.uploader_choose_new_filename')}...`"
                        @select="openNewFilenameModal"
                    />
                    <DropdownItem
                        :text="__('messages.uploader_append_timestamp')"
                        @select="retryWithTimestamp"
                    />
                    <DropdownItem
                        v-if="allowSelectingExisting"
                        :text="__('messages.uploader_discard_use_existing')"
                        @select="selectExisting"
                    />
                </template>
                <DropdownItem :text="__('Discard')" icon="x" @select="clear" />
            </DropdownMenu>
        </Dropdown>

        <confirmation-modal
            :open="showNewFilenameModal"
            :title="__('New Filename')"
            @cancel="showNewFilenameModal = false"
            @confirm="confirmNewFilename"
        >
            <Input autoselect v-model="newFilename" @keydown.enter="confirmNewFilename" />
        </confirmation-modal>
    </div>
</template>

<script>
import Upload from '@/components/assets/Upload.vue';

export default {
    mixins: [Upload],

    computed: {
        progress() {
            return Math.min(100, Math.max(0, this.percent || 0));
        },
    },
};
</script>
