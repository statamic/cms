<template>
    <div class="my-4 flex items-center" :class="{ 'text-red-500': status == 'error' }">
        <div class="flex flex-1 items-center">
            <div class="mx-2 flex items-center">
                <svg-icon name="micro/warning" class="h-4 w-4 text-red-500" v-if="status === 'error'" />
                <loading-graphic v-else :inline="true" text="" />
            </div>

            <div class="filename">{{ basename }}</div>
        </div>

        <div v-if="status !== 'error'" class="mx-2 h-4 flex-1 rounded bg-white">
            <div class="h-full rounded bg-blue" :style="{ width: percent + '%' }" />
        </div>

        <div class="ml-4 flex items-center gap-2 px-2" v-if="status === 'error'">
            {{ error }}
            <dropdown-list v-if="errorStatus === 409">
                <template #trigger>
                    <button class="btn btn-xs ml-4" v-text="`${__('Fix')}...`" />
                </template>
                <dropdown-item @click="retryAndOverwrite" :text="__('messages.uploader_overwrite_existing')" />
                <dropdown-item
                    @click="openNewFilenameModal"
                    :text="`${__('messages.uploader_choose_new_filename')}...`"
                />
                <dropdown-item @click="retryWithTimestamp" :text="__('messages.uploader_append_timestamp')" />
                <dropdown-item
                    @click="selectExisting"
                    v-if="allowSelectingExisting"
                    :text="__('messages.uploader_discard_use_existing')"
                />
            </dropdown-list>
            <button class="btn btn-xs" @click="clear" v-text="__('Discard')" />
        </div>

        <confirmation-modal
            v-if="showNewFilenameModal"
            :title="__('New Filename')"
            @cancel="showNewFilenameModal = false"
            @confirm="confirmNewFilename"
        >
            <text-input autoselect v-model="newFilename" @keydown.enter="confirmNewFilename" />
        </confirmation-modal>
    </div>
</template>

<script>
export default {
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
