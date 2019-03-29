<template>

    <div class="bg-white h-full flex flex-col">

        <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
            {{ __('Revision History') }}
            <button
                type="button"
                class="ml-2 p-1 text-xl text-grey-60 hover:text-grey-80"
                @click="close"
                v-html="'&times'" />
        </div>

        <div class="flex-1 overflow-auto">

            <div class="flex h-full items-center justify-center loading" v-if="loading">
                <loading-graphic />
            </div>

            <div v-if="!loading && revisions.length === 0" class="px-3">
                {{ __('No revisions') }}
            </div>

            <div
                v-for="group in revisions"
                :key="group.day"
            >
                <h6 class="revision-date" v-text="$moment.unix(group.day).format('MMMM D, Y')" />
                <div class="revision-list">
                    <revision
                        v-for="revision in group.revisions"
                        :key="revision.date"
                        :revision="revision"
                        :restore-url="restoreUrl"
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
    },

    data() {
        return {
            revisions: [],
            loading: true,
        }
    },

    mounted() {
        this.$axios.get(this.indexUrl).then(response => {
            this.loading = false;
            this.revisions = response.data.reverse();
        });
    },

    methods: {

        close() {
            this.$emit('closed');
        }

    }

}
</script>
