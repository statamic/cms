<template>
    <CardList :heading="__('Title')">
        <CardListItem v-for="global in globals" :key="global.id">
            <Tooltip :text="global.handle" :delay="1000">
                <a :href="global.edit_url">{{ __(global.title) }}</a>
            </Tooltip>
            <Dropdown>
                <DropdownMenu>
                    <DropdownItem :text="__('Edit')" icon="edit" :href="global.edit_url" />
                    <DropdownItem
                        v-if="global.configurable"
                        :text="__('Configure')"
                        icon="cog"
                        :href="global.configure_url"
                    />
                    <DropdownItem
                        v-if="global.deleteable"
                        :text="__('Delete')"
                        icon="trash"
                        variant="destructive"
                        @click="$refs[`deleter_${global.id}`][0].confirm()"
                    />
                </DropdownMenu>
            </Dropdown>
            <resource-deleter :ref="`deleter_${global.id}`" :resource="global" @deleted="deleted(global)" />
        </CardListItem>
    </CardList>
</template>

<script>
import { CardList, CardListItem, Tooltip, Dropdown, DropdownMenu, DropdownItem } from '@statamic/ui';

export default {
    components: { CardList, CardListItem, Tooltip, Dropdown, DropdownMenu, DropdownItem },

    props: {
        initialGlobals: { type: Array, required: true },
    },

    data() {
        return {
            globals: this.initialGlobals,
        };
    },

    methods: {
        deleted(global) {
            this.globals = this.globals.filter((g) => g.id !== global.id);
        },
    },
};
</script>
