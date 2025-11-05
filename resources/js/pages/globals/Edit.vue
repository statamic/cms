<script setup>
import GlobalPublishForm from '@/components/globals/PublishForm.vue';
import Head from '@/pages/layout/Head.vue';

defineProps([
    'actions',
    'globalsUrl',
    'title',
    'handle',
    'reference',
    'blueprintHandle',
    'blueprint',
    'values',
    'localizedFields',
    'meta',
    'localizations',
    'hasOrigin',
    'originValues',
    'originMeta',
    'locale',
    'canConfigure',
    'configureUrl',
    'canEdit',
    'canEditBlueprint',
]);
</script>

<template>
    <Head :title="__('Edit Global Set')" />

    <GlobalPublishForm
        publish-container="base"
        :initial-actions="actions"
        method="patch"
        :globals-url="globalsUrl"
        :initial-title="title"
        :initial-handle="handle"
        :initial-reference="reference"
        :initial-blueprint-handle="blueprintHandle"
        :initial-fieldset="blueprint"
        :initial-values="values"
        :initial-localized-fields="localizedFields"
        :initial-meta="meta"
        :initial-localizations="localizations"
        :initial-has-origin="hasOrigin"
        :initial-origin-values="originValues"
        :initial-origin-meta="originMeta"
        :initial-site="locale"
        :can-configure="canConfigure"
        :configure-url="configureUrl"
        :can-edit="canEdit"
        :can-edit-blueprint="canEditBlueprint"
    />
</template>