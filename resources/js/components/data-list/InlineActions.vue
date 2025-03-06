<template>
    <div>
        <data-list-action
            v-for="action in sortedActions"
            :key="action.handle"
            :action="action"
            :selections="1"
            :errors="errors"
            :is-dirty="isDirty"
            @selected="run"
            v-slot="{ action, select }"
        >
            <div>
                <button v-text="__(action.title)" :class="{ warning: action.dangerous }" @click="select" />
            </div>
        </data-list-action>
    </div>
</template>

<script>
import Actions from './Actions';

export default {
    mixins: [Actions],

    props: {
        actions: Array,
        item: { required: true },
        isDirty: { type: Boolean, default: false },
    },

    computed: {
        selections() {
            return [this.item];
        },
    },
};
</script>
