import Vue from "vue";

Vue.component('select-input', require('../components/inputs/Select.vue'));
Vue.component('text-input', require('../components/inputs/Text.vue'));
Vue.component('textarea-input', require('../components/inputs/Textarea.vue'));
Vue.component('relationship-input', require('../components/inputs/relationship/RelationshipInput.vue'));

Vue.component('text-fieldtype', require('../components/fieldtypes/TextFieldtype.vue'));
Vue.component('textarea-fieldtype', require('../components/fieldtypes/TextareaFieldtype.vue'));
Vue.component('slug-fieldtype', require('../components/fieldtypes/SlugFieldtype.vue'));

// Fieldtypes
import AssetsFolderFieldtype from '../components/fieldtypes/AssetsFolderFieldtype.vue'
import AssetContainerFieldtype from '../components/fieldtypes/AssetContainerFieldtype.vue'
import CollectionFieldtype from '../components/fieldtypes/CollectionFieldtype.vue'
import FieldsetFieldtype from '../components/fieldtypes/FieldsetFieldtype.vue'
import LocaleSettingsFieldtype from '../components/fieldtypes/LocaleSettingsFieldtype.vue'
import PagesFieldtype from '../components/fieldtypes/PagesFieldtype.vue'
import RedactorSettingsFieldtype from '../components/fieldtypes/redactor/RedactorSettingsFieldtype.vue'
import RelateFieldtype from '../components/fieldtypes/relate/RelateFieldtype.vue'
import RevealerFieldtype from '../components/fieldtypes/RevealerFieldtype.vue'
import RoutesFieldtype from '../components/fieldtypes/RoutesFieldtype.vue'
import StatusFieldtype from '../components/fieldtypes/StatusFieldtype.vue'
import TaxonomyFieldtype from '../components/fieldtypes/TaxonomyFieldtype.vue'
import TemplateFieldtype from '../components/fieldtypes/TemplateFieldtype.vue'
import ThemeFieldtype from '../components/fieldtypes/ThemeFieldtype.vue'
import UserGroupsFieldtype from '../components/fieldtypes/UserGroupsFieldtype.vue'
import UserRolesFieldtype from '../components/fieldtypes/UserRolesFieldtype.vue'
import UsersFieldtype from '../components/fieldtypes/UsersFieldtype.vue'

Vue.component('array-fieldtype', require('../components/fieldtypes/ArrayFieldtype.vue'));
Vue.component('assets-fieldtype', require('../components/fieldtypes/assets/AssetsFieldtype.vue'));
Vue.component('assets-fieldtype-index', require('../components/fieldtypes/assets/AssetsIndexFieldtype.vue'));
Vue.component('asset_container-fieldtype', AssetContainerFieldtype);
Vue.component('asset_folder-fieldtype', AssetsFolderFieldtype);
Vue.component('bard-fieldtype', require('../components/fieldtypes/bard/BardFieldtype.vue'));
Vue.component('checkboxes-fieldtype', require('../components/fieldtypes/CheckboxesFieldtype.vue'));
Vue.component('code-fieldtype', require('../components/fieldtypes/CodeFieldtype.vue'));
Vue.component('collection-fieldtype', CollectionFieldtype);
Vue.component('date-fieldtype', require('../components/fieldtypes/DateFieldtype.vue'));
Vue.component('fieldset-fieldtype', FieldsetFieldtype);
Vue.component('grid-fieldtype', require('../components/fieldtypes/grid/Grid.vue'));
Vue.component('hidden-fieldtype', require('../components/fieldtypes/HiddenFieldtype.vue'));
Vue.component('integer-fieldtype', require('../components/fieldtypes/IntegerFieldtype.vue'));
Vue.component('list-fieldtype', require('../components/fieldtypes/ListFieldtype.vue'));
Vue.component('locale_settings-fieldtype', LocaleSettingsFieldtype);
Vue.component('markdown-fieldtype', require('../components/fieldtypes/MarkdownFieldtype.vue'));
Vue.component('pages-fieldtype', PagesFieldtype);
Vue.component('radio-fieldtype', require('../components/fieldtypes/RadioFieldtype.vue'));
Vue.component('redactor-fieldtype', require('../components/fieldtypes/redactor/RedactorFieldtype.vue'));
Vue.component('redactor_settings-fieldtype', RedactorSettingsFieldtype);
Vue.component('relate-fieldtype', RelateFieldtype);
Vue.component('relationship-fieldtype', require('../components/fieldtypes/relationship/RelationshipFieldtype.vue'));
Vue.component('relationship-fieldtype-index', require('../components/fieldtypes/relationship/RelationshipIndexFieldtype.vue'));
Vue.component('replicator-fieldtype', require('../components/fieldtypes/replicator/Replicator.vue'));
Vue.component('revealer-fieldtype', RevealerFieldtype);
Vue.component('routes-fieldtype', RoutesFieldtype);
Vue.component('section-fieldtype', require('../components/fieldtypes/SectionFieldtype.vue'));
Vue.component('select-fieldtype', require('../components/fieldtypes/SelectFieldtype.vue'));
Vue.component('status-fieldtype', StatusFieldtype);
Vue.component('table-fieldtype', require('../components/fieldtypes/TableFieldtype.vue'));
Vue.component('tags-fieldtype', require('../components/fieldtypes/TagsFieldtype.vue'));
Vue.component('taxonomy-fieldtype', TaxonomyFieldtype);
Vue.component('template-fieldtype', TemplateFieldtype);
Vue.component('theme-fieldtype', ThemeFieldtype);
Vue.component('time-fieldtype', require('../components/fieldtypes/TimeFieldtype.vue'));
Vue.component('toggle-fieldtype', require('../components/fieldtypes/ToggleFieldtype.vue'));
Vue.component('users-fieldtype', UsersFieldtype);
Vue.component('user_groups-fieldtype', UserGroupsFieldtype);
Vue.component('user_roles-fieldtype', UserRolesFieldtype);
Vue.component('video-fieldtype', require('../components/fieldtypes/VideoFieldtype.vue'));
Vue.component('yaml-fieldtype', require('../components/fieldtypes/YamlFieldtype.vue'));
