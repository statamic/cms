<template>

    <entry-publish-form
        :is-creating="true"
        publish-container="base"
        :initial-actions="actions"
        method="post"
        :initial-title="__('Create')"
        :collection-title="collectionTitle"
        :collection-url="collectionUrl"
        :initial-fieldset="fieldset"
        :initial-values="values"
        :initial-meta="meta"
        :initial-published="published"
        :initial-localizations="localizations"
        :initial-has-origin="false"
        :initial-is-root="true"
        :initial-origin-values="{}"
        @saved="saved"
    ></entry-publish-form>

</template>

<script>
export default {

    props: [
        'actions',
        'collectionTitle',
        'collectionUrl',
        'fieldset',
        'values',
        'meta',
        'published',
        'localizations',
    ],

    methods: {

        saved(response) {
            window.location = response.data.redirect + '?created=true';
        }

    }

}
</script>
