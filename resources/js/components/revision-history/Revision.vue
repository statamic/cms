<template>

    <div class="revision-item"
        :class="{
            'status-working-copy': revision.working,
            'status-published': revision.attributes.published
        }"
        @click="showDetails = true"
    >
        <div v-if="revision.message" class="revision-item-note text-truncate" v-text="revision.message" />

        <div class="flex items-center">
            <avatar :user="revision.user" class="flex-no-shrink mr-1 w-6" />

            <div class="revision-item-content w-full flex">
                <div class="flex-1">
                    <div class="revision-author text-grey-70 text-2xs">
                        {{ revision.user.name || revision.user.email }} &ndash; {{ date.fromNow() }}
                    </div>
                </div>

                <span class="badge" v-if="revision.working" v-text="__('Working Copy')" />
                <span class="badge" :class="revision.action" v-else v-text="revision.action" />
                <span class="badge bg-orange" v-if="revision.attributes.current" v-text="'Current'" />

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
                                class="ml-2 p-1 text-xl text-grey-60 hover:text-grey-80"
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
