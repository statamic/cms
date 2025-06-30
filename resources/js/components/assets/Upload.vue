<template>
    <div class="my-4 flex items-center" :class="{ 'text-red-500': status == 'error' }">
        <div class="flex flex-1 items-center">
            <div class="mx-2 flex items-center">
                <svg-icon name="micro/warning" class="h-4 w-4 text-red-500" v-if="status === 'error'" />
                <loading-graphic v-else :inline="true" text="" />
            </div>

            <div class="filename">{{ basename }}</div>
        </div>

        <div v-if="status !== 'error'" class="mx-2 h-4 flex-1 rounded-sm bg-white">
            <div class="h-full rounded-sm bg-blue" :style="{ width: percent + '%' }" />
        </div>

        <div class="ml-4 flex items-center gap-2 px-2" v-if="status === 'error'">
            {{ error }}
            <Dropdown v-if="errorStatus === 409">
                <template #trigger>
                    <Button size="xs" :text="`${__('Fix')}...`" />
                </template>
                <DropdownMenu>
                    <DropdownItem @click="retryAndOverwrite" :text="__('messages.uploader_overwrite_existing')" />
                    <DropdownItem @click="openNewFilenameModal" :text="`${__('messages.uploader_choose_new_filename')}...`" />
                    <DropdownItem @click="retryWithTimestamp" :text="__('messages.uploader_append_timestamp')" />
                    <DropdownItem @click="selectExisting" v-if="allowSelectingExisting" :text="__('messages.uploader_discard_use_existing')" />
                </DropdownMenu>
            </Dropdown>
            <Button size="xs" @click="clear" :text="__('Discard')" />
        </div>

        <confirmation-modal
            v-if="showNewFilenameModal"
            :title="__('New Filename')"
            @cancel="showNewFilenameModal = false"
            @confirm="confirmNewFilename"
        >
            <Input autoselect v-model="newFilename" @keydown.enter="confirmNewFilename" />
        </confirmation-modal>
    </div>
</template>

<script>
import { Button, Dropdown, DropdownMenu, DropdownItem, Input } from '@statamic/ui';

export default {
    components: {
        Button,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        Input,
    },

    props: {
        extension: String,
        basename: String,
        percent: Number,
        error: String,
        errorStatus: Number,
        allowSelectingExisting: Boolean,
    },

    data() {
        return {
            showNewFilenameModal: false,
            newFilename: '',
        };
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

        retryAndOverwrite() {
            this.$emit('retry', { option: 'overwrite' });
        },

        retryWithTimestamp() {
            this.$emit('retry', { option: 'timestamp' });
        },

        openNewFilenameModal() {
            this.showNewFilenameModal = true;
            this.newFilename = this.basename.substring(0, this.basename.lastIndexOf('.'));
        },

        confirmNewFilename() {
            this.showNewFilenameModal = false;
            this.retryWithNewFilename();
        },

        retryWithNewFilename() {
            this.$emit('retry', { option: 'rename', filename: this.newFilename });
        },

        selectExisting() {
            this.$emit('existing-selected');
        },
    },
};
</script>
