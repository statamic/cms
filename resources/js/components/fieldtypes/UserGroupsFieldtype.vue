<template>
    <div>
        <div v-if="loading" class="loading loading-basic">
            <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
        </div>

        <div v-if="!loading && !canEdit">
            <template v-for="group in selectedGroupNames">
                {{ group }}<template v-if="$index !== selectedGroupNames.length-1">,</template>
            </template>
        </div>

        <div class="user_groups-fieldtype" v-if="!loading && canEdit">
            <relate-fieldtype :data.sync="data"
                              :name="name"
                              :config="config"
                              :suggestions-prop="groups"
                              v-ref:relate>
            </relate-fieldtype>
        </div>
    </div>
</template>

<script>
import AdaptsRelateFieldtype from './AdaptsRelateFieldtype.vue';

export default {

    mixins: [AdaptsRelateFieldtype],

    data: function() {
        return {
            loading: true,
            groups: {}
        };
    },

    computed: {

        canEdit: function() {
            return Vue.can('super');
        },

        selectedGroupNames: function() {
            var self = this;
            return _.map(this.data, function(id) {
                return _.findWhere(self.groups, { value: id }).text;
            });
        }

    },

    methods: {

        getGroups: function() {
            this.$http.get(cp_url('users/groups/get'), function(data) {
                var groups = [];
                _.each(data.items, function(group) {
                    groups.push({
                        value: group.id,
                        text: group.title
                    });
                });

                this.groups = groups;
                this.loading = false;
            });
        }

    },

    ready: function() {
        this.getGroups();
    }
};
</script>
