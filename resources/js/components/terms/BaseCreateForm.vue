<template>

    <term-publish-form
        :is-creating="true"
        publish-container="base"
        :initial-actions="actions"
        method="post"
        :initial-title="__('Create')"
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
        :create-another-url="createAnotherUrl"
        :listing-url="listingUrl"
        @saved="saved"
    ></term-publish-form>

</template>

<script>
export default {

    props: [
        'actions',
        'taxonomyHandle',
        'breadcrumbs',
        'fieldset',
        'values',
        'meta',
        'published',
        'localizations',
        'createAnotherUrl',
        'listingUrl',
    ],

    methods: {

        saved(response) {
            window.location = response.data.data.redirect + '?created=true';
        }

    }

}
</script>
