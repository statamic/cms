<template>
    <div class="field-dropdown">
        <div class="quick-list">
            <div class="quick-list-content">
                <a
                    v-for="(action, index) in actions.filter(a => a.quick)"
                    :key="index"
                    @click="action.run()"
                    v-tooltip="action.title"
                >
                    <svg-icon :name="action.icon" class="h-3 w-3" />
                </a>
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
        DropdownActions
    },

    props: {
        actions: {
            type: Array
        }
    }
}
</script>
