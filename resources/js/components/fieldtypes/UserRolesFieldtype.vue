<template>
    <div>
        <div v-if="loading" class="loading loading-basic">
            <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
        </div>
        <p v-if="!loading && !canEdit" class="form-control-static">
            <template v-for="role in selectedRoleNames">
                {{ role }}<template v-if="$index !== selectedRoleNames.length-1">,</template>
            </template>
        </p>
        <div class="user_roles-fieldtype" v-if="!loading && canEdit">
            <relate-fieldtype :data.sync="data"
                              :name="name"
                              :config="config"
                              :suggestions-prop="roles"
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
            roles: {}
        };
    },

    computed: {

        canEdit: function() {
            return Vue.can('super');
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
            this.$http.get(cp_url('users/roles/get'), function(data) {
                var roles = [];
                _.each(data.items, function(role) {
                    roles.push({
                        value: role.id,
                        text: role.title
                    });
                });

                this.roles = roles;
                this.loading = false;
            });
        }

    },

    ready: function() {
        this.getRoles();
    }

};
</script>
