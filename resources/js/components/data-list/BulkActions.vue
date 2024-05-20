<template>

    <div v-if="showAlways || hasSelections" class="data-list-bulk-actions">
        <div class="input-group input-group-sm relative">
            <div class="input-group-prepend">
                <div class="text-gray-700 dark:text-dark-175 hidden md:inline-block"
                    v-text="__n(`:count item selected|:count items selected`, selections.length)" />
                <div class="text-gray-700 dark:text-dark-175 md:hidden" v-text="selections.length" />
            </div>

            <data-list-action
                v-if="hasSelections"
                v-for="(action, index) in sortedActions"
                :key="action.handle"
                :action="action"
                :selections="selections.length"
                :errors="errors"
                @selected="run"
            >
                <button
                    slot-scope="{ action, select }"
                    class="input-group-item"
                    :class="{'text-red-500': action.dangerous, 'ltr:rounded-r rtl:rounded-l': index + 1 === sortedActions.length }"
                    @click="select"
                    v-text="__(action.title)" />
            </data-list-action>
        </div>
    </div>

</template>

<script>
import Actions from './Actions';

export default {

    mixins: [Actions],

    inject: ['sharedState'],

    props: {
        context: {
            type: Object,
            default: () => {}
        },
        showAlways: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            actions: [],
        };
    },

    computed: {
        selections() {
            return this.sharedState.selections;
        },

        hasSelections() {
            return this.selections.length > 0;
        },
    },

    watch: {
        selections: 'getActions',
    },

    methods: {
        getActions() {
            if (this.selections.length === 0) {
                this.actions = [];

                return;
            }

            let data = {
                selections: this.selections,
            };

            if (this.context) {
                data.context = this.context;
            }

            this.$axios.post(this.url+'/list', data).then(response => {
                this.actions = response.data;
            });
        },
    },

}
</script>
