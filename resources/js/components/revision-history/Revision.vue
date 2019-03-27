<template>

    <div class="revision-item"
        :class="{
            'status-working-copy': revision.working,
            'status-published': revision.attributes.published
        }"
        @click="showDetails = true"
    >
        <div class="revision-item-bullet-container">
            <i class="revision-item-bullet"></i>
        </div>
        <div class="revision-item-content">
            <div class="flex items-center">
                <span>
                    {{ date.fromNow() }}
                    &mdash;
                    {{ revision.user.name || revision.user.email }}
                </span>
                <span class="badge" v-if="revision.working" v-text="__('Working Copy')" />
                <span class="badge" v-if="!revision.working && revision.attributes.published" v-text="__('Published')" />
            </div>
            <div v-if="revision.message" class="revision-item-note" v-text="revision.message" />

            <stack
                name="revision-details"
                v-if="showDetails"
                @closed="showDetails = false"
            >
                <div slot-scope="{ close }" class="bg-white h-full flex flex-col">
                    <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
                        {{ __('Revision Details') }}
                        <button
                            type="button"
                            class="ml-2 p-1 text-xl text-grey-60"
                            @click="close"
                            v-html="'&times'" />
                    </div>
                    <div class="bg-white h-full p-3 overflow-auto">
                        <restore-revision
                            v-if="!revision.working"
                            :revision="revision"
                            :url="restoreUrl" />

                        <button
                            v-if="revision.working"
                            class="btn btn-flat"
                            v-text="__('Discard')" />

                        <pre
                            class="whitespace-pre-wrap text-xs font-mono mt-3"
                            v-text="JSON.stringify(revision.attributes, null, 2)" />
                    </div>
                </div>
            </stack>
        </div>
    </div>

</template>

<script>
import RestoreRevision from './Restore.vue';

export default {

    components: {
        RestoreRevision,
    },

    props: {
        revision: Object,
        restoreUrl: String
    },

    data() {
        return {
            showDetails: false,
        }
    },

    computed: {

        date() {
            return moment.unix(this.revision.date);
        }

    }

}
</script>
