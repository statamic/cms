<script setup>
import { Badge, Button, Modal, ModalClose } from '@statamic/ui';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import { computed } from 'vue';

const { activeFilters, activeFilterBadges, setFilter } = injectListingContext();

const badgeCount = computed(() => {
    let count = Object.keys(activeFilterBadges.value).length;

    if (activeFilterBadges.value.hasOwnProperty('fields')) {
        count = count + Object.keys(activeFilterBadges.value.fields).length - 1;
    }

    return count;
});

const fieldFilterBadges = computed(() => {
    return activeFilterBadges.value.fields || {};
});

const standardBadges = computed(() => {
    const { fields, ...badges } = activeFilterBadges.value;
    return badges;
});

function removeFieldFilter(handle) {
    const fields = { ...activeFilters.value.fields };
    delete fields[handle];
    setFilter('fields', fields);
}
</script>

<template>
    <div class="flex flex-1 items-center gap-3 overflow-x-auto py-3">
        <Modal :title="__('Apply Filters')">
            <template #trigger>
                <Button icon="filter" class="relative">
                    {{ __('Filter') }}
                    <Badge
                        v-if="badgeCount"
                        :text="badgeCount"
                        pill
                        variant="filled"
                        class="absolute -top-1.5 -right-1.5"
                    />
                </Button>
            </template>
            <div class="space-y-6 py-3">FILTERS GO HERE</div>
            <template #footer>
                <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                    <ModalClose>
                        <Button text="Cancel" variant="ghost" />
                    </ModalClose>
                    <Button text="Update Filter" variant="primary" />
                </div>
            </template>
        </Modal>

        <Button
            v-for="(badge, handle) in fieldFilterBadges"
            variant="filled"
            icon-append="x"
            :text="badge"
            @click="removeFieldFilter(handle)"
        />
        <Button
            v-for="(badge, handle) in standardBadges"
            variant="filled"
            icon-append="x"
            :text="badge"
            @click="setFilter(handle, null)"
        />
    </div>
</template>
