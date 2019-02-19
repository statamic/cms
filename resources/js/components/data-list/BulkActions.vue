<template>

    <div v-if="hasSelections" class="flex items-center bg-grey-lighter text-sm border-b px-2 py-1">

        <div
            class="text-grey mr-2"
            v-text="__n(`:count Selected`, selections.length)" />

        <div class="flex-1 text-right">

            <data-list-action
                v-for="action in sortedActions"
                :key="action.handle"
                :action="action"
                :selections="selections.length"
                @selected="run"
            >
                <button
                    slot-scope="{ action, select }"
                    class="ml-2 hover:text-grey-dark"
                    :class="[action.dangerous ? 'text-red' : 'text-blue']"
                    @click="select"
                    v-text="action.title" />
            </data-list-action>

        </div>

    </div>

</template>

<script>
import Actions from './Actions.vue';

export default {

    mixins: [Actions],

    inject: ['sharedState'],

    computed: {

        selections() {
            return this.sharedState.selections;
        },

        hasSelections() {
            return this.selections.length > 0;
        }

    }

}
</script>
