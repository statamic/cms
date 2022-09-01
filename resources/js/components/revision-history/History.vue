<template>

    <div class="bg-white h-full flex flex-col">

        <div class="bg-grey-20 px-2 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
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

            <div v-if="!loading && revisions.length === 0" class="p-2 text-grey text-sm">
                {{ __('No revisions') }}
            </div>

            <div
                v-for="group in revisions"
                :key="group.day"
            >
                <h6 class="revision-date" v-text="$moment.unix(group.day).format('LL')" />
                <div class="revision-list">
                    <revision
                        v-for="revision in group.revisions"
                        :key="revision.date"
                        :revision="revision"
                        :restore-url="restoreUrl"
                        :reference="reference"
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
