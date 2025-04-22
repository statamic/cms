<template>
    <div v-if="showAlways || hasSelections" class="fixed inset-x-0 bottom-1 w-full flex justify-center z-100">
        <ui-button-group>
            <ui-button
                variant="primary"
                class="text-gray-400!"
                :text="__n(`:count item selected|:count items selected`, selections.length)"
            />
            <data-list-action
                v-if="hasSelections"
                v-for="(action, index) in sortedActions"
                :key="action.handle"
                :action="action"
                :selections="selections.length"
                :errors="errors"
                @selected="run"
                v-slot="{ action, select }"
            >
                <ui-button
                    variant="primary"
                    @click="select"
                    :text="__(action.title)"
                />
            </data-list-action>
        </ui-button-group>
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
            default: () => {},
        },
        showAlways: {
            type: Boolean,
            default: false,
        },
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

            this.$axios.post(this.url + '/list', data).then((response) => {
                this.actions = response.data;
            });
        },
    },
};
</script>
