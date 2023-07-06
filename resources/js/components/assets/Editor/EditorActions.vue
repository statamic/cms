<template>

    <div>
        <data-list-action
            ref="actions"
            v-for="action in actions"
            :key="action.handle"
            :action="action"
            :selections="1"
            :errors="errors"
            @selected="run" />
    </div>

</template>

<script>
import Actions from '../../data-list/Actions.js';

export default {

    mixins: [Actions],

    props: {
        actions: {
            type: Array,
            required: true
        },
        id: {
            type: String,
            required: true
        },
    },

    computed: {
        selections() {
            return [this.id];
        }
    },

    created() {
        this.$events.$on('editor-action-selected', this.actionSelected);
    },

    methods: {

        findActionComponent(handle) {
            return _.find(this.$refs.actions, component => component.action.handle === handle);
        },

        actionSelected(event) {
            this.findActionComponent(event.action).confirming = true;
        },

    }

}
</script>
