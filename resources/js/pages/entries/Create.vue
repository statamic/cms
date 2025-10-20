<script setup>
import EntryPublishForm from '@/components/entries/PublishForm.vue';
import { router } from '@inertiajs/vue3';
import Head from '@/pages/layout/Head.vue';

defineProps([
    'actions',
    'collection',
    'collectionCreateLabel',
    'collectionHasRoutes',
    'blueprint',
    'values',
    'extraValues',
    'meta',
    'localizations',
    'locale',
    'revisionsEnabled',
    'parent',
    'canEditBlueprint',
    'canManagePublishState',
    'createAnotherUrl',
    'initialListingUrl',
    'previewTargets',
]);

function saved(response) {
    router.get(response.data.data.edit_url + '?created=true');
}
</script>

<template>
    <Head :title="collectionCreateLabel" />

    <entry-publish-form
        :is-creating="true"
        publish-container="base"
        :initial-actions="actions"
        method="post"
        :initial-title="collectionCreateLabel"
        :collection-handle="collection"
        :collection-has-routes="collectionHasRoutes"
        :initial-fieldset="blueprint"
        :initial-values="values"
        :initial-extra-values="extraValues"
        :initial-meta="meta"
        :initial-localizations="localizations"
        :initial-has-origin="false"
        :initial-origin-values="{}"
        :revisions-enabled="revisionsEnabled"
        :initial-site="locale"
        :parent="parent"
        :can-manage-publish-state="canManagePublishState"
        :can-edit-blueprint="canEditBlueprint"
        :create-another-url="createAnotherUrl"
        :initial-listing-url="initialListingUrl"
        :preview-targets="previewTargets"
        @saved="saved"
    ></entry-publish-form>
</template>
