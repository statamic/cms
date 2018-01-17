<template>
    <div class="replicator replicator-fieldtype-wrapper relative">

        <div class="replicator-sets">
            <replicator-set
                v-for="(index, set) in data"
                v-ref:set
                :parent-name="name"
                :data="set"
                :index="index"
                :config="setConfig(set.type)"
                @deleted="deleteSet"
                @expanded="setExpanded"
            >
                <template slot="expand-collapse">
                    <li><a @click="collapseAll">{{ translate('cp.collapse_all') }}</a></li>
                    <li><a @click="expandAll">{{ translate('cp.expand_all') }}</a></li>
                </template>

                <template slot="add-sets">
                    <li v-for="setConfig in config.sets">
                        <a @click.prevent="addSet(setConfig.name, index + 1)">
                            <i class="icon icon-add-to-list"></i>
                            {{ setConfig.display || setConfig.name }}
                        </a>
                    </li>
                </template>
            </replicator-set>
        </div>

        <button type="button" class="btn btn-default mr-8 mb-8" v-for="set in config.sets" v-on:click="addSet(set.name)" v-tip :tip-text="set.instructions">
			{{ set.display || set.name }}<i class="icon icon-plus icon-right"></i>
        </button>
    </div>
</template>

<script>
import Replicator from './Replicator';

export default {

    mixins: [Replicator, Fieldtype],

    components: {
        ReplicatorSet: require('./ReplicatorSet.vue')
    },

    data: function() {
        return {
            blank: {},
            sortableOptions: {},
            autoBindChangeWatcher: false,
            changeWatcherWatchDeep: false,
            accordionMode: true
        };
    },

    computed: {
        hasData: function() {
            return this.data !== null && this.data.length;
        }
    },

    ready: function() {
        // Initialize with an empty array if there's no data.
        if (! this.data) {
            this.data = [];
        }

        this.bindChangeWatcher();
        this.sortable();
    },

    methods: {

        sortable: function() {
            var self = this;
            var start = '';

            $(this.$el).children('.replicator-sets').sortable({
                axis: "y",
                revert: 175,
                placeholder: 'stacked-placeholder',
                handle: '.drag-handle',
                forcePlaceholderSize: true,
                start: function(e, ui) {
                    start = ui.item.index();
                    ui.placeholder.height(ui.item.height());
                },
                update: function(e, ui) {
                    var end  = ui.item.index();

                    // Make a local copy and reorder
                    var data = JSON.parse(JSON.stringify(self.data));
                    data.splice(end, 0, data.splice(start, 1)[0]);

                    self.data = data;
                }
            });
        },

        addSet: function(type, index) {
            var newSet = { type: type };

            // Get nulls for all the set's fields so Vue can track them more reliably.
            var set = this.setConfig(type);
            _.each(set.fields, function(field) {
                newSet[field.name] = field.default || Statamic.fieldtypeDefaults[field.type] || null;
            });

            if (index === undefined) {
                index = this.data.length;
            }

            this.data.splice(index, 0, newSet);

            if (this.accordionMode) this.collapseAllExcept(index);

            this.sortable();

            this.$nextTick(() => this.$refs.set[index].focus());
        },

        collapseAllExcept(except) {
            _.map(this.$refs.set, set => {
                if (set.index !== except) set.collapse();
            });
        },

        setExpanded(set, all) {
            // The 'all' variable will be true if the set was expanded due to a expandAll()
            // method call. In that case, we don't want to collapse the other sets.
            if (all) return;

            if (this.accordionMode) this.collapseAllExcept(set.index);
        },

        getReplicatorPreviewText() {
            return _.map(this.$refs.set, set => set.collapsedPreview).join(', ');
        }
    }
};
</script>
