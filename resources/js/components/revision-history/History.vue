<template>
    <div class="m-2 flex h-full flex-col rounded-xl bg-white dark:bg-gray-800">
        <header
            class="flex items-center justify-between rounded-t-xl border-b border-gray-300 bg-gray-50 px-4 py-2 dark:border-gray-950 dark:bg-gray-900"
        >
            <Heading size="lg">{{ __('Revision History') }}</Heading>
            <Button icon="x" variant="ghost" class="-me-2" @click="close" />
        </header>

        <div class="flex-1 overflow-auto">
            <div class="loading flex h-full items-center justify-center" v-if="loading">
                <Icon name="loading" />
            </div>

            <Heading size="sm" class="p-3" v-if="!loading && revisions.length === 0">
                {{ __('No revisions') }}
            </Heading>

            <div v-for="group in revisions" :key="group.day">
                <Heading size="sm" class="p-3" v-text="formatRelativeDate(group.day)" />
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
import { Heading, Button, Icon } from '@statamic/ui';

export default {
    components: {
        Revision,
        Heading,
        Button,
        Icon,
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
