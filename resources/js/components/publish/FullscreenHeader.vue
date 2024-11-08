<template>

    <header class="bg-white dark:bg-dark-550 fixed top-0 inset-x-0 px-4 flex items-center justify-between shadow z-max">
        <h2 class="w-full" v-text="__(config.display)" />
        <div class="grow-1 min-w-max flex gap-4 items-center">
            <slot />
        </div>
        <div class="w-full py-2.5 flex justify-end items-center">
            <dropdown-list class="mr-2" v-if="actions.length || internalActions.length">
                <dropdown-actions :actions="actions" @run="runAction" v-if="actions.length" />
                <div class="divider" />
                <dropdown-actions :actions="internalActions" @run="runAction" v-if="internalActions.length" />
            </dropdown-list>
            <button
                class="btn-quick-action"
                v-for="(action, index) in quickActions"
                :key="index"
                v-tooltip="action.title"
                @click="runAction(action)">
                <svg-icon :name="fieldActionIcon(action)" class="h-4 w-4" />
            </button>
        </div>
    </header>

</template>

<script>

export default {

    props: {
        field: {
            type: Object,
            required: true,
        },
        config: {
            type: Object,
            required: true,
        },
        runAction: {
            type: Function,
            required: true,
        },
        actions: {
            type: Array,
            default: () => [],
        },
        internalActions: {
            type: Array,
            default: () => [],
        },
        quickActions: {
            type: Array,
            default: () => [],
        },
    },

    methods: {

        fieldActionIcon({ icon }) {
            return typeof icon === 'function' ? icon({ field: this.field }) : icon;
        },

    },

}

</script>
