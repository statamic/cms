<script setup>
import RolePublishForm from '@/components/roles/PublishForm.vue';
import Head from '@/pages/layout/Head.vue';

defineProps({
    permissions: Array,
    canAssignSuper: Boolean,
    action: String,
    indexUrl: String,
});
</script>

<template>
    <Head :title="__('Create Role')" />

    <role-publish-form
        :action="action"
        method="post"
        :can-assign-super="canAssignSuper"
        :initial-permissions="permissions"
        :index-url="indexUrl"
    />
</template>
