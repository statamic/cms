<template>

    <header class="bg-white dark:bg-dark-550 fixed top-0 inset-x-0 px-4 flex items-center justify-between shadow z-max">
        <h2 class="w-full" v-text="__(config.display)" />
        <div class="grow-1 min-w-max flex gap-4 items-center">
            <slot />
        </div>
        <div class="w-full py-2.5 flex justify-end items-center">
            <dropdown-list class="mr-2" v-if="fieldActions.length || internalFieldActions.length">
                <dropdown-actions :actions="fieldActions" @run="runFieldAction" v-if="fieldActions.length" />
                <div class="divider" />
                <dropdown-actions :actions="internalFieldActions" @run="runFieldAction" v-if="internalFieldActions.length" />
            </dropdown-list>
            <button
                class="btn-quick-action"
                v-for="(action, index) in quickFieldActions"
                :key="index"
                v-tooltip="action.title"
                @click="runFieldAction(action)">
                <svg-icon :name="fieldActionIcon(action)" class="h-4 w-4" />
            </button>
        </div>
    </header>

</template>

<script>
import DropdownActions from "../field-actions/DropdownActions.vue";

export default {

    components: {
        DropdownActions
    },

    props: {
        field: {
            type: Object,
            required: true,
        },
        config: {
            type: Object,
            required: true,
        },
        runFieldAction: {
            type: Function,
            required: true,
        },
        fieldActions: {
            type: Array,
            default: () => [],
        },
        internalFieldActions: {
            type: Array,
            default: () => [],
        },
        quickFieldActions: {
            type: Array,
            default: () => [],
        },
    },

    methods: {

        fieldActionIcon({ icon }) {
            icon = icon || 'image';
            return typeof icon === 'function' ? icon({ field: this.field }) : icon;
        },

    },

}

</script>
