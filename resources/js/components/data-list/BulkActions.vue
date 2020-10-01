<template>

    <div v-if="showAlways || hasSelections" class="data-list-bulk-actions">
        <div class="input-group input-group-sm relative z-10">
            <div class="input-group-prepend">
                <div class="text-grey-60"
                    v-text="__n(`:count item selected|:count items selected`, selections.length)" />
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
                    :class="{'text-red': action.dangerous, 'rounded-r': index + 1 === sortedActions.length }"
                    @click="select"
                    v-text="__(action.title)" />
            </data-list-action>
        </div>
    </div>

</template>

<script>
import Actions from './Actions';
import qs from 'qs';

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

            let params = {
                selections: this.selections,
            };

            if (this.context) {
                params.context = this.context;
            }

            let config = {
                params,
                paramsSerializer: params => qs.stringify(params, {arrayFormat: 'brackets'})
            };

            this.$axios.get(this.url, config).then(response => {
                this.actions = response.data;
            });
        },
    },

}
</script>
