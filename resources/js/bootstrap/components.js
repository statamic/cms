import AssetManager from '../components/assets/AssetManager.vue';
import Browser from '../components/assets/Browser/Browser.vue';
import UpdatesBadge from '../components/UpdatesBadge.vue';
import FullscreenHeader from '../components/publish/FullscreenHeader.vue';
import FieldMeta from '../components/publish/FieldMeta.vue';
import LivePreview from '../components/live-preview/LivePreview.vue';
import Popout from '../components/live-preview/Popout.vue';
import EntryPublishForm from '../components/entries/PublishForm.vue';
import TermPublishForm from '../components/terms/PublishForm.vue';
import UserPublishForm from '../components/users/PublishForm.vue';
import EntryListing from '../components/entries/Listing.vue';
import CollectionListing from '../components/collections/Listing.vue';
import TaxonomyListing from '../components/taxonomies/Listing.vue';
import TermListing from '../components/terms/Listing.vue';
import AddonList from '../components/AddonList.vue';
import AddonDetails from '../components/AddonDetails.vue';
import CollectionWidget from '../components/entries/CollectionWidget.vue';
import FormWidget from '../components/forms/FormWidget.vue';
import SvgIcon from '../components/SvgIcon.vue';
import FileIcon from '../components/FileIcon.vue';
import LoadingGraphic from '../components/LoadingGraphic.vue';
import DropdownList from '../components/DropdownList.vue';
import DropdownItem from '../components/DropdownItem.vue';
import Slugify from '../components/slugs/Slugify.vue';
import ElementContainer from '../components/ElementContainer.vue';
import Avatar from '../components/Avatar.vue';
import CreateEntryButton from '../components/entries/CreateEntryButton.vue';
import Popover from '../components/Popover.vue';
import Portal from '../components/portals/Portal.vue';
import ConfirmationModal from '../components/modals/ConfirmationModal.vue';
import KeyboardShortcutsModal from '../components/modals/KeyboardShortcutsModal.vue';
import FieldActionModal from '../components/field-actions/FieldActionModal.vue';
import ElevatedSessionModal from '../components/modals/ElevatedSessionModal.vue';
import ResourceDeleter from '../components/ResourceDeleter.vue';
import Stack from '../components/stacks/Stack.vue';
import StackTest from '../components/stacks/StackTest.vue';
import CodeBlock from '../components/CodeBlock.vue';
import BlueprintCreateForm from '../components/blueprints/BlueprintCreateForm.vue';
import BlueprintResetter from '../components/blueprints/BlueprintResetter.vue';
import GitStatus from '../components/GitStatus.vue';
import { defineAsyncComponent } from 'vue';
import DateTime from '../components/DateTime.vue';
import UpdaterWidget from '../components/updater/UpdaterWidget.vue';

export default function registerGlobalComponents(app) {
    // Core
    app.component('asset-manager', AssetManager);
    app.component('asset-browser', Browser);
    app.component('updates-badge', UpdatesBadge);

    // Publish
    app.component('publish-field-meta', FieldMeta);
    app.component('publish-field-fullscreen-header', FullscreenHeader);

    app.component('live-preview', LivePreview);
    app.component('live-preview-popout', Popout);

    app.component('EntryPublishForm', EntryPublishForm);
    app.component('TermPublishForm', TermPublishForm);
    app.component('UserPublishForm', UserPublishForm);

    // Resource Type Lists
    app.component('entry-list', EntryListing);
    app.component('collection-list', CollectionListing);
    app.component('taxonomy-list', TaxonomyListing);
    app.component('term-list', TermListing);
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
    app.component('slugify', Slugify);
    app.component('element-container', ElementContainer);
    app.component('avatar', Avatar);
    app.component('create-entry-button', CreateEntryButton);
    app.component('popover', Popover);
    app.component('portal', Portal);
    app.component('code-block', CodeBlock);
    app.component('date-time', DateTime);
    app.component('git-status', GitStatus);

    // Modals
    app.component(
        'modal',
        defineAsyncComponent(() => import('../components/Modal.vue')),
    );
    app.component('confirmation-modal', ConfirmationModal);
    app.component('keyboard-shortcuts-modal', KeyboardShortcutsModal);
    app.component('resource-deleter', ResourceDeleter);
    app.component('field-action-modal', FieldActionModal);
    app.component('elevated-session-modal', ElevatedSessionModal);

    app.component('stack', Stack);
    app.component('stack-test', StackTest);

    app.component('blueprint-create-form', BlueprintCreateForm);
    app.component('blueprint-resetter', BlueprintResetter);
}
