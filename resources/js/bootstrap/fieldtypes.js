import Vue from "vue";

Vue.component('select-input', require('../components/inputs/Select.vue').default);
Vue.component('text-input', require('../components/inputs/Text.vue').default);
Vue.component('textarea-input', require('../components/inputs/Textarea.vue').default);
Vue.component('toggle-input', require('../components/inputs/Toggle.vue').default);
Vue.component('relationship-input', require('../components/inputs/relationship/RelationshipInput.vue').default);

Vue.component('text-fieldtype', require('../components/fieldtypes/TextFieldtype.vue').default);
Vue.component('textarea-fieldtype', require('../components/fieldtypes/TextareaFieldtype.vue').default);
Vue.component('slug-fieldtype', require('../components/fieldtypes/SlugFieldtype.vue').default);

// Fieldtypes
import RevealerFieldtype from '../components/fieldtypes/RevealerFieldtype.vue'
import StatusFieldtype from '../components/fieldtypes/StatusFieldtype.vue'
import TemplateFieldtype from '../components/fieldtypes/TemplateFieldtype.vue'

Vue.component('array-fieldtype', require('../components/fieldtypes/ArrayFieldtype.vue').default);
Vue.component('assets-fieldtype', require('../components/fieldtypes/assets/AssetsFieldtype.vue').default);
Vue.component('assets-fieldtype-index', require('../components/fieldtypes/assets/AssetsIndexFieldtype.vue').default);
Vue.component('asset_folder-fieldtype', require('../components/fieldtypes/AssetFolderFieldtype.vue').default);
Vue.component('bard-fieldtype', require('../components/fieldtypes/bard/BardFieldtype.vue').default);
Vue.component('bard_buttons_setting-fieldtype', require('../components/fieldtypes/bard/BardButtonsSettingFieldtype.vue').default);
Vue.component('button_group-fieldtype', require('../components/fieldtypes/ButtonGroupFieldtype.vue').default);
Vue.component('checkboxes-fieldtype', require('../components/fieldtypes/CheckboxesFieldtype.vue').default);
Vue.component('code-fieldtype', require('../components/fieldtypes/CodeFieldtype.vue').default);
Vue.component('collection_routes-fieldtype', require('../components/collections/Routes.vue').default);
Vue.component('collection_title_formats-fieldtype', require('../components/collections/TitleFormats.vue').default);
Vue.component('color-fieldtype', require('../components/fieldtypes/ColorFieldtype.vue').default);
Vue.component('date-fieldtype', require('../components/fieldtypes/DateFieldtype.vue').default);
Vue.component('fields-fieldtype', require('../components/fieldtypes/grid/FieldsFieldtype.vue').default);
Vue.component('files-fieldtype', require('../components/fieldtypes/FilesFieldtype.vue').default);
Vue.component('float-fieldtype', require('../components/fieldtypes/FloatFieldtype.vue').default);
Vue.component('global_set_sites-fieldtype', require('../components/globals/Sites.vue').default);
Vue.component('grid-fieldtype', require('../components/fieldtypes/grid/Grid.vue').default);
Vue.component('grid-fieldtype-index', require('../components/fieldtypes/grid/GridIndex.vue').default);
Vue.component('hidden-fieldtype', require('../components/fieldtypes/HiddenFieldtype.vue').default);
Vue.component('html-fieldtype', require('../components/fieldtypes/HtmlFieldtype.vue').default);
Vue.component('integer-fieldtype', require('../components/fieldtypes/IntegerFieldtype.vue').default);
Vue.component('link-fieldtype', require('../components/fieldtypes/LinkFieldtype.vue').default);
Vue.component('list-fieldtype', require('../components/fieldtypes/ListFieldtype.vue').default);
Vue.component('markdown-fieldtype', require('../components/fieldtypes/MarkdownFieldtype.vue').default);
Vue.component('radio-fieldtype', require('../components/fieldtypes/RadioFieldtype.vue').default);
Vue.component('range-fieldtype', require('../components/fieldtypes/RangeFieldtype.vue').default);
Vue.component('relationship-fieldtype', require('../components/fieldtypes/relationship/RelationshipFieldtype.vue').default);
Vue.component('relationship-fieldtype-index', require('../components/fieldtypes/relationship/RelationshipIndexFieldtype.vue').default);
Vue.component('replicator-fieldtype', require('../components/fieldtypes/replicator/Replicator.vue').default);
Vue.component('replicator-fieldtype-index', require('../components/fieldtypes/replicator/ReplicatorIndex.vue').default);
Vue.component('revealer-fieldtype', RevealerFieldtype);
Vue.component('section-fieldtype', require('../components/fieldtypes/SectionFieldtype.vue').default);
Vue.component('select-fieldtype', require('../components/fieldtypes/SelectFieldtype.vue').default);
Vue.component('sets-fieldtype', require('../components/fieldtypes/replicator/SetsFieldtype.vue').default);
Vue.component('status-fieldtype', StatusFieldtype);
Vue.component('table-fieldtype', require('../components/fieldtypes/TableFieldtype.vue').default);
Vue.component('tags-fieldtype', require('../components/fieldtypes/TagsFieldtype.vue').default);
Vue.component('tags-fieldtype-index', require('../components/fieldtypes/TagsIndexFieldtype.vue').default);
Vue.component('template-fieldtype', TemplateFieldtype);
Vue.component('template_folder-fieldtype', require('../components/fieldtypes/TemplateFolderFieldtype.vue').default);
Vue.component('time-fieldtype', require('../components/fieldtypes/TimeFieldtype.vue').default);
Vue.component('toggle-fieldtype', require('../components/fieldtypes/ToggleFieldtype.vue').default);
Vue.component('toggle-fieldtype-index', require('../components/fieldtypes/ToggleIndexFieldtype.vue').default);
Vue.component('video-fieldtype', require('../components/fieldtypes/VideoFieldtype.vue').default);
Vue.component('yaml-fieldtype', require('../components/fieldtypes/YamlFieldtype.vue').default);
