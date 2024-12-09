<template>

    <header class="bg-white dark:bg-dark-550 fixed top-0 inset-x-0 px-4 flex items-center justify-between shadow z-max">
        <h2 class="w-full" v-text="__(title)" />
        <div class="grow-1 min-w-max flex gap-4 items-center">
            <slot />
        </div>
        <div class="w-full py-2.5 flex justify-end items-center">
            <dropdown-list class="mr-2" v-if="fieldActions.length">
                <dropdown-actions :actions="fieldActions" v-if="fieldActions.length" />
            </dropdown-list>
            <button
                class="btn-quick-action"
                v-for="(action, index) in fieldActions.filter(a => a.quick)"
                :key="index"
                v-tooltip="action.title"
                @click="action.run()">
                <svg-icon :name="action.icon" class="h-4 w-4" />
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
        title: {
            type: String,
            required: true,
        },
        fieldActions: {
            type: Array,
            default: () => [],
        },
    }

}

</script>
