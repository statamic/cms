<template>

    <div class="replicator">

        <replicator-set
            v-for="(set, index) in values"
            :key="`set-${index}`"
            :index="index"
            :values="set"
            :config="setConfig(set.type)"
            @updated="updated"
        />

        <div class="set-buttons">
            <button
                v-for="set in setConfigs"
                :key="set.handle"
                class="btn mr-1 mb-1"
                @click.prevent="addSet(set.handle)"
            >
                {{ set.display }} <i class="icon icon-plus icon-right"></i>
            </button>
        </div>

    </div>

</template>

<script>
import ReplicatorSet from './Set.vue';

export default {

    mixins: [Fieldtype],

    components: { ReplicatorSet },

    data() {
        return {
            values: _.clone(this.value || []),
        }
    },

    computed: {

        setConfigs() {
            return this.config.sets;
        }

    },

    methods: {

        setConfig(handle) {
            return _.find(this.setConfigs, { handle });
        },

        updated(index, set) {
            this.values.splice(index, 1, set);
        },

        addSet(handle, index) {
            let newSet = { type: handle };

            // Get nulls for all the set's fields so Vue can track them more reliably.
            _.each(this.setConfig(handle).fields, field => {
                newSet[field.handle] = field.default
                    // || Statamic.fieldtypeDefaults[field.type] // TODO: inject fieldtype default here.
                    || null;
            });

            if (index === undefined) {
                index = this.values.length;
            }

            this.values.splice(index, 0, newSet);
        },

    },

    watch: {

        values(values) {
            this.$emit('updated', values);
        }

    }

}
</script>
