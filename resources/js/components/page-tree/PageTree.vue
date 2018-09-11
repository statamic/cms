<script>

import Branch from './Branch.vue';
import Branches from './Branches.vue';
import CreatePage from './CreatePage.vue';
import MountCollection from './MountCollection.vue';
import HasLocaleSelector from '../HasLocaleSelector';
import HasShowDraftsSelector from '../HasShowDraftsSelector';

export default {

    mixins: [HasShowDraftsSelector, HasLocaleSelector],

    components: {
        Branches,
        Branch,
        CreatePage,
        MountCollection,
    },

    props: ['url', 'saveUrl', 'structure'],

    data: function() {
        return {
            loading: true,
            saving: false,
            changed: false,
            showUrls: false,
            show: "urls",
            pages: [],
        }
    },

    computed: {

        homeEditUrl() {
            let url = cp_url('pages/edit');

            if (this.locale !== Object.keys(Statamic.locales)[0]) {
                url += '?locale=' + this.locale;
            }

            return url;
        },

        hasChildren() {
            return _.some(this.pages, page => page.items.length);
        },

        isSortable() {
            return Vue.can(`structures:${this.structure}:reorder`);
        }

    },

    mounted() {
        this.getPages();
        this.bindLocaleWatcher();
        this.bindShowDraftsWatcher();

        this.$mousetrap.bind('mod+s', (e) => {
            e.preventDefault();
            this.save();
        });
    },

    methods: {

        getPages: function() {
            this.pages = [];
            this.loading = true;
            var url = this.url + '?locale='+this.locale+'&drafts='+(this.showDrafts ? 1 : 0);

            this.axios.get(url)
                .then(response => {
                    this.pages = response.data.pages;
                    this.loading = false;
                    this.$nextTick(function() {
                        this.initSortable();
                    });
            });
        },

        initSortable: function() {
            if (! this.isSortable) {
                return;
            }

            var self = this;
            var draggedIndex, draggedPage, draggedInstance;

            var placeholder = `
                    <li class="branch branch-placeholder">
                        <div :class="'branch-row w-full flex items-center depth-' + depth">
                            <div class="page-move drag-handle w-6 h-full"></div>
                            <div class="flex p-1 items-center flex-1">
                                <div class="page-text">&nbsp;</div>
                            </div>
                        </div>
                    </li>`;

            $(this.$el).find('.page-tree > ul + ul').nestedSortable({
                containerSelector: 'ul',
                handle: '.drag-handle',
                placeholderClass: 'branch-placeholder',
                placeholder: placeholder,
                bodyClass: 'page-tree-dragging',
                draggedClass: 'branch-dragged',
                onMousedown: function ($item, _super, event) {
                    // Prevent dragging a lone top level page.
                    var branch = $item[0].__vue__;
                    var depth = parseInt($item[0].dataset.depth);
                    if (branch.$parent.pages.length === 1 && depth === 1) return false;
                    return true;
                },
                onDragStart: function($item, container, _super, event) {
                    // Grab the original page we're dragging now so we can move it later.
                    var branch = $item[0].__vue__;
                    draggedInstance = branch;
                    draggedIndex = branch.branchIndex;
                    draggedPage = branch.$parent.pages[draggedIndex];

                    // Let the plugin continue
                    _super($item, container);
                },
                onDrag: function($item, container, _super, event) {
                    // Update the placeholder template to show the page name.
                    $('.branch-placeholder').find('.page-text').text(draggedPage.title);
                    _super($item, container);
                },
                onDrop: function($item, container, _super, event) {
                    self.$refs.click.play();
                    self.changed = true;

                    // Remove the page from its original place
                    draggedInstance.$parent.pages.splice(draggedIndex, 1);

                    // Get the drop position
                    var dropIndex = $item.index();
                    var parentInstance = $item.parent()[0].__vue__;

                    // Update the page to use the new parent's url (recursively)
                    draggedPage = self.updateDroppedUrl(draggedPage, parentInstance.$parent.url);

                    // Get the new page's position and inject it into the data
                    parentInstance.pages.splice(dropIndex, 0, draggedPage);

                    // Force the Vue component to reload itself
                    var pages = self.pages;
                    self.pages = [];
                    self.$nextTick(function() {
                        self.pages = pages;
                    });

                    // Let the plugin continue
                    _super($item, container);
                }
            });
        },

        updateDroppedUrl: function(page, url) {
            var self = this;

            url = url || '';

            page.url = url + '/' + page.slug;

            page.items = _.map(page.items, function(child) {
                return self.updateDroppedUrl(child, page.url);
            });

            return page;
        },

        expandAll: function() {
            this.$refs.card_set.play();
            this.toggleAll(false);
        },

        collapseAll: function() {
            this.$refs.card_drop.play();
            this.toggleAll(true);
        },

        toggleAll: function(collapsed, pages) {
            var self = this;

            pages = pages || self.pages;

            _.each(pages, function(page) {
                Vue.set(page, 'collapsed', collapsed);
                if (page.items.length) {
                    self.toggleAll(collapsed, page.items);
                }
            });
        },

        toggleUrls: function() {
            this.showUrls = !this.showUrls;

            if (this.showUrls) {
                this.show = "titles";
            } else {
                this.show = "urls";
            }
        },

        save: function() {
            this.saving = true;

            let pages = JSON.parse(JSON.stringify(this.pages));
            pages = this.updateOrderIndexes(pages);

            this.axios.patch(this.saveUrl, { pages: pages })
            .then(response => {
                this.getPages();
                this.changed = false;
                this.saving = false;
                this.$events.$emit('setFlashSuccess', translate('cp.pages_reordered'))
            });
        },

        updateOrderIndexes: function(pages) {

            return _.map(pages, (item, i) => {
                // Recursively iterate over any children
                if (item.items.length) {
                    item.items = this.updateOrderIndexes(item.items);
                }

                item.order = i++; // We need non-zero-based indexes

                return item;
            });
        },

        createPage: function(parent) {
            this.$events.$emit('pages.create', parent);
        },

        onShowDraftsChanged() {
            this.getPages();
        },

        onLocaleChanged() {
            this.getPages();
        }

    },

    events: {
        'pages.create': function(parent) {
            this.$events.$emit('pages.create', parent);
        },
        'pages.mount': function(id) {
            this.$events.$emit('pages.mount', id);
        },
        'pages.unmount': function(id) {
            this.saving = true;
            this.$events.$emit('pages.unmount', id);
        },
        'page.deleted': function () {
            if (this.pages.length > 1) {
                return;
            }

            $(this.$el).find('.page-tree > ul + ul').nestedSortable('destroy');
        }
    },

    watch: {
        changed(changed) {
            this.$events.$emit('changesMade', changed);
        }
    }

};
</script>
