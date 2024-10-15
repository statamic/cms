<template>

    <div class="flex items-center my-4" :class="{'text-red-500': status == 'error'}">

        <div class="mx-2 flex items-center">
            <svg-icon name="micro/warning" class="text-red-500 h-4 w-4" v-if="status === 'error'" />
            <loading-graphic v-else :inline="true" text="" />
        </div>

        <div class="filename">{{ basename }}</div>

        <div
            v-if="status !== 'error'"
            class="bg-white flex-1 h-4 mx-2 rounded"
        >
            <div class="bg-blue h-full rounded"
                :style="{ width: percent+'%' }" />
        </div>

        <div class="ml-4 px-2 flex items-center gap-2" v-if="status === 'error'">
            {{ error }}
            <dropdown-list>
                <template #trigger>
                    <button class="ml-4 btn btn-xs">Retry...</button>
                </template>
                <dropdown-item @click="retryAndOverwrite">Overwrite existing file</dropdown-item>
                <dropdown-item @click="openNewFilenameModal">Choose new filename</dropdown-item>
                <dropdown-item @click="retryWithTimestamp">Append timestamp</dropdown-item>
            </dropdown-list>
            <button class="btn btn-xs" @click="clear">Cancel</button>
        </div>


        <confirmation-modal
            v-if="showNewFilenameModal"
            :title="__('New Filename')"
            @cancel="showNewFilenameModal = false"
            @confirm="confirmNewFilename"
        >
            <text-input :focus="true" v-model="newFilename" @keydown.enter="confirmNewFilename" />
        </confirmation-modal>

    </div>

</template>


<script>
export default {

    props: ['extension', 'basename', 'percent', 'error'],

    data() {
        return {
            showNewFilenameModal: false,
            newFilename: '',
        }
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
        }

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
        },

        confirmNewFilename() {
            this.showNewFilenameModal = false;
            this.retryWithNewFilename();
        },

        retryWithNewFilename() {
            this.$emit('retry', { option: 'rename', filename: this.newFilename})
        }

    }

}
</script>
