<template>
    <modal :show.sync="show" :saving="saving" :loading="loading" class="modal-small">
        <template slot="header">{{ translate('cp.mount_collection') }}</template>

        <template slot="body">
            <ul class="chooser">
                <li v-for="collection in collections">
                    <a href='' @click.prevent="mount(collection.value)">{{ collection.text }}</a>
                </li>
            </ul>
        </template>

        <template slot="footer">
            <button type="button" class="btn" @click="cancel">{{ translate('cp.cancel') }}</button>
        </template>
    </modal>
</template>

<script>
    export default {

        data: function() {
            return {
                id: null,
                show: false,
                saving: false,
                loading: true,
                fieldsets: []
            }
        },

        events: {
            'pages.mount': function(id) {
                this.loading = true;
                this.show = true;
                this.id = id;
                this.getCollections();
            },

            'pages.unmount': function(id) {
                this.id = id;
                this.mount(null);
            }
        },

        methods: {
            getCollections: function() {
                this.$http.get(cp_url('collections/get'), function(data) {
                    let collections = [];

                    _.each(data.items, function(collection) {
                        collections.push({
                            value: collection.id,
                            text: collection.title
                        });
                    });

                    this.collections = collections;
                    this.loading = false;
                });
            },

            cancel: function() {
                this.show = false;
            },

            mount: function(collection) {
                this.saving = true;
                const id = this.id;

                this.$http.post(cp_url('pages/mount'), { id, collection }).success((response) => {
                    window.location = window.location;
                });
            }
        }

    };
</script>