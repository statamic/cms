<script setup>
import TermPublishForm from '@/components/terms/PublishForm.vue';
import Head from '@/pages/layout/Head.vue';

defineProps([
    'actions',
    'taxonomy',
    'title',
    'reference',
    'blueprint',
    'values',
    'meta',
    'localizedFields',
    'published',
    'locale',
    'localizations',
    'hasOrigin',
    'originValues',
    'originMeta',
    'permalink',
    'hasWorkingCopy',
    'revisionsEnabled',
    'readOnly',
    'canEditBlueprint',
    'createAnotherUrl',
    'listingUrl',
    'previewTargets',
    'itemActions',
    'hasTemplate',
]);
</script>

<template>
    <Head :title />

    <term-publish-form
        publish-container="base"
        :initial-actions="actions"
        method="patch"
        :taxonomy-handle="taxonomy"
        :initial-title="title"
        :initial-reference="reference"
        :initial-fieldset="blueprint"
        :initial-values="values"
        :initial-meta="meta"
        :initial-localized-fields="localizedFields"
        :initial-published="published"
        :initial-site="locale"
        :initial-localizations="localizations"
        :initial-has-origin="hasOrigin"
        :initial-origin-values="originValues"
        :initial-origin-meta="originMeta"
        :initial-permalink="permalink"
        :initial-is-working-copy="hasWorkingCopy"
        :revisions-enabled="revisionsEnabled"
        :initial-read-only="readOnly"
        :can-edit-blueprint="canEditBlueprint"
        :create-another-url="createAnotherUrl"
        :listing-url="listingUrl"
        :preview-targets="previewTargets"
        :initial-item-actions="itemActions"
        :has-template="hasTemplate"
    />
</template>
