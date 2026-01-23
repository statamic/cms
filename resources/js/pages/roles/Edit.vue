<script setup>
import RolePublishForm from '@/components/roles/PublishForm.vue';
import Head from '@/pages/layout/Head.vue';

defineProps({
    role: Object,
    super: Boolean,
    permissions: Array,
    canAssignSuper: Boolean,
    action: String,
});
</script>

<template>
    <div class="max-w-6xl mx-auto" data-max-width-wrapper>
        <Head :title="__('Configure Role')" />

        <role-publish-form
            :action="action"
            method="patch"
            :can-assign-super="canAssignSuper"
            :initial-title="role.title"
            :initial-handle="role.handle"
            :initial-super="super"
            :initial-permissions="permissions"
        />
    </div>
</template>
