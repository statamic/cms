<template>
    <div v-if="showAlways || hasSelections" class="fixed inset-x-0 bottom-1 z-100 flex w-full justify-center">
        <ButtonGroup>
            <Button
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
                <Button variant="primary" @click="select" :text="__(action.title)" />
            </data-list-action>
        </ButtonGroup>
    </div>
</template>

<script>
import Actions from './Actions';
import { Button, ButtonGroup } from '@statamic/ui';

export default {
    mixins: [Actions],

    components: {
        Button,
        ButtonGroup,
    },

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
        selections: {
            deep: true,
            handler() {
                this.getActions();
            }
        },
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
