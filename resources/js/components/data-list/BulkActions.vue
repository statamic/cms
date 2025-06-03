<template>
    <Motion
        v-if="hasSelections"
        layout
        class="absolute inset-x-0 bottom-6 z-100 flex w-full justify-center"
        :initial="{ y: 100, opacity: 0 }"
        :animate="{ y: 0, opacity: 1 }"
        :transition="{ duration: 0.2, ease: 'easeInOut' }"
    >
        <ButtonGroup>
            <Button
                class="text-blue-500!"
                :text="__n(`Deselect :count item|Deselect all :count items`, selections.length)"
                @click="deselectAll"
            />
            <data-list-action
                v-for="(action, index) in sortedActions"
                :key="action.handle"
                :action="action"
                :selections="selections.length"
                :errors="errors"
                @selected="run"
                v-slot="{ action, select }"
            >
                <Button @click="select" :text="__(action.title)" />
            </data-list-action>
        </ButtonGroup>
    </Motion>
</template>

<script>
import Actions from './Actions';
import { Button, ButtonGroup } from '@statamic/ui';
import { Motion } from 'motion-v';

export default {
    mixins: [Actions],

    components: {
        Button,
        ButtonGroup,
        Motion,
    },

    inject: ['sharedState'],

    props: {
        context: { type: Object, default: () => {} }
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

        deselectAll() {
            this.sharedState.selections = [];
        },
    },
};
</script>
