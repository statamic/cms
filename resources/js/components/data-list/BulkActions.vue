<template>

    <div v-if="hasSelections" class="flex items-center">

        <div class="text-grey text-2xs mr-1"
            v-text="__n(`:count Selected`, selections.length)" />

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

    data() {
        return {
            actions: []
        }
    },

    watch: {
        selections(selections) {
            this.getActions(selections);
        }
    },

    computed: {

        selections() {
            return this.sharedState.selections;
        },

        hasSelections() {
            return this.selections.length > 0;
        },

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
