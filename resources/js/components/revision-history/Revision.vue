<template>

    <div>
        <div>
            Created {{ date.fromNow() }} by {{ revision.user.name || revision.user.email }}
            <blockquote v-if="revision.message" class="border-l pl-3 italic mt-1">
                {{ revision.message }}
            </blockquote>
        </div>

        <div class="flex">
            <preview-revision :revision="revision" class="mr-2" />
            <restore-revision :revision="revision" :url="restoreUrl" />
        </div>
    </div>

</template>

<script>
import PreviewRevision from './Preview.vue';
import RestoreRevision from './Restore.vue';

export default {

    components: {
        PreviewRevision,
        RestoreRevision,
    },

    props: {
        revision: Object,
        restoreUrl: String
    },

    computed: {

        date() {
            return moment.unix(this.revision.date);
        }

    }

}
</script>
