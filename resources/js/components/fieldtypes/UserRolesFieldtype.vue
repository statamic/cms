<template>
    <div>
        <loading-graphic v-if="loading" :size="16" :inline="true" />
        <div class="user_roles-fieldtype" v-if="!loading">
            <relate-fieldtype :value="value"
                              :name="name"
                              :config="config"
                              :suggestions-prop="roles"
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
            roles: {}
        };
    },

    computed: {

        canEdit: function() {
            return true; // TODO
            return Vue.can('users:edit-roles');
        },

        selectedRoleNames: function() {
            var self = this;
            return _.map(this.data, function(id) {
                return _.findWhere(self.roles, { value: id }).text;
            });
        }

    },

    methods: {

        getRoles: function() {
            axios.get(cp_url('roles')).then(response => {
                this.roles = response.data.map(role => {
                    return {
                        value: role.id,
                        text: role.title
                    };
                });
                this.loading = false;
            });
        }

    },

    mounted() {
        this.getRoles();
    }

};
</script>
