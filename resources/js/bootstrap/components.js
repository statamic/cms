import Vue from 'vue'

// Core
Vue.component('asset-manager', require('../components/assets/AssetManager.vue'));
Vue.component('asset-browser', require('../components/assets/Browser/Browser.vue'));
Vue.component('updater', require('../components/Updater.vue'));
Vue.component('updates-badge', require('../components/UpdatesBadge.vue'));
Vue.component('composer-output', require('../components/ComposerOutput.vue'));

// Publish
Vue.component('publish-container', require('../components/publish/Container.vue'));
Vue.component('publish-fields', require('../components/publish/Fields.vue'));
Vue.component('publish-field-meta', require('../components/publish/FieldMeta.vue'));
Vue.component('publish-sections', require('../components/publish/Sections.vue'));
Vue.component('publish-validation-errors', require('../components/publish/ValidationErrors'));
Vue.component('form-group', require('../components/publish/FormGroup.vue'));

Vue.component('live-preview', require('../components/live-preview/LivePreview.vue'));
Vue.component('live-preview-popout', require('../components/live-preview/Popout.vue'));

Vue.component('EntryPublishForm', require('../components/entries/PublishForm.vue'));

// Data List
Vue.component('data-list', require('../components/data-list/DataList.vue'));
Vue.component('data-list-search', require('../components/data-list/Search.vue'));
Vue.component('data-list-bulk-actions', require('../components/data-list/BulkActions.vue'));
Vue.component('data-list-inline-actions', require('../components/data-list/InlineActions.vue'));
Vue.component('data-list-column-picker', require('../components/data-list/ColumnPicker.vue'));
Vue.component('data-list-toggle-all', require('../components/data-list/ToggleAll.vue'));
Vue.component('data-list-pagination', require('../components/data-list/Pagination.vue'));
Vue.component('data-list-filters', require('../components/data-list/Filters.vue'));
Vue.component('data-list-table', require('../components/data-list/Table.vue'));

// Resource Type Lists
Vue.component('entry-list', require('../components/entries/Listing.vue'));
Vue.component('collection-list', require('../components/collections/Listing.vue'));
Vue.component('asset-container-list', require('../components/AssetContainerList.vue'));
Vue.component('addon-list', require('../components/AddonList.vue'));
Vue.component('addon-details', require('../components/AddonDetails.vue'));

// Widgets
Vue.component('collection-widget', require('../components/entries/Widget.vue'));

// Reusable
Vue.component('svg-icon', require('../components/SvgIcon.vue'));
Vue.component('file-icon', require('../components/FileIcon.vue'));
Vue.component('loading-graphic', require('../components/LoadingGraphic.vue'));
Vue.component('dropdown-list', require('../components/DropdownList'));
Vue.component('validation-errors', require('../components/ValidationErrors'));
Vue.component('slugify', require('../components/Slugify.vue'));
Vue.component('element-container', require('../components/ElementContainer.vue'));
Vue.component('avatar', require('../components/Avatar.vue'));

// Recursive
Vue.component('role-permission-tree', require('../components/roles/PermissionTree.vue'));

// Modals
Vue.component('modal', require('../components/Modal.vue'));
Vue.component('confirmation-modal', require('../components/modals/ConfirmationModal.vue'));
Vue.component('favorite-creator', require('../components/FavoriteCreator.vue'));
Vue.component('keyboard-shortcuts-modal', require('../components/modals/KeyboardShortcutsModal.vue'));
// Vue.component('modal-dialog', require('../components/ModalDialog.vue'));

Vue.component('stack', require('../components/stacks/Stack.vue'));
Vue.component('stack-test', require('../components/stacks/StackTest.vue'));

Vue.component('pane', require('../components/panes/Pane.vue'));
