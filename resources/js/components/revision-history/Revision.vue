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
            </div>

            <div class="flex items-center gap-1 ml-auto">
                <Badge
                    size="sm"
                    :color="
                        revision.action === 'working'
                            ? 'gray'
                            : {
                                  publish: 'green',
                                  revision: 'gray',
                                  restore: 'gray',
                                  unpublish: 'red',
                              }[revision.action]
                    "
                    :text="
                        revision.action === 'working'
                            ? __('Working Copy')
                            : {
                                  publish: __('Published'),
                                  revision: __('Revision'),
                                  restore: __('Restored'),
                                  unpublish: __('Unpublished'),
                              }[revision.action]
                    "
                />
                <Button
                    v-if="sharedPreviewUrl"
                    icon="share-link"
                    icon-only
                    size="xs"
                    variant="ghost"
                    inset
                    :aria-label="__('Share Revision')"
                    v-tooltip="__('Share Revision')"
                    :loading="copyingPreviewLink"
                    @click.stop="copyPreviewLink"
                />
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
import { Subheading, Badge, Avatar, Button } from '@/components/ui';

export default {
    components: {
        RevisionPreview,
        RestoreRevision,
        Subheading,
        Badge,
        Avatar,
        Button,
    },

    props: {
        revision: Object,
        restoreUrl: String,
        sharedPreviewUrl: String,
        reference: String,
        canRestoreRevisions: Boolean,
        isLast: Boolean,
    },

    data() {
        return {
            showDetails: false,
            copyingPreviewLink: false,
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

        async copyPreviewLink() {
            this.copyingPreviewLink = true;

            try {
                const payload = this.revision.action === 'working' ? {} : { revision: this.revision.date };
                const { data } = await this.$axios.post(this.sharedPreviewUrl, payload);

                try {
                    await navigator.clipboard.writeText(data.url);
                    this.$toast.success(
                        __('messages.shared_preview_link_copied', { hours: data.expires_in_hours }),
                    );
                } catch {
                    Statamic.$callbacks.call('copyToClipboard', data.url);
                }
            } catch {
                this.$toast.error(__('Unable to copy preview link'));
            } finally {
                this.copyingPreviewLink = false;
            }
        },
    },
};
</script>
