<template>
    <li class="branch" :class="{ draft: !published }" :data-depth="depth">
        <div class="branch-row w-full flex items-center">

            <div v-if="home" class="w-6 home-handle">
                <i class="icon icon-home mx-auto opacity-25"></i>
            </div>

            <div v-if="!home && sortable" :class="['page-move drag-handle w-6 h-full', {'cursor-not-allowed opacity-50': isSingleTopLevelPage}]"></div>

            <div class="flex items-center flex-1 p-1">

                <div class="page-text">
                    <a :href="editUrl" class="page-title">{{ title }}</a>
                    <a :href="editUrl" class="page-url">{{ url }}</a>
                </div>

                <div class="w-6 opacity-25 hover:opacity-50 page-toggle-container ml-px" v-if="hasChildren">
                    <div :class="{'page-toggle': true, toggleable: hasChildren}" v-on:click="toggle">
                        <i :class="{ 'icon': true, 'icon-chevron-down': true, 'collapsed': collapsed }"></i>
                    </div>
                </div>

                <div class="has-collection flex items-center pl-2" v-if="hasEntries">
                    <svg-icon name="entries" class="pr-1 text-grey h-6 w-6"></svg-icon>
                    <a :href="createEntryUrl">{{ __('Add') }}</a>
                    <span class="mx-sm text-grey text-2xs">{{ __('or') }}</span>
                    <a :href="entriesUrl">{{ __('Edit') }}</a>
                </div>
            </div>

            <div class="branch-meta flex items-center pr-1">
                <div class="page-actions" v-if="can('pages:create') || can('pages:delete')">
                    <a :href="url" :title="url" class="page-action text-grey-dark px-sm bloop" target="_blank">
                        <svg-icon name="visit" class="opacity-25 hover:opacity-75 h-4 w-4"></svg-icon>
                    </a>
                    <div class="btn-group page-action action-more px-sm">
                        <i class="icon icon-dots-three-vertical opacity-25 hover:opacity-75" data-toggle="dropdown"></i>
                        <ul class="dropdown-menu">
                            <li v-if="can('pages:create')"><a href="" @click.prevent="createPage">{{ __('Create Page') }}</a></li>
                            <li v-if="can('super')">
                                <a href="" @click.prevent="mountCollection" v-if="!hasEntries">{{ __('Mount Collection') }}</a>
                                <a href="" @click.prevent="unmountCollection" v-if="hasEntries">{{ __('Unmount Collection') }}</a>
                            </li>
                            <li v-if="can('pages:create')"><a href="" @click.prevent="duplicatePage">{{ __('Duplicate') }}</a></li>
                            <li v-if="can('pages:create') && can('pages:delete')" class="divider"></li>
                            <li v-if="can('pages:delete')" class="warning"><a href="" @click.prevent="deletePage">{{ __('Delete') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <branches :pages="childPages"
                  :depth="depth + 1"
                  :parent-url="url"
                  :collapsed="collapsed"
                  :sortable="sortable"
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
        },
        sortable: Boolean
    },

    computed: {

        hasChildren: function() {
            return this.childPages.length;
        },

        isSingleTopLevelPage() {
            return this.$parent.pages.length === 1 && this.depth === 1;
        }

    },

    methods: {

        toggle: function() {
            this.collapsed = !this.collapsed;
        },

        createPage: function() {
            this.$events.$emit('pages.create', this.url);
        },

        deletePage: function() {
            var self = this;

            swal({
                type: 'warning',
                title: __('Are you sure?'),
                text: __n('cp.confirm_delete_page', 1),
                confirmButtonText: __('Yes, I\'m sure'),
                cancelButtonText: __('Cancel'),
                showCancelButton: true
            }, function() {
                self.$http.post(cp_url('pages/delete'), { uuid: self.uuid }).success(function() {
                    self.$parent.pages.splice(self.branchIndex, 1);

                    this.$events.$emit('page.deleted');
                });
            });
        },

        duplicatePage: function() {
            this.$http.post(cp_url('pages/duplicate'), { id: this.uuid }).success((data) => {
                window.location = data.redirect;
            });
        },

        mountCollection: function () {
            this.$events.$emit('pages.mount', this.uuid);
        },

        unmountCollection: function () {
            this.$events.$emit('pages.unmount', this.uuid);
        }

    }

};
</script>
