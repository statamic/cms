import { defineAsyncComponent } from 'vue';
import RevealerFieldtype from '../components/fieldtypes/RevealerFieldtype.vue';
import TemplateFieldtype from '../components/fieldtypes/TemplateFieldtype.vue';
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
import BardButtonsSettingFieldtype from '../components/fieldtypes/bard/BardButtonsSettingFieldtype.vue';
import ButtonGroupFieldtype from '../components/fieldtypes/ButtonGroupFieldtype.vue';
import CheckboxesFieldtype from '../components/fieldtypes/CheckboxesFieldtype.vue';
import Routes from '../components/collections/Routes.vue';
import TitleFormats from '../components/collections/TitleFormats.vue';
import ColorFieldtype from '../components/fieldtypes/ColorFieldtype.vue';
import DateFieldtype from '../components/fieldtypes/DateFieldtype.vue';
import DateIndexFieldtype from '../components/fieldtypes/DateIndexFieldtype.vue';
import DictionaryFieldtype from '../components/fieldtypes/DictionaryFieldtype.vue';
import DictionaryIndexFieldtype from '../components/fieldtypes/DictionaryIndexFieldtype.vue';
import DictionaryFields from '../components/fieldtypes/DictionaryFields.vue';
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
import ToggleFieldtype from '../components/fieldtypes/ToggleFieldtype.vue';
import ToggleIndexFieldtype from '../components/fieldtypes/ToggleIndexFieldtype.vue';
import WidthFieldtype from '../components/fieldtypes/WidthFieldtype.vue';
import VideoFieldtype from '../components/fieldtypes/VideoFieldtype.vue';
import SetPicker from '../components/fieldtypes/replicator/SetPicker.vue';
import SetField from '../components/fieldtypes/replicator/Field.vue';

export default function registerFieldtypes(app) {
    app.component('select-input', Select);
    app.component('text-input', Text);
    app.component('textarea-input', Textarea);
    app.component('toggle-input', Toggle);
    app.component('relationship-input', RelationshipInput);
    app.component('text-fieldtype', TextFieldtype);
    app.component('textarea-fieldtype', TextareaFieldtype);
    app.component('slug-fieldtype', SlugFieldtype);
    app.component('array-fieldtype', ArrayFieldtype);
    app.component('assets-fieldtype', AssetsFieldtype);
    app.component('assets-fieldtype-index', AssetsIndexFieldtype);
    app.component('asset_folder-fieldtype', AssetFolderFieldtype);
    app.component(
        'bard-fieldtype',
        defineAsyncComponent(() => import('../components/fieldtypes/bard/BardFieldtype.vue')),
    );
    app.component(
        'bard-fieldtype-set',
        defineAsyncComponent(() => import('../components/fieldtypes/bard/Set.vue')),
    );
    app.component('bard_buttons_setting-fieldtype', BardButtonsSettingFieldtype);
    app.component('button_group-fieldtype', ButtonGroupFieldtype);
    app.component('checkboxes-fieldtype', CheckboxesFieldtype);
    app.component(
        'code-fieldtype',
        defineAsyncComponent(() => import('../components/fieldtypes/CodeFieldtype.vue')),
    );
    app.component('collection_routes-fieldtype', Routes);
    app.component('collection_title_formats-fieldtype', TitleFormats);
    app.component('color-fieldtype', ColorFieldtype);
    app.component('date-fieldtype', DateFieldtype);
    app.component('date-fieldtype-index', DateIndexFieldtype);
    app.component('dictionary-fieldtype', DictionaryFieldtype);
    app.component('dictionary-fieldtype-index', DictionaryIndexFieldtype);
    app.component('dictionary_fields-fieldtype', DictionaryFields);
    app.component('field_display-fieldtype', FieldDisplayFieldtype);
    app.component('fields-fieldtype', FieldsFieldtype);
    app.component('files-fieldtype', FilesFieldtype);
    app.component('float-fieldtype', FloatFieldtype);
    app.component('global_set_sites-fieldtype', Sites);
    app.component('grid-fieldtype', Grid);
    app.component('grid-fieldtype-index', GridIndex);
    app.component('group-fieldtype', GroupFieldtype);
    app.component('hidden-fieldtype', HiddenFieldtype);
    app.component('html-fieldtype', HtmlFieldtype);
    app.component('icon-fieldtype', IconFieldtype);
    app.component('integer-fieldtype', IntegerFieldtype);
    app.component('link-fieldtype', LinkFieldtype);
    app.component('list-fieldtype', ListFieldtype);
    app.component('list-fieldtype-index', ListIndexFieldtype);
    app.component(
        'markdown-fieldtype',
        defineAsyncComponent(() => import('../components/fieldtypes/markdown/MarkdownFieldtype.vue')),
    );
    app.component('markdown_buttons_setting-fieldtype', MarkdownButtonsSettingFieldtype);
    app.component('radio-fieldtype', RadioFieldtype);
    app.component('range-fieldtype', RangeFieldtype);
    app.component('relationship-fieldtype', RelationshipFieldtype);
    app.component('relationship-fieldtype-index', RelationshipIndexFieldtype);
    app.component('replicator-fieldtype', Replicator);
    app.component('replicator-fieldtype-set', ReplicatorSet);
    app.component('replicator-fieldtype-index', ReplicatorIndex);
    app.component('section-fieldtype', SectionFieldtype);
    app.component('select-fieldtype', SelectFieldtype);
    app.component('sets-fieldtype', SetsFieldtype);
    app.component('table-fieldtype', TableFieldtype);
    app.component('tags-fieldtype', TagsFieldtype);
    app.component('tags-fieldtype-index', TagsIndexFieldtype);
    app.component('taggable-fieldtype-index', TagsIndexFieldtype);
    app.component('template_folder-fieldtype', TemplateFolderFieldtype);
    app.component(
        'time-fieldtype',
        defineAsyncComponent(() => import('../components/fieldtypes/TimeFieldtype.vue')),
    );
    app.component('toggle-fieldtype', ToggleFieldtype);
    app.component('toggle-fieldtype-index', ToggleIndexFieldtype);
    app.component('width-fieldtype', WidthFieldtype);
    app.component('video-fieldtype', VideoFieldtype);
    app.component(
        'yaml-fieldtype',
        defineAsyncComponent(() => import('../components/fieldtypes/YamlFieldtype.vue')),
    );
    app.component('set-picker', SetPicker);
    app.component('set-field', SetField);
    app.component('revealer-fieldtype', RevealerFieldtype);
    app.component('template-fieldtype', TemplateFieldtype);
}
