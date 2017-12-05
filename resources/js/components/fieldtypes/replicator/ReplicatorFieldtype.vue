<template>
    <div class="replicator-fieldtype-wrapper relative">

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
export default {

    mixins: [Fieldtype],

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

        this.accordionMode = this.getAccordionModeFromStorage();

        if (this.accordionMode) this.collapseAll();

        this.sortable();
        this.bindChangeWatcher();
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

        setConfig: function(type) {
            return _.findWhere(this.config.sets, { name: type });
        },

        deleteSet: function(index) {
            this.data.splice(index, 1);
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

        expandAll: function() {
            _.each(this.$refs.set, set => set.expand(true));
            this.setAccordionMode(false);
        },

        collapseAll: function () {
            _.each(this.$refs.set, set => set.collapse(true));
            this.setAccordionMode(true);
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

        getAccordionModeFromStorage() {
            let mode = this.accordionMode;
            const stored = localStorage.getItem('statamic.replicator.accordion');

            if (stored === 'true') {
                mode = true;
            } else if (stored === 'false') {
                mode = false;
            }

            return mode;
        },

        setAccordionMode(mode) {
            this.accordionMode = mode;
            localStorage.setItem('statamic.replicator.accordion', mode);
        },

        getReplicatorPreviewText() {
            return _.map(this.$refs.set, set => set.collapsedPreview).join(', ');
        }
    }
};
</script>
