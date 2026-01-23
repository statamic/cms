<script setup>
import UserPublishForm from '@/components/users/PublishForm.vue';
import Head from '@/pages/layout/Head.vue';

defineProps([
    'actions',
    'title',
    'reference',
    'blueprint',
    'values',
    'meta',
    'canEditPassword',
    'canEditBlueprint',
    'requiresCurrentPassword',
    'itemActions',
    'itemActionUrl',
    'twoFactor',
]);
</script>

<template>
    <div class="max-w-5xl mx-auto" data-max-width-wrapper>
        <Head :title="__('Edit User')" />

        <UserPublishForm
            :actions="actions"
            method="patch"
            publish-container="base"
            :initial-title="title"
            :initial-reference="reference"
            :initial-fieldset="blueprint"
            :initial-values="values"
            :initial-meta="meta"
            :can-edit-password="canEditPassword"
            :can-edit-blueprint="canEditBlueprint"
            :requires-current-password="requiresCurrentPassword"
            :initial-item-actions="itemActions"
            :item-action-url="itemActionUrl"
            :two-factor="twoFactor"
        />
    </div>
</template>