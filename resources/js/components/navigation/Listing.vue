<template>
    <CardList :heading="__('Title')">
        <CardListItem v-for="item in navigations" :key="item.id">
            <a
                :href="item.available_in_selected_site ? item.show_url : item.edit_url"
                v-text="__(item.title)"
            />
            <dropdown-list>
                <dropdown-item :text="__('Edit')" :redirect="item.edit_url" />
                <dropdown-item v-if="item.deleteable" :text="__('Delete')" class="warning" @click="$refs[`deleter_${item.id}`].confirm()" >
                    <resource-deleter :ref="`deleter_${item.id}`" :resource="item" @deleted="removeRow(item)" />
                </dropdown-item>
            </dropdown-list>
        </CardListItem>
    </CardList>
</template>

<script>
import { CardList, CardListItem } from '@statamic/ui';

export default {
    components: { CardList, CardListItem },

    props: {
        navigations: { type: Array, required: true },
    },
};
</script>
