<template>
    <CardList :heading="__('Title')">
        <CardListItem v-for="item in navigations" :key="item.id">
            <a
                :href="item.available_in_selected_site ? item.show_url : item.edit_url"
                v-text="__(item.title)"
            />
            <Dropdown placement="left-start">
                <DropdownMenu>
                    <DropdownItem :text="__('Configure')" icon="cog" :href="item.edit_url" />
                    <DropdownItem v-if="item.deleteable" :text="__('Delete')" icon="trash" variant="destructive" @click="$refs[`deleter_${item.id}`][0].confirm()" />
                </DropdownMenu>
            </Dropdown>

            <resource-deleter :ref="`deleter_${item.id}`" :resource="item" reload />
        </CardListItem>
    </CardList>
</template>

<script>
import { CardList, CardListItem, Dropdown, DropdownMenu, DropdownItem } from '@statamic/cms/ui';

export default {
    components: { CardList, CardListItem, Dropdown, DropdownMenu, DropdownItem },

    props: {
        navigations: { type: Array, required: true },
    },
};
</script>
