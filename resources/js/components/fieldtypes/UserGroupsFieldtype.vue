<template>
    <div>
        <loading-graphic v-if="loading" :size="16" :inline="true" />
        <div class="user_groups-fieldtype" v-if="!loading">
            <relate-fieldtype :value="value"
                              :name="name"
                              :config="config"
                              :suggestions-prop="groups"
                              :disabled="!canEdit"
                              ref="relate"
                              @updated="update($event)">
            </relate-fieldtype>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
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
            return true; // TODO
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
            axios.get(cp_url('user-groups')).then(response => {
                this.groups = response.data.map(group => {
                    return {
                        value: group.id,
                        text: group.title
                    };
                });
                this.loading = false;
            });
        }

    },

    mounted() {
        this.getGroups();
    }
};
</script>
