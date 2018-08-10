import Vue from 'vue'
import EntryListing from '../components/listings/entries';

// Core
Vue.component('addon-listing', require('../components/listings/addons'));
Vue.component('asset-manager', require('../components/assets/AssetManager.vue'));
Vue.component('asset-browser', require('../components/assets/Browser/Browser.vue'));
Vue.component('asset-container-form', require('../components/assets/Container/EditForm.vue'));
Vue.component('asset-container-listing', require('../components/listings/asset-containers'));
Vue.component('asset-container-wizard', require('../components/assets/Container/Wizard/Wizard.vue'));
Vue.component('collection-listing', require('../components/listings/collections'));
Vue.component('configure-asset-container-listing', require('../components/listings/asset-containers-configure'));
Vue.component('configure-collection-listing', require('../components/listings/collections-configure'));
Vue.component('configure-globals-listing', require('../components/listings/globals-configure'));
Vue.component('configure-taxonomies-listing', require('../components/listings/taxonomies-configure'));
Vue.component('entry-listing', EntryListing);
Vue.component('formset-builder', require('../components/formset-builder/formset-builder'));
Vue.component('fieldset-builder', require('../components/fieldset-builder/Builder.vue'));
Vue.component('global-search', require('../components/GlobalSearch.vue'));
Vue.component('page-tree', require('../components/page-tree/PageTree.vue'));
Vue.component('publish', require('../components/publish/Publish.vue'));
Vue.component('publish-fields', require('../components/publish/Fields.vue'));
Vue.component('installer', require('../components/installer/installer'));
Vue.component('updater', require('../components/updater'));
Vue.component('importer', require('../components/importer/importer'));
Vue.component('term-listing', require('../components/listings/terms'));
Vue.component('taxonomies-listing', require('../components/listings/taxonomies'));
Vue.component('globals-listing', require('../components/listings/globals'));
Vue.component('user-listing', require('../components/listings/users'));
Vue.component('user-options', require('../components/publish/user-options'));
Vue.component('user-group-listing', require('../components/listings/user-groups'));
Vue.component('user-role-listing', require('../components/listings/user-roles'));
Vue.component('fieldset-listing', require('../components/listings/fieldsets'));
Vue.component('form-submission-listing', require('../components/listings/form-submissions'));
Vue.component('roles', require('../components/roles/roles'));
Vue.component('login', require('../components/login/login'));
Vue.component('login-modal', require('../components/login/LoginModal.vue'));
Vue.component('shortcuts-modal', require('../components/ShortcutsModal.vue'));


// Reusable
Vue.component('svg-icon', require('../components/SvgIcon.vue'));
Vue.component('file-icon', require('../components/FileIcon.vue'));
Vue.component('list', require('../components/list'));
Vue.component('alert', require('../components/alert'));
Vue.component('branch', require('../components/page-tree/Branch.vue'));
Vue.component('branches', require('../components/page-tree/Branches.vue'));
Vue.component('fields-builder', require('../components/fieldset-builder/fields-builder'));
Vue.component('fieldset-fields', require('../components/fieldset-builder/Sections/Fields.vue'));
Vue.component('fieldset-field', require('../components/fieldset-builder/Sections/Field.vue'));
Vue.component('fieldtype-selector', require('../components/fieldset-builder/FieldtypeSelector.vue'));
Vue.component('modal', require('../components/Modal.vue'));
Vue.component('modal-dialog', require('../components/ModalDialog.vue'));
Vue.component('pagination', require('../components/pagination/Pagination.vue'));

Vue.component('search', require('../components/dossier/DossierSearch.vue'));
Vue.component('dossier-sort-selector', require('../components/dossier/SortSelector.vue'));
