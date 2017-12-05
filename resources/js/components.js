import DossierTable from './components/dossier/DossierTable.vue'
import DossierSearch from './components/dossier/DossierSearch.vue'
import PageTree from './components/page-tree/PageTree.vue'
import PageTreeBranch from './components/page-tree/Branch.vue'
import PageTreeBranches from './components/page-tree/Branches.vue'

// Core
Vue.component('addon-listing', require('./components/listings/addons'));
Vue.component('asset-manager', require('./components/assets/AssetManager.vue'));
Vue.component('asset-browser', require('./components/assets/Browser/Browser.vue'));
Vue.component('asset-container-form', require('./components/assets/Container/EditForm.vue'));
Vue.component('asset-container-listing', require('./components/listings/asset-containers'));
Vue.component('asset-container-wizard', require('./components/assets/Container/Wizard/Wizard.vue'));
Vue.component('collection-listing', require('./components/listings/collections'));
Vue.component('configure-asset-container-listing', require('./components/listings/asset-containers-configure'));
Vue.component('configure-collection-listing', require('./components/listings/collections-configure'));
Vue.component('configure-globals-listing', require('./components/listings/globals-configure'));
Vue.component('configure-taxonomies-listing', require('./components/listings/taxonomies-configure'));
Vue.component('entry-listing', require('./components/listings/entries'));
Vue.component('formset-builder', require('./components/formset-builder/formset-builder'));
Vue.component('fieldset-builder', require('./components/fieldset-builder/builder'));
Vue.component('page-tree', PageTree);
Vue.component('publish', require('./components/publish/publish'));
Vue.component('typeahead', require('./components/typeahead/typeahead'));
Vue.component('installer', require('./components/installer/installer'));
Vue.component('updater', require('./components/updater'));
Vue.component('importer', require('./components/importer/importer'));
Vue.component('term-listing', require('./components/listings/terms'));
Vue.component('taxonomies-listing', require('./components/listings/taxonomies'));
Vue.component('globals-listing', require('./components/listings/globals'));
Vue.component('user-listing', require('./components/listings/users'));
Vue.component('user-group-listing', require('./components/listings/user-groups'));
Vue.component('user-role-listing', require('./components/listings/user-roles'));
Vue.component('fieldset-listing', require('./components/listings/fieldsets'));
Vue.component('form-submission-listing', require('./components/listings/form-submissions'));
Vue.component('roles', require('./components/roles/roles'));
Vue.component('login', require('./components/login/login'));

// Reusable
Vue.component('svg-icon', require('./components/SvgIcon.vue'));
Vue.component('file-icon', require('./components/FileIcon.vue'));
Vue.component('list', require('./components/list'));
Vue.component('alert', require('./components/alert'));
Vue.component('branch', PageTreeBranch);
Vue.component('branches', PageTreeBranches);
Vue.component('set-builder', require('./components/fieldset-builder/set-builder'));
Vue.component('fields-builder', require('./components/fieldset-builder/fields-builder'));
Vue.component('field-settings', require('./components/fieldset-builder/field-settings'));
Vue.component('fieldset-fields', require('./components/fieldset-builder/fieldset-fields'));
Vue.component('fieldtype-selector', require('./components/fieldset-builder/fieldtype-selector'));
Vue.component('modal', require('./components/modal/modal'));
Vue.component('pagination', require('./components/pagination/Pagination.vue'));

Vue.component('search', DossierSearch);
