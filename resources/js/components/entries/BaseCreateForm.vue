<template>

    <entry-publish-form
        :is-creating="true"
        publish-container="base"
        :initial-actions="actions"
        method="post"
        :initial-title="__('Create Entry')"
        :collection-handle="collectionHandle"
        :initial-fieldset="fieldset"
        :initial-values="values"
        :initial-meta="meta"
        :initial-localizations="localizations"
        :initial-has-origin="false"
        :initial-is-root="true"
        :initial-origin-values="{}"
        :revisions-enabled="revisions"
        :breadcrumbs="breadcrumbs"
        :initial-site="site"
        :create-another-url="createAnotherUrl"
        :listing-url="listingUrl"
        @saved="saved"
    ></entry-publish-form>

</template>

<script>
export default {

    props: [
        'actions',
        'collectionHandle',
        'fieldset',
        'values',
        'meta',
        'localizations',
        'revisions',
        'breadcrumbs',
        'site',
        'createAnotherUrl',
        'listingUrl',
    ],

    methods: {

        saved(response) {
            window.location = response.data.data.edit_url + '?created=true';
        }

    }

}
</script>
