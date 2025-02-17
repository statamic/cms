import Vue from "vue";
import RevealerFieldtype from '../components/fieldtypes/RevealerFieldtype.vue'
import StatusFieldtype from '../components/fieldtypes/StatusFieldtype.vue'
import TemplateFieldtype from '../components/fieldtypes/TemplateFieldtype.vue'
import Select from '../components/inputs/Select.vue';
import Text from '../components/inputs/Text.vue';
import Textarea from '../components/inputs/Textarea.vue';
import Toggle from '../components/inputs/Toggle.vue';
import RelationshipInput from '../components/inputs/relationship/RelationshipInput.vue';
import TextFieldtype from '../components/fieldtypes/TextFieldtype.vue';
import TextareaFieldtype from '../components/fieldtypes/TextareaFieldtype.vue';
import SlugFieldtype from '../components/fieldtypes/SlugFieldtype.vue';
import ArrayFieldtype from '../components/fieldtypes/ArrayFieldtype.vue';
import AssetsFieldtype from '../components/fieldtypes/assets/AssetsFieldtype.vue';
import AssetsIndexFieldtype from '../components/fieldtypes/assets/AssetsIndexFieldtype.vue';
import AssetFolderFieldtype from '../components/fieldtypes/AssetFolderFieldtype.vue';
import BardFieldtype from '../components/fieldtypes/bard/BardFieldtype.vue';
import BardSet from '../components/fieldtypes/bard/Set.vue';
import BardButtonsSettingFieldtype from '../components/fieldtypes/bard/BardButtonsSettingFieldtype.vue';
import ButtonGroupFieldtype from '../components/fieldtypes/ButtonGroupFieldtype.vue';
import CheckboxesFieldtype from '../components/fieldtypes/CheckboxesFieldtype.vue';
import CodeFieldtype from '../components/fieldtypes/CodeFieldtype.vue';
import Routes from '../components/collections/Routes.vue';
import TitleFormats from '../components/collections/TitleFormats.vue';
import ColorFieldtype from '../components/fieldtypes/ColorFieldtype.vue';
import DateFieldtype from '../components/fieldtypes/DateFieldtype.vue';
import DictionaryFieldtype from "../components/fieldtypes/DictionaryFieldtype.vue";
import DictionaryIndexFieldtype from "../components/fieldtypes/DictionaryIndexFieldtype.vue";
import DictionaryFields from "../components/fieldtypes/DictionaryFields.vue";
import FieldDisplayFieldtype from '../components/fieldtypes/FieldDisplayFieldtype.vue';
import FieldsFieldtype from '../components/fieldtypes/grid/FieldsFieldtype.vue';
import FilesFieldtype from '../components/fieldtypes/FilesFieldtype.vue';
import FloatFieldtype from '../components/fieldtypes/FloatFieldtype.vue';
import Sites from '../components/globals/Sites.vue';
import Grid from '../components/fieldtypes/grid/Grid.vue';
import GridIndex from '../components/fieldtypes/grid/GridIndex.vue';
import GroupFieldtype from '../components/fieldtypes/GroupFieldtype.vue';
import HiddenFieldtype from '../components/fieldtypes/HiddenFieldtype.vue';
import HtmlFieldtype from '../components/fieldtypes/HtmlFieldtype.vue';
import IconFieldtype from '../components/fieldtypes/IconFieldtype.vue';
import IntegerFieldtype from '../components/fieldtypes/IntegerFieldtype.vue';
import LinkFieldtype from '../components/fieldtypes/LinkFieldtype.vue';
import ListFieldtype from '../components/fieldtypes/ListFieldtype.vue';
import ListIndexFieldtype from '../components/fieldtypes/ListIndexFieldtype.vue';
import MarkdownFieldtype from '../components/fieldtypes/markdown/MarkdownFieldtype.vue';
import MarkdownButtonsSettingFieldtype from '../components/fieldtypes/markdown/MarkdownButtonsSettingFieldtype.vue';
import RadioFieldtype from '../components/fieldtypes/RadioFieldtype.vue';
import RangeFieldtype from '../components/fieldtypes/RangeFieldtype.vue';
import RelationshipFieldtype from '../components/fieldtypes/relationship/RelationshipFieldtype.vue';
import RelationshipIndexFieldtype from '../components/fieldtypes/relationship/RelationshipIndexFieldtype.vue';
import Replicator from '../components/fieldtypes/replicator/Replicator.vue';
import ReplicatorSet from '../components/fieldtypes/replicator/Set.vue';
import ReplicatorIndex from '../components/fieldtypes/replicator/ReplicatorIndex.vue';
import SectionFieldtype from '../components/fieldtypes/SectionFieldtype.vue';
import SelectFieldtype from '../components/fieldtypes/SelectFieldtype.vue';
import SetsFieldtype from '../components/fieldtypes/replicator/SetsFieldtype.vue';
import TableFieldtype from '../components/fieldtypes/TableFieldtype.vue';
import TagsFieldtype from '../components/fieldtypes/TagsFieldtype.vue';
import TagsIndexFieldtype from '../components/fieldtypes/TagsIndexFieldtype.vue';
import TemplateFolderFieldtype from '../components/fieldtypes/TemplateFolderFieldtype.vue';
import TimeFieldtype from '../components/fieldtypes/TimeFieldtype.vue';
import ToggleFieldtype from '../components/fieldtypes/ToggleFieldtype.vue';
import ToggleIndexFieldtype from '../components/fieldtypes/ToggleIndexFieldtype.vue';
import WidthFieldtype from '../components/fieldtypes/WidthFieldtype.vue';
import VideoFieldtype from '../components/fieldtypes/VideoFieldtype.vue';
import YamlFieldtype from '../components/fieldtypes/YamlFieldtype.vue';
import SetPicker from '../components/fieldtypes/replicator/SetPicker.vue';
import SetField from '../components/fieldtypes/replicator/Field.vue';

Vue.component('select-input', Select);
Vue.component('text-input', Text);
Vue.component('textarea-input', Textarea);
Vue.component('toggle-input', Toggle);
Vue.component('relationship-input', RelationshipInput);
Vue.component('text-fieldtype', TextFieldtype);
Vue.component('textarea-fieldtype', TextareaFieldtype);
Vue.component('slug-fieldtype', SlugFieldtype);
Vue.component('array-fieldtype', ArrayFieldtype);
Vue.component('assets-fieldtype', AssetsFieldtype);
Vue.component('assets-fieldtype-index', AssetsIndexFieldtype);
Vue.component('asset_folder-fieldtype', AssetFolderFieldtype);
Vue.component('bard-fieldtype', BardFieldtype);
Vue.component('bard-fieldtype-set', BardSet);
Vue.component('bard_buttons_setting-fieldtype', BardButtonsSettingFieldtype);
Vue.component('button_group-fieldtype', ButtonGroupFieldtype);
Vue.component('checkboxes-fieldtype', CheckboxesFieldtype);
Vue.component('code-fieldtype', CodeFieldtype);
Vue.component('collection_routes-fieldtype', Routes);
Vue.component('collection_title_formats-fieldtype', TitleFormats);
Vue.component('color-fieldtype', ColorFieldtype);
Vue.component('date-fieldtype', DateFieldtype);
Vue.component('dictionary-fieldtype', DictionaryFieldtype);
Vue.component('dictionary-fieldtype-index', DictionaryIndexFieldtype);
Vue.component('dictionary_fields-fieldtype', DictionaryFields);
Vue.component('field_display-fieldtype', FieldDisplayFieldtype);
Vue.component('fields-fieldtype', FieldsFieldtype);
Vue.component('files-fieldtype', FilesFieldtype);
Vue.component('float-fieldtype', FloatFieldtype);
Vue.component('global_set_sites-fieldtype', Sites);
Vue.component('grid-fieldtype', Grid);
Vue.component('grid-fieldtype-index', GridIndex);
Vue.component('group-fieldtype', GroupFieldtype);
Vue.component('hidden-fieldtype', HiddenFieldtype);
Vue.component('html-fieldtype', HtmlFieldtype);
Vue.component('icon-fieldtype', IconFieldtype);
Vue.component('integer-fieldtype', IntegerFieldtype);
Vue.component('link-fieldtype', LinkFieldtype);
Vue.component('list-fieldtype', ListFieldtype);
Vue.component('list-fieldtype-index', ListIndexFieldtype);
Vue.component('markdown-fieldtype', MarkdownFieldtype);
Vue.component('markdown_buttons_setting-fieldtype', MarkdownButtonsSettingFieldtype);
Vue.component('radio-fieldtype', RadioFieldtype);
Vue.component('range-fieldtype', RangeFieldtype);
Vue.component('relationship-fieldtype', RelationshipFieldtype);
Vue.component('relationship-fieldtype-index', RelationshipIndexFieldtype);
Vue.component('replicator-fieldtype', Replicator);
Vue.component('replicator-fieldtype-set', ReplicatorSet);
Vue.component('replicator-fieldtype-index', ReplicatorIndex);
Vue.component('section-fieldtype', SectionFieldtype);
Vue.component('select-fieldtype', SelectFieldtype);
Vue.component('sets-fieldtype', SetsFieldtype);
Vue.component('table-fieldtype', TableFieldtype);
Vue.component('tags-fieldtype', TagsFieldtype);
Vue.component('tags-fieldtype-index', TagsIndexFieldtype);
Vue.component('taggable-fieldtype-index', TagsIndexFieldtype);
Vue.component('template_folder-fieldtype', TemplateFolderFieldtype);
Vue.component('time-fieldtype', TimeFieldtype);
Vue.component('toggle-fieldtype', ToggleFieldtype);
Vue.component('toggle-fieldtype-index', ToggleIndexFieldtype);
Vue.component('width-fieldtype', WidthFieldtype);
Vue.component('video-fieldtype', VideoFieldtype);
Vue.component('yaml-fieldtype', YamlFieldtype);
Vue.component('set-picker', SetPicker);
Vue.component('set-field', SetField);


Vue.component('revealer-fieldtype', RevealerFieldtype);
Vue.component('status-fieldtype', StatusFieldtype);
Vue.component('template-fieldtype', TemplateFieldtype);
