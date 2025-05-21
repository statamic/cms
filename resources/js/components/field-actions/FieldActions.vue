<template>
    <div class="field-dropdown">
        <div class="quick-list">
            <div class="quick-list-content">
                <ui-button
                    v-for="(action, index) in actions.filter((a) => a.quick)"
                    :key="index"
                    @click="action.run()"
                    v-tooltip="action.title"
                    :icon-only="true"
                    size="sm"
                    variant="ghost"
                >
                    <svg-icon :name="action.icon" class="size-3" />
                </ui-button>
            </div>
            <dropdown-list placement="left-start" :offset="[7, -3]">
                <dropdown-actions :actions="actions" />
            </dropdown-list>
        </div>
    </div>
</template>

<script>
import DropdownActions from '../field-actions/DropdownActions.vue';

export default {
    components: {
        DropdownActions,
    },

    props: {
        actions: {
            type: Array,
        },
    },
};
</script>
