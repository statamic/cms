<script setup>
import { Head } from '@inertiajs/vue3';
import { Header, Button, Dropdown, DropdownMenu, DropdownItem } from '@/components/ui';
import UserListing from '@/components/users/Listing.vue';
import ResourceDeleter from '@/components/ResourceDeleter.vue';
import { ref } from 'vue';

const props = defineProps({
    group: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    listingConfig: {
        type: Object,
        required: true,
    },
});

const deleter = ref(null);

function handleDelete() {
    deleter.value?.confirm();
}
</script>

<template>
    <div class="max-w-5xl max-w-wrapper mx-auto">
        <Head :title="group.title" />

        <Header :title="group.title" icon="groups">
            <template #actions>
                <Dropdown v-if="group.canEdit || group.canDelete">
                    <template #trigger>
                        <Button icon="dots" variant="ghost" :aria-label="__('Actions')" />
                    </template>
                    <DropdownMenu>
                        <DropdownItem
                            v-if="group.canEdit"
                            :text="__('Edit Group')"
                            icon="edit"
                            :href="group.editUrl"
                        />
                        <DropdownItem
                            v-if="group.canDelete"
                            :text="__('Delete Group')"
                            icon="trash"
                            @click="handleDelete"
                        />
                    </DropdownMenu>
                </Dropdown>

                <Button
                    v-if="group.canEdit"
                    variant="primary"
                    :text="__('Edit Group')"
                    :href="group.editUrl"
                />
            </template>
        </Header>

        <UserListing
            :listing-key="listingConfig.listingKey"
            :group="listingConfig.groupId"
            :filters="filters"
            :allow-filter-presets="listingConfig.allowFilterPresets"
            :action-url="listingConfig.actionUrl"
        />

        <ResourceDeleter
            v-if="group.canDelete"
            ref="deleter"
            :resource-title="group.title"
            :route="group.deleteUrl"
            redirect="/cp/user-groups"
        />
    </div>
</template>
