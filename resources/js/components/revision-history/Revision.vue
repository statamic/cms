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
            <avatar v-if="revision.user" :user="revision.user" class="flex-no-shrink mr-1 w-6" />

            <div class="revision-item-content w-full flex">
                <div class="flex-1">
                    <div class="revision-author text-grey-70 text-2xs">
                        <template v-if="revision.user">{{ revision.user.name || revision.user.email }} &ndash;</template>
                        {{ date.fromNow() }}
                    </div>
                </div>

                <span class="badge" v-if="revision.working" v-text="__('Working Copy')" />
                <span class="badge" :class="revision.action" v-else v-text="revision.action" />
                <span class="badge bg-orange" v-if="revision.attributes.current" v-text="'Current'" />

                <revision-preview
                    v-if="showDetails"
                    :revision="revision"
                    @closed="showDetails = false"
                >
                    <template slot="action-buttons-right">
                        <restore-revision :revision="revision" :url="restoreUrl" />
                    </template>
                </revision-preview>
            </div>
        </div>
    </div>

</template>

<script>
import RestoreRevision from './Restore.vue';
import RevisionPreview from './Preview.vue';

export default {

    components: {
        RevisionPreview,
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
