import Vue from 'vue'
import vSelect from 'vue-select'

// Third Party
Vue.component('v-select', vSelect)

// Core
Vue.component('asset-manager', require('../components/assets/AssetManager.vue').default);
Vue.component('asset-browser', require('../components/assets/Browser/Browser.vue').default);
Vue.component('updates-badge', require('../components/UpdatesBadge.vue').default);
Vue.component('composer-output', require('../components/ComposerOutput.vue').default);

// Publish
Vue.component('publish-container', require('../components/publish/Container.vue').default);
Vue.component('publish-form', require('../components/publish/PublishForm.vue').default);
Vue.component('publish-fields', require('../components/publish/Fields.vue').default);
Vue.component('publish-fields-container', require('../components/publish/FieldsContainer.vue').default);
Vue.component('publish-field', require('../components/publish/Field.vue').default);
Vue.component('publish-field-meta', require('../components/publish/FieldMeta.vue').default);
Vue.component('configure-sections', require('../components/configure/Sections.vue').default);
Vue.component('publish-sections', require('../components/publish/Sections.vue').default);
Vue.component('publish-validation-errors', require('../components/publish/ValidationErrors').default);
Vue.component('form-group', require('../components/publish/FormGroup.vue').default);

Vue.component('live-preview', require('../components/live-preview/LivePreview.vue').default);
Vue.component('live-preview-popout', require('../components/live-preview/Popout.vue').default);

Vue.component('EntryPublishForm', require('../components/entries/PublishForm.vue').default);
Vue.component('TermPublishForm', require('../components/terms/PublishForm.vue').default);
Vue.component('UserPublishForm', require('../components/users/PublishForm.vue').default);

// Data List
Vue.component('data-list', require('../components/data-list/DataList.vue').default);
Vue.component('data-list-table', require('../components/data-list/Table.vue').default);
Vue.component('data-list-search', require('../components/data-list/Search.vue').default);
Vue.component('data-list-bulk-actions', require('../components/data-list/BulkActions.vue').default);
Vue.component('data-list-inline-actions', require('../components/data-list/InlineActions.vue').default);
Vue.component('data-list-column-picker', require('../components/data-list/ColumnPicker.vue').default);
Vue.component('data-list-toggle-all', require('../components/data-list/ToggleAll.vue').default);
Vue.component('data-list-pagination', require('../components/data-list/Pagination.vue').default);
Vue.component('data-list-filters', require('../components/data-list/Filters.vue').default);
Vue.component('data-list-filter-presets', require('../components/data-list/FilterPresets.vue').default);

// Resource Type Lists
Vue.component('entry-list', require('../components/entries/Listing.vue').default);
Vue.component('collection-list', require('../components/collections/Listing.vue').default);
Vue.component('taxonomy-list', require('../components/taxonomies/Listing.vue').default);
Vue.component('term-list', require('../components/terms/Listing.vue').default);
Vue.component('asset-container-list', require('../components/AssetContainerList.vue').default);
Vue.component('addon-list', require('../components/AddonList.vue').default);
Vue.component('addon-details', require('../components/AddonDetails.vue').default);

// Widgets
Vue.component('collection-widget', require('../components/entries/Widget.vue').default);

// Reusable
Vue.component('svg-icon', require('../components/SvgIcon.vue').default);
Vue.component('file-icon', require('../components/FileIcon.vue').default);
Vue.component('loading-graphic', require('../components/LoadingGraphic.vue').default);
Vue.component('dropdown-list', require('../components/DropdownList.vue').default);
Vue.component('dropdown-item', require('../components/DropdownItem.vue').default);
Vue.component('validation-errors', require('../components/ValidationErrors').default);
Vue.component('slugify', require('../components/Slugify.vue').default);
Vue.component('element-container', require('../components/ElementContainer.vue').default);
Vue.component('avatar', require('../components/Avatar.vue').default);
Vue.component('breadcrumb', require('../components/Breadcrumb.vue').default);
Vue.component('breadcrumbs', require('../components/Breadcrumbs.vue').default);
Vue.component('create-entry-button', require('../components/entries/CreateEntryButton.vue').default);
Vue.component('popover', require('../components/Popover.vue').default);

// Recursive
Vue.component('role-permission-tree', require('../components/roles/PermissionTree.vue').default);

// Modals
Vue.component('modal', require('../components/Modal.vue').default);
Vue.component('confirmation-modal', require('../components/modals/ConfirmationModal.vue').default);
Vue.component('favorite-creator', require('../components/FavoriteCreator.vue').default);
Vue.component('keyboard-shortcuts-modal', require('../components/modals/KeyboardShortcutsModal.vue').default);
Vue.component('resource-deleter', require('../components/ResourceDeleter.vue').default);
// Vue.component('modal-dialog', require('../components/ModalDialog.vue').default);

Vue.component('stack', require('../components/stacks/Stack.vue').default);
Vue.component('stack-test', require('../components/stacks/StackTest.vue').default);

Vue.component('pane', require('../components/panes/Pane.vue').default);
