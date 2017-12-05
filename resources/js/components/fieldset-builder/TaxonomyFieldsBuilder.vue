<template>

    <div class="taxonomy-fields-builder">

        <div v-if="loading" class="loading loading-basic">
            <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
        </div>

        <table v-if="!loading" class="fields-table bordered-table">
            <thead>
            <tr>
                <th>{{ translate('cp.taxonomy')}}</th>
                <th>{{ translate('cp.handle')}}</th>
                <th>{{ translate('cp.label')}}</th>
                <th class="row-controls"></th>
            </tr>
            </thead>
            <tbody v-el:tbody>
                <tr is="TaxonomyField"
                    v-for="field in fields"
                    :field="field"
                    :taxonomy="getTaxonomyByField(field)"
                    :fieldtype-config="fieldtypeConfig"
                    @selected="taxonomySelected">
                </tr>
            </tbody>
        </table>

    </div>


</template>


<script>
export default {

    components: {
        TaxonomyField: require('./TaxonomyField.vue')
    },


    props: ['fields', 'fieldtypes'],


    data() {
        return {
            loading: true,
            taxonomies: [],
            fieldtypeConfig: null
        }
    },


    ready() {
        this.getTaxonomies();
        this.getTaxonomyFieldtypeConfig();
    },


    methods: {

        getTaxonomies() {
            this.$http.get(cp_url('taxonomies/get')).success((response) => {
                this.loading = false;
                this.taxonomies = response.items;
                this.$nextTick(() => this.enableSorting());
            });
        },

        getTaxonomyFieldtypeConfig() {
            let config = _(this.fieldtypes).findWhere({ name: 'taxonomy' }).config;

            // Taxonomy fields are aware of what taxonomy they are. The user doesn't need
            // to specify it. We also only ever want the suggest mode. No panes.
            config = _(config).reject((c) => {
                return _(['taxonomy', 'mode']).contains(c.name);
            });

            this.fieldtypeConfig = config;
        },

        getTaxonomyByField(field) {
            return _(this.taxonomies).findWhere({ id: field.taxonomy });
        },

        enableSorting() {
            var self = this;

            $(this.$els.tbody).sortable({
                axis: 'y',
                revert: 175,
                placeholder: 'placeholder',
                handle: '.drag-handle',
                forcePlaceholderSize: true,

                start: function(e, ui) {
                    ui.item.data('start', ui.item.index());
                },

                update: function(e, ui) {
                    var start = ui.item.data('start'),
                        end   = ui.item.index();

                    self.fields.splice(end, 0, self.fields.splice(start, 1)[0]);
                }

            });
        }

    }

}
</script>
