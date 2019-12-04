<template>

    <term-publish-form
        :is-creating="true"
        publish-container="base"
        :initial-actions="actions"
        method="post"
        :initial-title="__('Create')"
        :taxonomy-title="taxonomyTitle"
        :taxonomy-url="taxonomyUrl"
        :initial-fieldset="fieldset"
        :initial-values="values"
        :initial-meta="meta"
        :initial-published="published"
        :initial-localizations="localizations"
        :initial-has-origin="false"
        :initial-is-root="true"
        :initial-origin-values="{}"
        @saved="saved"
    ></term-publish-form>

</template>

<script>
export default {

    props: [
        'actions',
        'taxonomyTitle',
        'taxonomyUrl',
        'fieldset',
        'values',
        'meta',
        'published',
        'localizations',
    ],

    methods: {

        saved(response) {
            window.location = response.data.data.redirect + '?created=true';
        }

    }

}
</script>
