<script setup>
import EntryPublishForm from '@/components/entries/PublishForm.vue';
import { router } from '@inertiajs/vue3';
import Head from '@/pages/layout/Head.vue';

defineProps([
    'actions',
    'collectionHandle',
    'collectionCreateLabel',
    'collectionHasRoutes',
    'blueprint',
    'values',
    'extraValues',
    'meta',
    'localizations',
    'revisions',
    'site',
    'parent',
    'canManagePublishState',
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
    <Head :title="collectionCreateLabel" />

    <entry-publish-form
        :is-creating="true"
        publish-container="base"
        :initial-actions="actions"
        method="post"
        :initial-title="collectionCreateLabel"
        :collection-handle="collectionHandle"
        :collection-has-routes="collectionHasRoutes"
        :initial-fieldset="blueprint"
        :initial-values="values"
        :initial-extra-values="extraValues"
        :initial-meta="meta"
        :initial-localizations="localizations"
        :initial-has-origin="false"
        :initial-origin-values="{}"
        :revisions-enabled="revisions"
        :initial-site="site"
        :parent="parent"
        :can-manage-publish-state="canManagePublishState"
        :can-edit-blueprint="canEditBlueprint"
        :create-another-url="createAnotherUrl"
        :initial-listing-url="listingUrl"
        :preview-targets="previewTargets"
        @saved="saved"
    ></entry-publish-form>
</template>
