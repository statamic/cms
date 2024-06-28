<template>

    <div class="revision-item"
        :class="{
            'status-working-copy': revision.working,
            'status-published': revision.attributes.published
        }"
        @click="open"
    >
        <div v-if="revision.message" class="revision-item-note truncate" v-text="revision.message" />

        <div class="flex items-center">
            <avatar v-if="revision.user" :user="revision.user" class="shrink-0 rtl:ml-2 ltr:mr-2 w-6" />

            <div class="revision-item-content w-full flex">
                <div class="flex-1">
                    <div class="revision-author text-gray-700 dark:text-dark-150 text-2xs">
                        <template v-if="revision.user">{{ revision.user.name || revision.user.email }} &ndash;</template>
                        {{ date.toDate().toLocaleTimeString($config.get('locale').replace('_', '-'), { hour: 'numeric', minute: '2-digit' }) }}
                    </div>
                </div>

                <span class="badge" v-if="revision.working" v-text="__('Working Copy')" />
                <span class="badge" :class="revision.action" v-else v-text="__(revision.action)" />
                <span class="badge bg-orange" v-if="revision.attributes.current" v-text="__('Current')" />

                <revision-preview
                    v-if="showDetails"
                    :revision="revision"
                    component="entry-publish-form"
                    :component-props="componentProps"
                    @closed="showDetails = false"
                >
                    <template slot="action-buttons-right">
                        <restore-revision
                            v-if="canRestoreRevisions"
                            :revision="revision"
                            :url="restoreUrl"
                            :reference="reference"
                            class="rtl:mr-4 ltr:ml-4" />
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
        restoreUrl: String,
        reference: String,
        canRestoreRevisions: Boolean,
    },

    data() {
        return {
            showDetails: false,
            componentProps: {
                initialActions: 'actions',
                collectionTitle: 'collection.title',
                collectionUrl: 'collection.url',
                initialTitle: 'title',
                initialReference: 'reference',
                initialFieldset: 'blueprint',
                initialValues: 'values',
                initialLocalizedFields: 'localizedFields',
                initialMeta: 'meta',
                initialPublished: 'published',
                initialPermalink: 'permalink',
                initialLocalizations: 'localizations',
                initialHasOrigin: 'hasOrigin',
                initialOriginValues: 'originValues',
                initialOriginMeta: 'originMeta',
                initialSite: 'locale',
                initialIsWorkingCopy: 'hasWorkingCopy',
                initialIsRoot: 'isRoot',
                initialReadOnly: 'readOnly',
            }
        }
    },

    computed: {

        date() {
            return moment.unix(this.revision.date);
        }

    },

    methods: {

        open() {
            if (this.revision.working) {
                this.$emit('working-copy-selected');
                return;
            }

            this.showDetails = true;
        }

    }

}
</script>
