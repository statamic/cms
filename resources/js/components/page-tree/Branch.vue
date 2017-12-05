<template>
    <li class="branch" :class="{ draft: !published }">
        <div class="branch-row">
            <div class="page-indent">
            <span :class="{'page-toggle': true, toggleable: hasChildren}" v-on:click="toggle">
                <i v-if="hasChildren" :class="{ 'icon': true, 'icon-chevron-down': true, 'collapsed': collapsed }"></i>
                <i v-if="url == '/'" class="icon icon-home"></i>
            </span>
                <span class="page-move drag-handle" v-if="!home"></span>
                <span class="page-unmovable" v-if="home"></span>
                <span class="indent-arrow" v-if="!home"></span>
            </div>

            <div class="page-text">
                <a :href="editUrl" class="page-title">{{ title }}</a>
                <a :href="editUrl" class="page-url">{{ url }}</a>
            </div>

            <div class="page-extras">
                <div class="page-entries" v-if="hasEntries">
                    <i class="icon icon-documents"></i>
                    <a :href="createEntryUrl">{{ translate('cp.add') }}</a>
                    {{ translate('cp.or') }}
                    <a :href="entriesUrl">{{ translate('cp.edit') }}</a>
                </div>
            </div>

            <div class="branch-meta">
                <div class="page-actions" v-if="can('pages:create') || can('pages:delete')">
                    <a :href="url" :title="url" class="page-action" target="_blank">
                        <i class="icon icon-link"></i>
                    </a>
                    <div class="btn-group page-action action-more">
                        <i class="icon icon-dots-three-vertical" data-toggle="dropdown"></i>
                        <ul class="dropdown-menu">
                            <li v-if="can('pages:create')"><a href="" @click.prevent="createPage">{{ translate('cp.create_page_button') }}</a></li>
                            <li v-if="can('super')">
                                <a href="" @click.prevent="mountCollection" v-if="!hasEntries">{{ translate('cp.mount_collection') }}</a>
                                <a href="" @click.prevent="unmountCollection" v-if="hasEntries">{{ translate('cp.unmount_collection') }}</a>
                            </li>
                            <li v-if="can('pages:create')"><a href="" @click.prevent="duplicatePage">{{ translate('cp.duplicate') }}</a></li>
                            <li v-if="can('pages:create') && can('pages:delete')" class="divider"></li>
                            <li v-if="can('pages:delete')" class="warning"><a href="" @click.prevent="deletePage">{{ translate('cp.delete') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <branches :pages="childPages"
                  :depth="depth + 1"
                  :parent-url="url"
                  :collapsed.sync="collapsed"
                  v-if="!home">
        </branches>
    </li>
</template>

<script>
export default {

    props: {
        branchIndex: Number,
        uuid: String,
        title: String,
        url: String,
        published: {
            type: Boolean,
            default: true
        },
        editUrl: String,
        hasEntries: Boolean,
        entriesUrl: String,
        createEntryUrl: String,
        childPages: {
            type: Array,
            default: function() {
                return [];
            }
        },
        collapsed: Boolean,
        depth: Number,
        home: {
            type: Boolean,
            default: false
        }
    },

    computed: {

        hasChildren: function() {
            return this.childPages.length;
        }

    },

    methods: {

        toggle: function() {
            this.collapsed = !this.collapsed;
        },

        createPage: function() {
            this.$dispatch('pages.create', this.url);
        },

        deletePage: function() {
            var self = this;

            swal({
                type: 'warning',
                title: translate('cp.are_you_sure'),
                text: translate_choice('cp.confirm_delete_page', 1),
                confirmButtonText: translate('cp.yes_im_sure'),
                cancelButtonText: translate('cp.cancel'),
                showCancelButton: true
            }, function() {
                self.$http.post(cp_url('pages/delete'), { uuid: self.uuid }).success(function() {
                    self.$parent.pages.splice(self.branchIndex, 1);

                    this.$dispatch('page.deleted');
                });
            });
        },

        duplicatePage: function() {
            this.$http.post(cp_url('pages/duplicate'), { id: this.uuid }).success((data) => {
                window.location = data.redirect;
            });
        },

        mountCollection: function () {
            this.$dispatch('pages.mount', this.uuid);
        },

        unmountCollection: function () {
            this.$dispatch('pages.unmount', this.uuid);
        }

    }

};
</script>
