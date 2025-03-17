<template>
    <header class="fixed inset-x-0 top-0 z-max flex items-center justify-between bg-white px-4 shadow dark:bg-dark-550">
        <h2 class="w-full" v-text="__(title)" />
        <div class="grow-1 flex min-w-max items-center gap-4">
            <slot />
        </div>
        <div class="flex w-full items-center justify-end py-2.5">
            <dropdown-list class="mr-2" v-if="fieldActions.length">
                <dropdown-actions :actions="fieldActions" v-if="fieldActions.length" />
            </dropdown-list>
            <button
                class="btn-quick-action"
                v-for="(action, index) in fieldActions.filter((a) => a.quick)"
                :key="index"
                v-tooltip="action.title"
                @click="action.run()"
            >
                <svg-icon :name="action.icon" class="h-4 w-4" />
            </button>
        </div>
    </header>
</template>

<script>
import DropdownActions from '../field-actions/DropdownActions.vue';

export default {
    components: {
        DropdownActions,
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
    },
};
</script>
