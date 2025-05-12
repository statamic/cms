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
import FieldActions from '../components/field-actions/FieldActions.vue';
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
import CollectionWidget from '../components/entries/CollectionWidget.vue';
import FormWidget from '../components/forms/FormWidget.vue';
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
import ConfirmationModal from '../components/modals/ConfirmationModal.vue';
import FavoriteCreator from '../components/FavoriteCreator.vue';
import KeyboardShortcutsModal from '../components/modals/KeyboardShortcutsModal.vue';
import FieldActionModal from '../components/field-actions/FieldActionModal.vue';
import ElevatedSessionModal from '../components/modals/ElevatedSessionModal.vue';
import ResourceDeleter from '../components/ResourceDeleter.vue';
import Stack from '../components/stacks/Stack.vue';
import StackTest from '../components/stacks/StackTest.vue';
import CodeBlock from '../components/CodeBlock.vue';
import BlueprintResetter from '../components/blueprints/BlueprintResetter.vue';
import { defineAsyncComponent } from 'vue';
import DateTime from '../components/DateTime.vue';
import UpdaterWidget from '../components/updater/UpdaterWidget.vue';

export default function registerGlobalComponents(app) {
    // Core
    app.component('asset-manager', AssetManager);
    app.component('asset-browser', Browser);
    app.component('updates-badge', UpdatesBadge);

    // Publish
    app.component('publish-container', Container);
    app.component('publish-form', PublishForm);
    app.component('publish-fields', Fields);
    app.component('publish-fields-container', FieldsContainer);
    app.component('publish-field', Field);
    app.component('publish-field-meta', FieldMeta);
    app.component('publish-field-actions', FieldActions);
    app.component('publish-field-fullscreen-header', FullscreenHeader);
    app.component('configure-tabs', ConfigureTabs);
    app.component('publish-tabs', PublishTabs);
    app.component('publish-sections', PublishSections);
    app.component('publish-validation-errors', PublishValidationErrors);
    app.component('form-group', FormGroup);

    app.component('live-preview', LivePreview);
    app.component('live-preview-popout', Popout);

    app.component('EntryPublishForm', EntryPublishForm);
    app.component('TermPublishForm', TermPublishForm);
    app.component('UserPublishForm', UserPublishForm);

    // Data List
    app.component('data-list', DataList);
    app.component('data-list-table', Table);
    app.component('data-list-search', Search);
    app.component('data-list-bulk-actions', BulkActions);
    app.component('data-list-inline-actions', InlineActions);
    app.component('data-list-column-picker', ColumnPicker);
    app.component('data-list-toggle-all', ToggleAll);
    app.component('data-list-pagination', Pagination);
    app.component('data-list-filters', Filters);
    app.component('data-list-filter-presets', FilterPresets);

    // Resource Type Lists
    app.component('entry-list', EntryListing);
    app.component('collection-list', CollectionListing);
    app.component('taxonomy-list', TaxonomyListing);
    app.component('term-list', TermListing);
    app.component('asset-container-list', AssetContainerList);
    app.component('addon-list', AddonList);
    app.component('addon-details', AddonDetails);

    // Widgets
    app.component('collection-widget', CollectionWidget);
    app.component('form-widget', FormWidget);
    app.component('updater-widget', UpdaterWidget);

    // Reusable
    app.component('svg-icon', SvgIcon);
    app.component('file-icon', FileIcon);
    app.component('loading-graphic', LoadingGraphic);
    app.component('dropdown-list', DropdownList);
    app.component('dropdown-item', DropdownItem);
    app.component('validation-errors', ValidationErrors);
    app.component('slugify', Slugify);
    app.component('element-container', ElementContainer);
    app.component('avatar', Avatar);
    app.component('breadcrumb', Breadcrumb);
    app.component('breadcrumbs', Breadcrumbs);
    app.component('create-entry-button', CreateEntryButton);
    app.component('popover', Popover);
    app.component('portal', Portal);
    app.component('code-block', CodeBlock);
    app.component('date-time', DateTime);

    // Recursive
    app.component('role-permission-tree', PermissionTree);

    // Modals
    app.component(
        'modal',
        defineAsyncComponent(() => import('../components/Modal.vue')),
    );
    app.component('confirmation-modal', ConfirmationModal);
    app.component('favorite-creator', FavoriteCreator);
    app.component('keyboard-shortcuts-modal', KeyboardShortcutsModal);
    app.component('resource-deleter', ResourceDeleter);
    app.component('field-action-modal', FieldActionModal);
    app.component('elevated-session-modal', ElevatedSessionModal);

    app.component('stack', Stack);
    app.component('stack-test', StackTest);

    app.component('blueprint-resetter', BlueprintResetter);
}
