<template>
    <CardList :heading="__('Title')">
        <CardListItem v-for="global in globals" :key="global.id">
            <Tooltip :text="global.handle" :delay="1000">
                <a :href="global.edit_url">{{ __(global.title) }}</a>
            </Tooltip>
            <dropdown-list>
                <dropdown-item :text="__('Edit')" :redirect="global.edit_url" />
                <dropdown-item v-if="global.deleteable" :text="__('Delete')" class="warning" @click="$refs[`deleter_${global.id}`].confirm()">
                    <resource-deleter :ref="`deleter_${global.id}`" :resource="global" @deleted="removeRow(global)" />
                </dropdown-item>
            </dropdown-list>
        </CardListItem>
    </CardList>
</template>

<script>
import { CardList, CardListItem, Tooltip } from '@statamic/ui';

export default {

    components: { CardList, CardListItem, Tooltip },

    props: {
        globals: { type: Array, required: true },
    },
};
</script>
