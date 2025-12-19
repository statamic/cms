<template>
    <div class="m-2 flex h-full flex-col rounded-xl bg-white dark:bg-gray-800">
        <div class="flex-1 overflow-auto">
            <div class="loading flex h-full items-center justify-center" v-if="loading">
                <Icon name="loading" />
            </div>

            <Heading size="sm" class="p-3" v-if="!loading && revisions.length === 0">
                {{ __('No revisions') }}
            </Heading>

            <div v-for="group in revisions" :key="group.day">
                <Heading size="sm" class="p-3 text-gray-600 dark:text-gray-300" v-text="formatRelativeDate(group.day)" />
                <div class="relative grid gap-3">
                    <div class="absolute inset-y-0 left-6 top-3 border-l-1 border-gray-400 dark:border-gray-600 border-dashed" />
                    <revision
                        v-for="(revision, index) in group.revisions"
                        :key="revision.date"
                        :revision="revision"
                        :restore-url="restoreUrl"
                        :reference="reference"
                        :can-restore-revisions="canRestoreRevisions"
                        :is-last="index === group.revisions.length - 1"
                        @working-copy-selected="close"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Revision from './Revision.vue';
import DateFormatter from '@/components/DateFormatter.js';
import { Heading, Button, Icon } from '@/components/ui';

export default {
	emits: ['closed'],

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
