import Vue from 'vue'
import vSelect from 'vue-select'
import AssetManager from '../components/assets/AssetManager.vue';
import Browser from '../components/assets/Browser/Browser.vue';
import UpdatesBadge from '../components/UpdatesBadge.vue';
import Container from '../components/publish/Container.vue';
import PublishForm from '../components/publish/PublishForm.vue';
import Fields from '../components/publish/Fields.vue';
import FieldsContainer from '../components/publish/FieldsContainer.vue'; // deprecated
import Field from '../components/publish/Field.vue';
import FullscreenHeader from '../components/publish/FullscreenHeader.vue';
import FieldMeta from '../components/publish/FieldMeta.vue';
import ConfigureTabs from '../components/configure/Tabs.vue';
import PublishTabs from '../components/publish/Tabs.vue';
import PublishSections from '../components/publish/Sections.vue';
import PublishValidationErrors from '../components/publish/ValidationErrors.vue';
import FormGroup from '../components/publish/FormGroup.vue';
import LivePreview from '../components/live-preview/LivePreview.vue';
import Popout from '../components/live-preview/Popout.vue';
import EntryPublishForm from '../components/entries/PublishForm.vue';
import TermPublishForm from '../components/terms/PublishForm.vue';
import UserPublishForm from '../components/users/PublishForm.vue';
import DataList from '../components/data-list/DataList.vue';
import Table from '../components/data-list/Table.vue';
import Search from '../components/data-list/Search.vue';
import BulkActions from '../components/data-list/BulkActions.vue';
import InlineActions from '../components/data-list/InlineActions.vue';
import ColumnPicker from '../components/data-list/ColumnPicker.vue';
import ToggleAll from '../components/data-list/ToggleAll.vue';
import Pagination from '../components/data-list/Pagination.vue';
import Filters from '../components/data-list/Filters.vue';
import FilterPresets from '../components/data-list/FilterPresets.vue';
import EntryListing from '../components/entries/Listing.vue';
import CollectionListing from '../components/collections/Listing.vue';
import TaxonomyListing from '../components/taxonomies/Listing.vue';
import TermListing from '../components/terms/Listing.vue';
import AssetContainerList from '../components/AssetContainerList.vue';
import AddonList from '../components/AddonList.vue';
import AddonDetails from '../components/AddonDetails.vue';
import CollectionWidget from '../components/entries/Widget.vue';
import SvgIcon from '../components/SvgIcon.vue';
import FileIcon from '../components/FileIcon.vue';
import LoadingGraphic from '../components/LoadingGraphic.vue';
import DropdownList from '../components/DropdownList.vue';
import DropdownItem from '../components/DropdownItem.vue';
import ValidationErrors from '../components/ValidationErrors.vue';
import Slugify from '../components/slugs/Slugify.vue';
import ElementContainer from '../components/ElementContainer.vue';
import Avatar from '../components/Avatar.vue';
import Breadcrumb from '../components/Breadcrumb.vue';
import Breadcrumbs from '../components/Breadcrumbs.vue';
import CreateEntryButton from '../components/entries/CreateEntryButton.vue';
import Popover from '../components/Popover.vue';
import Portal from '../components/portals/Portal.vue';
import PermissionTree from '../components/roles/PermissionTree.vue';
import Modal from '../components/Modal.vue';
import ConfirmationModal from '../components/modals/ConfirmationModal.vue';
import FavoriteCreator from '../components/FavoriteCreator.vue';
import KeyboardShortcutsModal from '../components/modals/KeyboardShortcutsModal.vue';
import ResourceDeleter from '../components/ResourceDeleter.vue';
import Stack from '../components/stacks/Stack.vue';
import StackTest from '../components/stacks/StackTest.vue';
import CodeBlock from '../components/CodeBlock.vue';
import BlueprintResetter from '../components/blueprints/BlueprintResetter.vue';

// Third Party
Vue.component('v-select', vSelect)

// Core
Vue.component('asset-manager', AssetManager);
Vue.component('asset-browser', Browser);
Vue.component('updates-badge', UpdatesBadge);

// Publish
Vue.component('publish-container', Container);
Vue.component('publish-form', PublishForm);
Vue.component('publish-fields', Fields);
Vue.component('publish-fields-container', FieldsContainer);
Vue.component('publish-field', Field);
Vue.component('publish-field-meta', FieldMeta);
Vue.component('publish-field-fullscreen-header', FullscreenHeader);
Vue.component('configure-tabs', ConfigureTabs);
Vue.component('publish-tabs', PublishTabs);
Vue.component('publish-sections', PublishSections);
Vue.component('publish-validation-errors', PublishValidationErrors);
Vue.component('form-group', FormGroup);

Vue.component('live-preview', LivePreview);
Vue.component('live-preview-popout', Popout);

Vue.component('EntryPublishForm', EntryPublishForm);
Vue.component('TermPublishForm', TermPublishForm);
Vue.component('UserPublishForm', UserPublishForm);

// Data List
Vue.component('data-list', DataList);
Vue.component('data-list-table', Table);
Vue.component('data-list-search', Search);
Vue.component('data-list-bulk-actions', BulkActions);
Vue.component('data-list-inline-actions', InlineActions);
Vue.component('data-list-column-picker', ColumnPicker);
Vue.component('data-list-toggle-all', ToggleAll);
Vue.component('data-list-pagination', Pagination);
Vue.component('data-list-filters', Filters);
Vue.component('data-list-filter-presets', FilterPresets);

// Resource Type Lists
Vue.component('entry-list', EntryListing);
Vue.component('collection-list', CollectionListing);
Vue.component('taxonomy-list', TaxonomyListing);
Vue.component('term-list', TermListing);
Vue.component('asset-container-list', AssetContainerList);
Vue.component('addon-list', AddonList);
Vue.component('addon-details', AddonDetails);

// Widgets
Vue.component('collection-widget', CollectionWidget);

// Reusable
Vue.component('svg-icon', SvgIcon);
Vue.component('file-icon', FileIcon);
Vue.component('loading-graphic', LoadingGraphic);
Vue.component('dropdown-list', DropdownList);
Vue.component('dropdown-item', DropdownItem);
Vue.component('validation-errors', ValidationErrors);
Vue.component('slugify', Slugify);
Vue.component('element-container', ElementContainer);
Vue.component('avatar', Avatar);
Vue.component('breadcrumb', Breadcrumb);
Vue.component('breadcrumbs', Breadcrumbs);
Vue.component('create-entry-button', CreateEntryButton);
Vue.component('popover', Popover);
Vue.component('portal', Portal);
Vue.component('code-block', CodeBlock);

// Recursive
Vue.component('role-permission-tree', PermissionTree);

// Modals
Vue.component('modal', Modal);
Vue.component('confirmation-modal', ConfirmationModal);
Vue.component('favorite-creator', FavoriteCreator);
Vue.component('keyboard-shortcuts-modal', KeyboardShortcutsModal);
Vue.component('resource-deleter', ResourceDeleter);

Vue.component('stack', Stack);
Vue.component('stack-test', StackTest);

Vue.component('blueprint-resetter', BlueprintResetter);
