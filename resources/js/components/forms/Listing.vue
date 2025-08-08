<template>
    <Listing :items="items" :columns="initialColumns" :action-url="actionUrl">
        <template #cell-title="{ row: form }">
            <a :href="form.show_url">{{ form.title }}</a>
        </template>
        <template #prepended-row-actions="{ row: form }">
            <DropdownItem v-if="form.can_edit" :text="__('Configure')" :href="form.edit_url" icon="cog" />
            <DropdownItem
                v-if="form.can_edit_blueprint"
                icon="blueprint-edit"
                :text="__('Edit Blueprint')"
                :href="form.blueprint_url"
            />
        </template>
    </Listing>
</template>

<script>
import { Listing, DropdownItem } from '@statamic/ui';

export default {
    components: {
        Listing,
        DropdownItem,
    },

    props: ['items', 'initialColumns', 'actionUrl'],
};
</script>
