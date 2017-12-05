var Permission = {
    name: 'permission',

    template: `
        <div>
            <input type="checkbox" :id="name" name="permissions[]" v-model="selectedPermissions" :value="name" :disabled="disabled" />
            <label :for="name">{{ label }}</label>

            <ul v-if="children" :class="{ 'disabled': !selected }">
                <li v-for="(key, value) in children">
                    <permission :key="key"
                                :value="value"
                                :selected-permissions="selectedPermissions"
                                :disabled="!selected"
                    ></permission>
                </li>
            </ul>
        </div>
    `,

    props: ['key', 'value', 'selectedPermissions', 'disabled'],

    computed: {
        name: function () {
            return this.hasChildren ? this.key : this.value;
        },
        label: function () {
            var key = this.name;

            var matches = key.match(/^.*\:(.*)\:.*$/);

            if (matches) {
                key = matches[0].replace(':'+matches[1]+':', ':*:');
            }

            return translate('permissions.'+key);
        },
        hasChildren: function () {
            return typeof this.value !== 'string';
        },
        children: function () {
            if (! this.hasChildren) {
                return null;
            }
            return this.value;
        },
        selected: function () {
            return _.contains(this.selectedPermissions, this.name);
        }
    },

    watch: {
        disabled: function (disabled) {
            if (disabled) {
                var i = _.indexOf(this.selectedPermissions, this.name);
                if (i !== -1) {
                    this.selectedPermissions.splice(i, 1);
                }
            }
        }
    }
};

module.exports = {

    components: {
        Permission
    },

    props: ['permissions', 'selected', 'titles'],

    computed: {
        superSelected: function () {
            return _.indexOf(this.selected, 'super') !== -1;
        }
    },

    methods: {
        startsWith: function (haystack, needle) {
            return !haystack.indexOf(needle);
        },

        title: function (string) {
            var parts = string.split(':');
            return this.titles[parts[0]][parts[1]];
        },

        label: function (group) {
            if (this.startsWith(group, 'collections')) {
                return translate_choice('cp.collections', 1) + ': ' + this.title(group);
            }

            if (this.startsWith(group, 'taxonomies')) {
                return translate_choice('cp.taxonomies', 1) + ': ' + this.title(group);
            }

            if (this.startsWith(group, 'globals')) {
                return translate_choice('cp.globals', 1) + ': ' + this.title(group);
            }

            if (this.startsWith(group, 'assets')) {
                return translate_choice('cp.asset_containers', 2) + ': ' + this.title(group);
            }

            return translate('permissions.group_'+group);
        }
    }

};
