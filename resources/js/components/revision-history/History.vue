<template>

    <div class="bg-white h-full flex flex-col">

        <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
            {{ __('Revision History') }}
            <button
                type="button"
                class="ml-2 p-1 text-xl text-grey-60"
                @click="close"
                v-html="'&times'" />
        </div>

        <div class="flex-1 overflow-auto p-3">

            <div class="flex h-full items-center justify-center loading" v-if="loading">
                <loading-graphic />
            </div>

            <div v-if="!loading && revisions.length === 0" class="">
                {{ __('No revisions') }}
            </div>

            <revision
                v-for="revision in revisions"
                :key="revision.date"
                :revision="revision"
                :restore-url="restoreUrl"
                class="text-sm mb-3 pb-3 border-b flex items-center justify-between"
            />

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
