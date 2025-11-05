import Browser from '../components/assets/Browser/Browser.vue';
import UpdatesBadge from '../components/UpdatesBadge.vue';
import FullscreenHeader from '../components/publish/FullscreenHeader.vue';
import FieldMeta from '../components/publish/FieldMeta.vue';
import EntryPublishForm from '../components/entries/PublishForm.vue';
import TermPublishForm from '../components/terms/PublishForm.vue';
import UserPublishForm from '../components/users/PublishForm.vue';
import EntryListing from '../components/entries/Listing.vue';
import CollectionWidget from '../components/entries/CollectionWidget.vue';
import FormWidget from '../components/forms/FormWidget.vue';
import FileIcon from '../components/FileIcon.vue';

import Slugify from '../components/slugs/Slugify.vue';
import ElementContainer from '../components/ElementContainer.vue';
import CreateEntryButton from '../components/entries/CreateEntryButton.vue';
import Portal from '../components/portals/Portal.vue';
import ConfirmationModal from '../components/modals/ConfirmationModal.vue';
import FieldActionModal from '../components/field-actions/FieldActionModal.vue';
import ElevatedSessionModal from '../components/modals/ElevatedSessionModal.vue';
import ResourceDeleter from '../components/ResourceDeleter.vue';
import Stack from '../components/stacks/Stack.vue';
import BlueprintResetter from '../components/blueprints/BlueprintResetter.vue';
import GitStatus from '../components/GitStatus.vue';
import DateTime from '../components/DateTime.vue';
import UpdaterWidget from '../components/updater/UpdaterWidget.vue';

import CreateTermButton from '../components/terms/CreateTermButton.vue';
import BlueprintListing from '../components/blueprints/Listing.vue';
import BlueprintBuilder from '../components/blueprints/Builder.vue';
import FormSubmissionListing from '../components/forms/SubmissionListing.vue';
import UserWizard from '../components/users/Wizard.vue';
import RolePublishForm from '../components/roles/PublishForm.vue';
import UserGroupPublishForm from '../components/user-groups/PublishForm.vue';
import ItemActions from '../components/actions/ItemActions.vue';
import BulkActions from '../components/actions/BulkActions.vue';

import { defineAsyncComponent } from 'vue';
import { Link } from '@inertiajs/vue3';
import DynamicHtmlRenderer from '@/components/DynamicHtmlRenderer.vue';

export default function registerGlobalComponents(app) {
    // Core
    app.component('asset-browser', Browser);
    app.component('updates-badge', UpdatesBadge);
    app.component('inertia-link', Link);
    app.component('dynamic-html-renderer', DynamicHtmlRenderer);

    // Publish
    app.component('publish-field-meta', FieldMeta);
    app.component('publish-field-fullscreen-header', FullscreenHeader);

    app.component('EntryPublishForm', EntryPublishForm);
    app.component('TermPublishForm', TermPublishForm);
    app.component('UserPublishForm', UserPublishForm);

    // Resource Type Lists
    app.component('entry-list', EntryListing);

    // Widgets
    app.component('collection-widget', CollectionWidget);
    app.component('form-widget', FormWidget);
    app.component('updater-widget', UpdaterWidget);

    // Reusable
    app.component('file-icon', FileIcon);

    app.component('slugify', Slugify);
    app.component('element-container', ElementContainer);
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

    app.component('blueprint-resetter', BlueprintResetter);

    // Temporarily global during intertia migration
    // These are no longer used at the top level since there's now a layout component.
    // Eventually they will be moved into their respective pages.
    app.component('CreateTermButton', CreateTermButton);
    app.component('BlueprintListing', BlueprintListing);
    app.component('BlueprintBuilder', BlueprintBuilder);
    app.component('FormSubmissionListing', FormSubmissionListing);
    app.component('UserWizard', UserWizard);
    app.component('RolePublishForm', RolePublishForm);
    app.component('UserGroupPublishForm', UserGroupPublishForm);
    app.component('ItemActions', ItemActions);
    app.component('BulkActions', BulkActions);
}
