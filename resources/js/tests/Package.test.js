import { it, expect } from 'vitest';
import * as modules from '@/bootstrap/cms/index.js';

global.__STATAMIC__ = modules;

it('exports modules', async () => {
    expect(Object.keys(modules).toSorted()).toEqual([
        'bard',
        'core',
        'savePipeline',
        'ui',
    ]);
});

it('exports core', async () => {
    const expected = [
        'ConfirmationModal',
        'DateFormatter',
        'Fieldtype',
        'FieldtypeMixin',
        'HasActions',
        'HasInputOptions',
        'HasPreferences',
        'IndexFieldtype',
        'IndexFieldtypeMixin',
        'InlineEditForm',
        'ItemActions',
        'RelatedItem',
        'RestoreRevision',
        'RevisionHistory',
        'RevisionPreview',
        'SaveButtonOptions',
        'SortableList',
        'clone',
        'requireElevatedSession',
        'requireElevatedSessionIf',
        'resetValuesFromResponse',
    ];

    expect(Object.keys(modules.core).toSorted()).toEqual(expected)
    expect(Object.keys(await import('@/package/index.js')).toSorted()).toEqual(expected);
});

it('exports save pipeline', async () => {
    const expected = [
        'AfterSaveHooks',
        'BeforeSaveHooks',
        'Pipeline',
        'PipelineStopped',
        'Request',
    ];

    expect(Object.keys(modules.savePipeline).toSorted()).toEqual(expected);
    expect(Object.keys(await import('@/package/save-pipeline.js')).toSorted()).toEqual(expected);
});

it('exports bard', async () => {
    const expected = [
        'ToolbarButtonMixin',
    ];

    expect(Object.keys(modules.bard).toSorted()).toEqual(expected);
    expect(Object.keys(await import('@/package/bard.js')).toSorted()).toEqual(expected);
});

it('exports ui', async () => {
    const expected = [
        'AuthCard',
        'Badge',
        'Button',
        'ButtonGroup',
        'Calendar',
        'Card',
        'CardList',
        'CardListItem',
        'CardPanel',
        'CharacterCounter',
        'Checkbox',
        'CheckboxGroup',
        'CodeEditor',
        'Combobox',
        'Context',
        'ContextFooter',
        'ContextItem',
        'ContextLabel',
        'ContextMenu',
        'ContextSeparator',
        'CreateForm',
        'DatePicker',
        'DateRangePicker',
        'Description',
        'DragHandle',
        'Dropdown',
        'DropdownItem',
        'DropdownLabel',
        'DropdownMenu',
        'DropdownSeparator',
        'Editable',
        'EmptyStateItem',
        'EmptyStateMenu',
        'ErrorMessage',
        'Field',
        'FieldsProvider',
        'Header',
        'Heading',
        'Icon',
        'Input',
        'Label',
        'Listing',
        'ListingCustomizeColumns',
        'ListingFilters',
        'ListingHeaderCell',
        'ListingPagination',
        'ListingPresetTrigger',
        'ListingPresets',
        'ListingRowActions',
        'ListingSearch',
        'ListingTable',
        'ListingTableBody',
        'ListingTableHead',
        'ListingToggleAll',
        'LivePreview',
        'Modal',
        'ModalClose',
        'ModalTitle',
        'Pagination',
        'Panel',
        'PanelFooter',
        'PanelHeader',
        'Popover',
        'PublishComponents',
        'PublishContainer',
        'PublishField',
        'PublishFields',
        'PublishForm',
        'PublishLocalizations',
        'PublishSections',
        'PublishTabs',
        'Radio',
        'RadioGroup',
        'Select',
        'Separator',
        'Skeleton',
        'Slider',
        'SplitterGroup',
        'SplitterPanel',
        'SplitterResizeHandle',
        'StatusIndicator',
        'Subheading',
        'Switch',
        'TabContent',
        'TabList',
        'TabProvider',
        'TabTrigger',
        'Table',
        'TableCell',
        'TableColumn',
        'TableColumns',
        'TableRow',
        'TableRows',
        'Tabs',
        'Textarea',
        'TimePicker',
        'ToggleGroup',
        'ToggleItem',
        'Tooltip',
        'Widget',
        'injectPublishContext',
        'publishContextKey',
    ];

    expect(Object.keys(modules.ui).toSorted()).toEqual(expected);
    expect(Object.keys(await import('@/package/ui.js')).toSorted()).toEqual(expected);
});
