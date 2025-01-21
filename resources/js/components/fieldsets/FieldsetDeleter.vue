<template>
    <confirmation-modal
        v-if="deleting"
        :title="modalTitle"
        :buttonText="__('Delete')"
        :danger="true"
        :disabled="Object.keys(resource.imported_by).length > 0"
        @confirm="confirmed"
        @cancel="cancel"
    >
        <template slot="body">
            <template v-if="Object.keys(resource.imported_by).length > 0">
                <p class="mb-2">{{ __(`Before you can delete this fieldset, you need to remove references to it in blueprints and fieldsets:`) }}</p>

                <div v-for="(items, group) in resource.imported_by">
                    <h3 class="little-heading rtl:pr-0 ltr:pl-0 mb-2">{{ group }}</h3>

                    <ul class="list-disc rtl:pr-4 ltr:pl-4">
                        <li
                            v-for="item in items"
                            :key="item.handle"
                            class="font-mono text-sm mb-1.5"
                            v-text="item.title"
                        ></li>
                    </ul>
                </div>
            </template>

            <template v-else>
                <p>{{ __('Are you sure you want to delete this item?') }}</p>
            </template>
        </template>
    </confirmation-modal>
</template>

<script>
export default {

    props: {
        resource: {
            type: Object
        },
        resourceTitle: {
            type: String
        },
        route: {
            type: String,
        },
        redirect: {
            type: String
        },
        reload: {
            type: Boolean
        }
    },

    data() {
        return {
            deleting: false,
            redirectFromServer: null,
        }
    },

    computed: {
        title() {
            return data_get(this.resource, 'title', this.resourceTitle);
        },

        modalTitle() {
            return __('Delete :resource', {resource: __(this.title)});
        },

        deleteUrl() {
            let url = data_get(this.resource, 'delete_url', this.route);
            if (! url) console.error('ResourceDeleter cannot find delete url');
            return url;
        },

        redirectUrl() {
            return this.redirect || this.redirectFromServer;
        },
    },

    methods: {
        confirm() {
            this.deleting = true;
        },

        confirmed() {
            this.$axios.delete(this.deleteUrl)
                .then(response => {
                    this.redirectFromServer = data_get(response, 'data.redirect');
                    this.success();
                })
                .catch(() => {
                    this.$toast.error(__('Something went wrong'));
                });
        },

        success() {
            if (this.redirectUrl) {
                location.href = this.redirectUrl;
                return;
            }

            if (this.reload) {
                location.reload();
                return;
            }

            this.$toast.success(__('Deleted'));
            this.$emit('deleted');
        },

        cancel() {
            this.deleting = false;
        }
    }
}
</script>
