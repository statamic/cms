<template>

    <entry-publish-form
        publish-container="base"
        :action="action"
        method="post"
        :initial-title="__('Create')"
        :collection-title="collectionTitle"
        :collection-url="collectionUrl"
        :initial-fieldset="fieldset"
        :initial-values="values"
        :initial-meta="meta"
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
    ],

    methods: {

        saved(response) {
            window.location = response.data.redirect;
        }

    }

}
</script>
