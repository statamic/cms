<template>

    <entry-publish-form
        publish-container="base"
        :initial-action="action"
        method="post"
        :initial-title="__('Create')"
        :collection-title="collectionTitle"
        :collection-url="collectionUrl"
        :initial-fieldset="fieldset"
        :initial-values="values"
        :initial-meta="meta"
        :initial-localizations="localizations"
        @saved="saved"
    ></entry-publish-form>

</template>

<script>
export default {

    props: [
        'action',
        'collectionTitle',
        'collectionUrl',
        'fieldset',
        'values',
        'meta',
        'localizations',
    ],

    methods: {

        saved(response) {
            window.location = response.data.redirect;
        }

    }

}
</script>
