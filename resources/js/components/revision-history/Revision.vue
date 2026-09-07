<template>
    <div
        class="relative block cursor-pointer space-y-2 px-3 py-2 text-sm hover:[&_.revision-message]:underline"
        :class="{
            'status-working-copy': revision.action === 'working',
            'status-published': revision.attributes.published,
            'border border-ui-accent-bg dark:border-ui-accent-bg/90 rounded-lg py-2.5 bg-[hsl(from_var(--theme-color-ui-accent-bg)_h_s_97)] dark:bg-[hsl(from_var(--theme-color-ui-accent-bg)_h_40_20)]': revision.attributes.current,
            'bg-gradient-to-b from-transparent from-60% to-white dark:to-gray-800 -mt-1': isLast,
        }"
        v-tooltip="revision.attributes.current ? __('Current Revision') : null"
        @click="open"
    >
        <div class="flex gap-3">
            <Avatar v-if="revision.user" :user="revision.user" class="size-6 shrink-0 mt-1" />

            <div class="grid gap-1">
                <div v-if="revision.message" class="revision-message font-medium" v-text="revision.message" />
                <Subheading class="text-xs text-gray-500! dark:text-gray-400!" :class="{ 'text-gray-800! dark:text-white!': revision.attributes.current }">
                    {{ time }}
                    <template v-if="revision.user">
                        by {{ revision.user.name || revision.user.email }}
                    </template>
                </Subheading>
                <Subheading
                    v-if="revision.publish_at"
                    class="text-xs text-gray-500! dark:text-gray-400!"
                    v-text="__('Scheduled to publish :date', { date: publishAt })"
                />
            </div>

            <div class="flex items-center gap-1 ml-auto">
                <Badge size="sm" :color="badgeColor" :text="badgeText" />
            </div>

            <revision-preview
                v-if="showDetails"
                :revision="revision"
                component="entry-publish-form"
                :component-props="componentProps"
                @closed="showDetails = false"
            >
                <template #action-buttons-right>
                    <restore-revision
                        v-if="canRestoreRevisions"
                        :revision="revision"
                        :url="restoreUrl"
                        :reference="reference"
                        class="ltr:ml-4 rtl:mr-4"
                    />
                </template>
            </revision-preview>
        </div>
    </div>
</template>

<script>
import RestoreRevision from './Restore.vue';
import RevisionPreview from './Preview.vue';
import DateFormatter from '@/components/DateFormatter.js';
import { Subheading, Badge, Avatar } from '@/components/ui';

export default {
    components: {
        RevisionPreview,
        RestoreRevision,
        Subheading,
        Badge,
        Avatar,
    },

    props: {
        revision: Object,
        restoreUrl: String,
        reference: String,
        canRestoreRevisions: Boolean,
        isLast: Boolean,
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
                initialReadOnly: 'readOnly',
            },
        };
    },

    computed: {
        badgeColor() {
            if (this.revision.action === 'working') return 'gray';
            if (this.revision.publish_at) return 'amber';

            return {
                publish: 'green',
                revision: 'gray',
                restore: 'gray',
                unpublish: 'red',
            }[this.revision.action];
        },

        badgeText() {
            if (this.revision.action === 'working') return __('Working Copy');
            if (this.revision.publish_at) return __('Scheduled');

            return {
                publish: __('Published'),
                revision: __('Revision'),
                restore: __('Restored'),
                unpublish: __('Unpublished'),
            }[this.revision.action];
        },

        publishAt() {
            return DateFormatter.format(this.revision.publish_at * 1000, 'datetime');
        },

        time() {
            return DateFormatter.format(this.revision.date * 1000, 'time');
        },
    },

    methods: {
        open() {
            if (this.revision.action === 'working') {
                this.$emit('working-copy-selected');
                return;
            }

            this.showDetails = true;
        },
    },
};
</script>
