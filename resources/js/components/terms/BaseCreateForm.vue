<template>

    <term-publish-form
        :is-creating="true"
        publish-container="base"
        :initial-actions="actions"
        method="post"
        :initial-title="taxonomyCreateLabel"
        :taxonomy-handle="taxonomyHandle"
        :breadcrumbs="breadcrumbs"
        :initial-fieldset="fieldset"
        :initial-values="values"
        :initial-meta="meta"
        :initial-published="published"
        :initial-localizations="localizations"
        :initial-has-origin="false"
        :initial-is-root="true"
        :initial-origin-values="{}"
        :initial-site="site"
        :can-edit-blueprint="canEditBlueprint"
        :create-another-url="createAnotherUrl"
        :listing-url="listingUrl"
        :preview-targets="previewTargets"
        @saved="saved"
    ></term-publish-form>

</template>

<script>
export default {

    props: [
        'actions',
        'taxonomyHandle',
        'taxonomyCreateLabel',
        'breadcrumbs',
        'fieldset',
        'values',
        'meta',
        'published',
        'localizations',
        'site',
        'canEditBlueprint',
        'createAnotherUrl',
        'listingUrl',
        'previewTargets',
    ],

    methods: {

        saved(response) {
            window.location = response.data.data.edit_url + '?created=true';
        }

    }

}
</script>
