<script setup>
import EntryPublishForm from '@/components/entries/PublishForm.vue';
import Head from '@/pages/layout/Head.vue';

defineProps([
    'actions',
    'collection',
    'collectionHasRoutes',
    'title',
    'reference',
    'blueprint',
    'values',
    'extraValues',
    'localizedFields',
    'meta',
    'permalink',
    'originBehavior',
    'localizations',
    'hasOrigin',
    'originValues',
    'originMeta',
    'locale',
    'hasWorkingCopy',
    'revisionsEnabled',
    'readOnly',
    'canEditBlueprint',
    'canManagePublishState',
    'createAnotherUrl',
    'initialListingUrl',
    'previewTargets',
    'autosaveInterval',
    'itemActions',
    'itemActionUrl',
])
</script>

<template>
    <Head :title />

    <EntryPublishForm
        publish-container="base"
        :initial-actions="actions"
        method="patch"
        :collection-handle="collection"
        :collection-has-routes="collectionHasRoutes"
        :initial-title="title"
        :initial-reference="reference"
        :initial-fieldset="blueprint"
        :initial-values="values"
        :initial-extra-values="extraValues"
        :initial-localized-fields="localizedFields"
        :initial-meta="meta"
        :initial-permalink="permalink"
        :origin-behavior="originBehavior"
        :initial-localizations="localizations"
        :initial-has-origin="hasOrigin"
        :initial-origin-values="originValues"
        :initial-origin-meta="originMeta"
        :initial-site="locale"
        :initial-is-working-copy="hasWorkingCopy"
        :revisions-enabled="revisionsEnabled"
        :initial-read-only="readOnly"
        :can-edit-blueprint="canEditBlueprint"
        :can-manage-publish-state="canManagePublishState"
        :create-another-url="createAnotherUrl"
        :initial-listing-url="initialListingUrl"
        :preview-targets="previewTargets"
        :autosave-interval="autosaveInterval"
        :initial-item-actions="itemActions"
        :item-action-url="itemActionUrl"
    />
</template>
