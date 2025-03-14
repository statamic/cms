<template>
    <div class="flex h-full flex-col bg-white dark:bg-dark-800">
        <div
            class="flex items-center justify-between border-b border-gray-300 bg-gray-200 px-4 py-2 text-lg font-medium dark:border-dark-900 dark:bg-dark-600"
        >
            {{ __('Revision History') }}
            <button type="button" class="btn-close" @click="close" v-html="'&times'" />
        </div>

        <div class="flex-1 overflow-auto">
            <div class="loading flex h-full items-center justify-center" v-if="loading">
                <loading-graphic />
            </div>

            <div v-if="!loading && revisions.length === 0" class="p-4 text-sm text-gray dark:text-dark-150">
                {{ __('No revisions') }}
            </div>

            <div v-for="group in revisions" :key="group.day">
                <h6 class="revision-date" v-text="formatRelativeDate(group.day)" />
                <div class="revision-list">
                    <revision
                        v-for="revision in group.revisions"
                        :key="revision.date"
                        :revision="revision"
                        :restore-url="restoreUrl"
                        :reference="reference"
                        :can-restore-revisions="canRestoreRevisions"
                        @working-copy-selected="close"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Revision from './Revision.vue';
import DateFormatter from '@statamic/components/DateFormatter.js';

export default {
    components: {
        Revision,
    },

    props: {
        indexUrl: String,
        restoreUrl: String,
        reference: String,
        canRestoreRevisions: Boolean,
    },

    data() {
        return {
            revisions: [],
            loading: true,
            escBinding: null,
        };
    },

    mounted() {
        this.$axios.get(this.indexUrl).then((response) => {
            this.loading = false;
            this.revisions = response.data.reverse();
        });
        this.escBinding = this.$keys.bindGlobal(['esc'], (e) => {
            this.close();
        });
    },

    beforeUnmount() {
        this.escBinding.destroy();
    },

    methods: {
        formatRelativeDate(value) {
            const isToday = new Date(value * 1000) < new Date().setUTCHours(0, 0, 0, 0);

            return !isToday
                ? __('Today')
                : DateFormatter.format(value * 1000, {
                      month: 'long',
                      day: 'numeric',
                      year: 'numeric',
                  });
        },

        close() {
            this.$emit('closed');
        },
    },
};
</script>
