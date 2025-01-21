<template>

    <div class="bg-white dark:bg-dark-800 h-full flex flex-col">

        <div class="bg-gray-200 dark:bg-dark-600 px-4 py-2 border-b border-gray-300 dark:border-dark-900 text-lg font-medium flex items-center justify-between">
            {{ __('Revision History') }}
            <button
                type="button"
                class="btn-close"
                @click="close"
                v-html="'&times'" />
        </div>

        <div class="flex-1 overflow-auto">

            <div class="flex h-full items-center justify-center loading" v-if="loading">
                <loading-graphic />
            </div>

            <div v-if="!loading && revisions.length === 0" class="p-4 text-gray dark:text-dark-150 text-sm">
                {{ __('No revisions') }}
            </div>

            <div
                v-for="group in revisions"
                :key="group.day"
            >
                <h6 class="revision-date" v-text="
                    $moment.unix(group.day).isBefore($moment().startOf('day'))
                        ? $moment.unix(group.day).toDate().toLocaleDateString($config.get('locale').replace('_', '-'), { month: 'long', day: 'numeric', year: 'numeric' })
                        : __('Today')" />
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
        }
    },

    mounted() {
        this.$axios.get(this.indexUrl).then(response => {
            this.loading = false;
            this.revisions = response.data.reverse();
        });
        this.escBinding = this.$keys.bindGlobal(['esc'], e => {
            this.close();
        });
    },

    beforeDestroy() {
        this.escBinding.destroy();
    },

    methods: {

        close() {
            this.$emit('closed');
        }

    }

}
</script>
