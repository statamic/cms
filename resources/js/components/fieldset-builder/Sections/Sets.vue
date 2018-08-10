<template>
    <div class="field-nested field-sets" ref="sets">
        <replicator-set
            v-for="(i, set) in sets"
            v-ref=sets
            :set="set"
            :set-index="i"
            :section="section"
            :fieldtypes="fieldtypes"
            :parent-key="parentKey"
            @deleted="remove"
        ></replicator-set>
    </div>
</template>

<script>
import ReplicatorSet from './Set.vue';
import { Sortable } from '@shopify/draggable';

export default {

    components: {
        ReplicatorSet
    },

    props: ['fieldtypes', 'sets', 'section', 'parentKey'],

    mounted() {
        this.sets = this.sets || [];

        this.makeSetsSortable();
    },

    methods: {

        updateFieldWidths() {
            this.$nextTick(() => {
                _.each(this.$refs.sets, component => { component.updateFieldWidths() });
            });
        },

        add() {
            const count = this.sets.length + 1;
            this.sets.push({
                display: `Set ${count}`,
                name: `set_${count}`,
                id: `set_${count}`,
                instructions: null,
                fields: [],
                isNew: true
            });

            this.$notify.success(translate('cp.set_added'));

            this.$nextTick(() => this.$refs.sets[count-1].focus());
        },

        remove(index) {
            if (! confirm(translate('cp.are_you_sure'))) {
                return;
            }

            this.sets.splice(index, 1);
        },

        makeSetsSortable() {
            const container = this.$refs.sets;
            const sortableFields = new Sortable(container, {
                draggable: `.field-set--${this.parentKey}`,
                handle: `.set-drag-handle--${this.parentKey}`,
                appendTo: container,
                mirror: { constrainDimensions: true },
            }).on('sortable:stop', e => {
                this.sets.splice(e.newIndex, 0, this.sets.splice(e.oldIndex, 1)[0]);
            })
        }

    }

}
</script>
