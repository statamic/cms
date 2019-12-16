<template>

    <div v-if="hasSelections" class="flex items-center">

        <div class="text-grey text-2xs mr-1"
            v-text="__n(`:count selected`, selections.length)" />

            <data-list-action
                v-for="action in sortedActions"
                :key="action.handle"
                :action="action"
                :selections="selections.length"
                @selected="run"
            >
                <button
                    slot-scope="{ action, select }"
                    class="btn-flat ml-1"
                    :class="{'text-red': action.dangerous}"
                    @click="select"
                    v-text="__(action.title)" />
            </data-list-action>

    </div>

</template>

<script>
import Actions from './Actions.vue';

export default {

    mixins: [Actions],

    inject: ['sharedState'],

    props: {
        context: {
            type: Object,
            default: () => {}
        }
    },

    computed: {

        selections() {
            return this.sharedState.selections;
        },

        hasSelections() {
            return this.selections.length > 0;
        },

        actions() {
            const rows = this.sharedState.rows.filter(row => this.selections.includes(row.id));

            let actions = rows.reduce((carry, row) => carry.concat(row.actions), []);

            actions = _.uniq(actions, 'handle');

            // Remove any actions that are missing from any row. If you can't apply the action
            // to all of the selected items, you should not see the button. There's server
            // side authorization for when the action is executed anyway, just in case.
            rows.forEach(row => {
                actions = actions.filter(action => row.actions.map(a => a.handle).includes(action.handle));
            });

            return _.sortBy(actions, 'title');
        }

    },

    methods: {

        getActions(selections) {
            if (selections.length === 0) {
                this.actions = [];

                return;
            }

            let params = {selections};

            if (this.context) {
                params.context = this.context;
            }

            this.$axios.get(this.url, {params}).then(response => {
                this.actions = response.data;
            });
        },

    }

}
</script>
