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

            <div v-if="revisions.length === 0" class="">
                {{ __('No revisions') }}
            </div>

            <div
                v-for="(revision, i) in revisions"
                :key="i"
                class="text-sm mb-3 pb-3 border-b flex items-center justify-between"
            >
                <div>
                    Created {{ moment(revision.date).fromNow() }} by {{ revision.user.name || revision.user.email }}
                    <blockquote v-if="revision.message" class="border-l pl-3 italic mt-1">
                        {{ revision.message }}
                    </blockquote>
                </div>
                <div>
                    <button class="btn btn-flat" @click="previewing = revision.date">Preview</button>
                    <stack name="revision-preview" v-if="previewing === revision.date" @closed="previewing = null">
                        <div class="bg-white h-full p-3">
                            the preview goes here.
                        </div>
                    </stack>
                    <button class="btn btn-flat ml-1" @click="restore(revision)">Restore</button>
                </div>
            </div>

        </div>

    </div>

</template>

<script>
export default {

    props: {
        url: String
    },

    data() {
        return {
            revisions: [],
            previewing: null,
        }
    },

    mounted() {
        this.$axios.get(this.url).then(response => {
            this.revisions = response.data.reverse();
        });
    },

    methods: {

        moment(timestamp) {
            return moment.unix(timestamp);
        },

        close() {
            this.$emit('closed');
        },

        restore(revision) {
            if (confirm('Are you sure you want to restore this revision?')) {
                this.$notify.success('Restoring revision (not really)');
                this.close();
            }
        }

    }

}
</script>
