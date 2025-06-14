<template>
    <entry-publish-form
        :is-creating="true"
        publish-container="base"
        :initial-actions="actions"
        method="post"
        :initial-title="collectionCreateLabel"
        :collection-handle="collectionHandle"
        :collection-has-routes="collectionHasRoutes"
        :initial-fieldset="fieldset"
        :initial-values="values"
        :initial-extra-values="extraValues"
        :initial-meta="meta"
        :initial-localizations="localizations"
        :initial-has-origin="false"
        :initial-is-root="true"
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

<script>
export default {
    props: [
        'actions',
        'collectionHandle',
        'collectionCreateLabel',
        'collectionHasRoutes',
        'fieldset',
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
    ],

    methods: {
        saved(response) {
            window.location = response.data.data.edit_url + '?created=true';
        },
    },
};
</script>
