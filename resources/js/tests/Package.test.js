import { it, expect } from 'vitest';
import * as modules from '@/bootstrap/cms/index.js';
import * as uiComponentLibrary from '../../../packages/ui/src/index.js';
import * as internalUiComponents from '@/components/ui/index.js';


global.__STATAMIC__ = modules;

it('exports modules', async () => {
    expect(Object.keys(modules).toSorted()).toEqual([
        'bard',
        'core',
        'savePipeline',
        'temporary',
        'ui',
    ]);
});

it('exports core', async () => {
    const expected = [
        'DateFormatter',
        'Fieldtype',
        'FieldtypeMixin',
        'IndexFieldtype',
        'IndexFieldtypeMixin',
        'ItemActions',
        'requireElevatedSession',
        'requireElevatedSessionIf',
    ];

    expect(Object.keys(modules.core).toSorted()).toEqual(expected)
    expect(Object.keys(await import('@statamic/cms/index.js')).toSorted()).toEqual(expected);
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
    expect(Object.keys(await import('@statamic/cms/save-pipeline.js')).toSorted()).toEqual(expected);
});

it('exports bard', async () => {
    const expected = [
        'ToolbarButtonMixin',
    ];

    expect(Object.keys(modules.bard).toSorted()).toEqual(expected);
    expect(Object.keys(await import('@statamic/cms/bard.js')).toSorted()).toEqual(expected);
});

it('exports ui', async () => {
    const expectedUiPackageExports = [
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
        'DatePicker',
        'DateRangePicker',
        'Description',
        'DragHandle',
        'Drawer',
        'Dropdown',
        'DropdownFooter',
        'DropdownHeader',
        'DropdownItem',
        'DropdownLabel',
        'DropdownMenu',
        'DropdownSeparator',
        'Editable',
        'EmptyStateItem',
        'EmptyStateMenu',
        'ErrorMessage',
        'Field',
        'Header',
        'Heading',
        'Icon',
        'Input',
        'InputGroup',
        'InputGroupAppend',
        'InputGroupPrepend',
        'Label',
        'Modal',
        'ModalClose',
        'ModalTitle',
        'Pagination',
        'Panel',
        'PanelFooter',
        'PanelHeader',
        'Popover',
        'Radio',
        'RadioGroup',
        'Select',
        'Separator',
        'Skeleton',
        'Slider',
        'SplitterGroup',
        'SplitterPanel',
        'SplitterResizeHandle',
        'Subheading',
        'Switch',
        'TabContent',
        'TabList',
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
        'registerIconSet',
        'registerIconSetFromStrings',
    ].toSorted();

    const expectedCmsPackageExports = [
        ...expectedUiPackageExports,
        'CommandPaletteItem',
        'CreateForm',
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
        'PublishComponents',
        'PublishContainer',
        'PublishField',
        'PublishFields',
        'PublishFieldsProvider',
        'PublishForm',
        'PublishLocalizations',
        'PublishSections',
        'PublishTabs',
        'StatusIndicator',
        'TabProvider',
        'Widget',
        'injectPublishContext',
        'publishContextKey',
    ].toSorted();

    // The UI components package contains a subset of all the UI components. It only contains
    // the more generic components that might be used outside the control panel.
    // Defined in packages/ui/index.js.
    expect(Object.keys(uiComponentLibrary).toSorted()).toEqual(expectedUiPackageExports);

    // Internally, we use `import { Something } from @ui`. This is a merge of the UI component
    // library and additional CP-specific components. Defined in resources/js/components/ui/index.js.
    expect(Object.keys(internalUiComponents).toSorted()).toEqual(expectedCmsPackageExports);

    // The @statamic/cms package has a UI module that exposes the same set of components.
    // It will expect it to be exposed to window.__STATAMIC__.ui.
    expect(Object.keys(modules.ui).toSorted()).toEqual(expectedCmsPackageExports);

    // Finally, check that @statamic/cms/ui will have the same exports.
    // It's not the actual items, but the keys should match.
    expect(Object.keys(await import('@statamic/cms/ui.js')).toSorted()).toEqual(expectedCmsPackageExports);

});
