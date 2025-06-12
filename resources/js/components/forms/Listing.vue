<template>
    <data-list
        :visible-columns="columns"
        :columns="columns"
        :rows="items"
        @selections-updated="selections = $event"
    >
        <div>
            <BulkActions
                :url="actionUrl"
                :selections="selections"
                @started="actionStarted"
                @completed="actionCompleted"
                v-slot="{ actions }"
            >
                <div class="fixed inset-x-0 bottom-1 z-100 flex w-full justify-center">
                    <ButtonGroup>
                        <Button
                            variant="primary"
                            class="text-gray-400!"
                            :text="__n(`:count item selected|:count items selected`, selections.length)"
                        />
                        <Button
                            v-for="action in actions"
                            :key="action.handle"
                            variant="primary"
                            :text="__(action.title)"
                            @click="action.run"
                        />
                    </ButtonGroup>
                </div>
            </BulkActions>

            <ui-panel>
                <data-list-table :allow-bulk-actions="true">
                    <template #cell-title="{ row: form }">
                        <a :href="form.show_url">{{ form.title }}</a>
                    </template>
                    <template #actions="{ row: form, index }">
                        <ItemActions
                            :url="actionUrl"
                            :actions="form.actions"
                            :item="form.id"
                            @started="actionStarted"
                            @completed="actionCompleted"
                            v-slot="{ actions }"
                        >
                            <Dropdown v-if="form.can_edit || form.can_edit_blueprint || form.actions.length" placement="left-start" class="me-3">
                                <DropdownMenu>
                                    <DropdownLabel :text="__('Actions')" />
                                    <DropdownItem v-if="form.can_edit" :text="__('Edit')" :href="form.edit_url" icon="edit" />
                                    <DropdownItem v-if="form.can_edit_blueprint" icon="blueprint-edit" :text="__('Edit Blueprint')" :href="form.blueprint_url" />
                                    <DropdownSeparator v-if="actions.length" />
                                    <DropdownItem
                                        v-for="action in actions"
                                        :key="action.handle"
                                        :text="__(action.title)"
                                        :icon="action.icon"
                                        :variant="action.dangerous ? 'destructive' : 'default'"
                                        @click="action.run"
                                    />
                                </DropdownMenu>
                            </Dropdown>
                        </ItemActions>
                    </template>
                </data-list-table>
            </ui-panel>
        </div>
    </data-list>
</template>

<script>
import Listing from '../Listing.vue';
import {
    CardPanel,
    Dropdown,
    DropdownMenu,
    DropdownLabel,
    DropdownItem,
    DropdownSeparator,
    ButtonGroup, Button,
} from '@statamic/ui';
import ItemActions from '@statamic/components/actions/ItemActions.vue';
import BulkActions from '@statamic/components/actions/BulkActions.vue';

export default {
    mixins: [Listing],

    components: {
        BulkActions,
        Button,
        ButtonGroup,
        ItemActions,
        Dropdown,
        DropdownMenu,
        DropdownLabel,
        DropdownItem,
        DropdownSeparator,
        CardPanel,
    },

    props: ['initialColumns'],

    data() {
        return {
            columns: this.initialColumns,
            requestUrl: cp_url('forms'),
        };
    },
};
</script>
