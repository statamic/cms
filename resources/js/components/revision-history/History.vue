<template>
    <div class="flex h-full flex-col bg-white dark:bg-gray-800 m-2 rounded-xl">
        <header
            class="flex items-center justify-between border-b border-gray-300 dark:border-gray-950 bg-gray-50 dark:bg-gray-900 rounded-t-xl px-4 py-2"
        >
            <ui-heading size="lg">{{ __('Revision History') }}</ui-heading>
            <ui-button icon="x" variant="ghost" class="-me-2" @click="close" />
        </header>

        <div class="flex-1 overflow-auto">
            <div class="loading flex h-full items-center justify-center" v-if="loading">
                <loading-graphic />
            </div>

            <ui-heading size="sm" class="p-3" v-if="!loading && revisions.length === 0">
                {{ __('No revisions') }}
            </ui-heading>

            <div v-for="group in revisions" :key="group.day">
                <ui-heading size="sm" class="p-3" v-text="formatRelativeDate(group.day)" />
                <div class="divide-y divide-gray-200 dark:divide-gray-900">
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
