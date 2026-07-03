<script setup>
import UserWizard from '@/components/users/Wizard.vue';
import Head from '@/pages/layout/Head.vue';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

defineProps([
    'route',
    'usersIndexUrl',
    'usersCreateUrl',
    'canCreateSupers',
    'canAssignRoles',
    'canAssignGroups',
    'activationExpiry',
    'separateNameFields',
    'canSendInvitation',
    'blueprint',
    'fields',
    'meta',
    'initialValues',
]);

useArchitecturalBackground();
</script>

<template>
    <Head :title="__('Create User')" />

    <UserWizard
        :route="route"
        :users-index-url="usersIndexUrl"
        :users-create-url="usersCreateUrl"
        :can-create-supers="canCreateSupers"
        :can-assign-roles="canAssignRoles"
        :can-assign-groups="canAssignGroups"
        :activation-expiry="activationExpiry"
        :separate-name-fields="separateNameFields"
        :can-send-invitation="canSendInvitation"
        :blueprint="blueprint"
        :fields="fields"
        :meta="meta"
        :initial-values="initialValues"
    />
</template>