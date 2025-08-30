import AssetManager from '../components/assets/AssetManager.vue';
import Browser from '../components/assets/Browser/Browser.vue';
import UpdatesBadge from '../components/UpdatesBadge.vue';
import FullscreenHeader from '../components/publish/FullscreenHeader.vue';
import FieldMeta from '../components/publish/FieldMeta.vue';
import EntryPublishForm from '../components/entries/PublishForm.vue';
import TermPublishForm from '../components/terms/PublishForm.vue';
import UserPublishForm from '../components/users/PublishForm.vue';
import EntryListing from '../components/entries/Listing.vue';
import CollectionListing from '../components/collections/Listing.vue';
import TaxonomyListing from '../components/taxonomies/Listing.vue';
import TermListing from '../components/terms/Listing.vue';
import AddonListing from '../components/addons/Listing.vue';
import CollectionWidget from '../components/entries/CollectionWidget.vue';
import FormWidget from '../components/forms/FormWidget.vue';
import FileIcon from '../components/FileIcon.vue';

import Slugify from '../components/slugs/Slugify.vue';
import ElementContainer from '../components/ElementContainer.vue';
import Avatar from '../components/Avatar.vue';
import CreateEntryButton from '../components/entries/CreateEntryButton.vue';
import Portal from '../components/portals/Portal.vue';
import ConfirmationModal from '../components/modals/ConfirmationModal.vue';
import FieldActionModal from '../components/field-actions/FieldActionModal.vue';
import ElevatedSessionModal from '../components/modals/ElevatedSessionModal.vue';
import ResourceDeleter from '../components/ResourceDeleter.vue';
import Stack from '../components/stacks/Stack.vue';
import BlueprintCreateForm from '../components/blueprints/BlueprintCreateForm.vue';
import BlueprintResetter from '../components/blueprints/BlueprintResetter.vue';
import GitStatus from '../components/GitStatus.vue';
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

    app.component('EntryPublishForm', EntryPublishForm);
    app.component('TermPublishForm', TermPublishForm);
    app.component('UserPublishForm', UserPublishForm);

    // Resource Type Lists
    app.component('entry-list', EntryListing);
    app.component('collection-list', CollectionListing);
    app.component('taxonomy-list', TaxonomyListing);
    app.component('term-list', TermListing);
    app.component('addon-list', AddonListing);

    // Widgets
    app.component('collection-widget', CollectionWidget);
    app.component('form-widget', FormWidget);
    app.component('updater-widget', UpdaterWidget);

    // Reusable
    app.component('file-icon', FileIcon);

    app.component('slugify', Slugify);
    app.component('element-container', ElementContainer);
    app.component('avatar', Avatar);
    app.component('create-entry-button', CreateEntryButton);
    app.component('portal', Portal);
    app.component('date-time', DateTime);
    app.component('git-status', GitStatus);

    // Modals
    app.component('confirmation-modal', ConfirmationModal);
    app.component('resource-deleter', ResourceDeleter);
    app.component('field-action-modal', FieldActionModal);
    app.component('elevated-session-modal', ElevatedSessionModal);

    app.component('stack', Stack);

    app.component('blueprint-create-form', BlueprintCreateForm);
    app.component('blueprint-resetter', BlueprintResetter);
}
