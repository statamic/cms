<script setup>
import TermPublishForm from '@/components/terms/PublishForm.vue';
import { router } from '@inertiajs/vue3';
import Head from '@/pages/layout/Head.vue';

defineProps([
    'actions',
    'taxonomy',
    'taxonomyCreateLabel',
    'blueprint',
    'values',
    'meta',
    'published',
    'localizations',
    'locale',
    'canEditBlueprint',
    'createAnotherUrl',
    'listingUrl',
    'previewTargets',
]);

function saved(response) {
    router.get(response.data.data.edit_url + '?created=true');
}
</script>

<template>
    <Head :title="taxonomyCreateLabel" />

    <term-publish-form
        :is-creating="true"
        publish-container="base"
        :initial-actions="actions"
        method="post"
        :initial-title="taxonomyCreateLabel"
        :taxonomy-handle="taxonomy"
        :initial-fieldset="blueprint"
        :initial-values="values"
        :initial-meta="meta"
        :initial-published="published"
        :initial-localizations="localizations"
        :initial-has-origin="false"
        :initial-origin-values="{}"
        :initial-site="locale"
        :can-edit-blueprint="canEditBlueprint"
        :create-another-url="createAnotherUrl"
        :listing-url="listingUrl"
        :preview-targets="previewTargets"
        @saved="saved"
    />
</template>
