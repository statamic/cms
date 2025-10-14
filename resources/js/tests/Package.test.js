import { it, expect } from 'vitest';
import * as modules from '@/bootstrap/cms/index.js';
import * as uiComponentLibrary from '../../../packages/ui/src/index.js';
import * as internalUiComponents from '@/components/ui/index.js';

global.__STATAMIC__ = modules;

it('exports modules', async () => {
    expect(Object.keys(modules).toSorted()).toEqual([
        'api',
        'bard',
        'core',
        'inertia',
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

it('exports api', async () => {
    const expected = [
        'bard',
        'callbacks',
        'commandPalette',
        'components',
        'conditions',
        'config',
        'contrast',
        'dateFormatter',
        'dirty',
        'echo',
        'events',
        'fieldActions',
        'hooks',
        'keys',
        'permissions',
        'portals',
        'preferences',
        'progress',
        'reveal',
        'slug',
        'stacks',
        'theme',
        'toast',
    ];

    expect(Object.keys(modules.api).toSorted()).toEqual(expected)
    expect(Object.keys(await import('@statamic/cms/api.js')).toSorted()).toEqual(expected);
});

it('exports inertia', async () => {
    const expected = [
        'Head',
        'Link',
        'router',
    ];

    expect(Object.keys(modules.inertia).toSorted()).toEqual(expected);
    expect(Object.keys(await import('@statamic/cms/inertia.js')).toSorted()).toEqual(expected);
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
    // The multiple sets of exports is admittedly messy, so this test ensures that if you add
    // a component to one place that you don't forget to add it to the other places.

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
        'HoverCard',
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
        'Avatar',
        'CommandPaletteItem',
        'CreateForm',
        'DocsCallout',
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

    // Internally, we use `import { Something } from @ui`.
    // This is a merge of the UI component library and additional CP-specific components.
    // Defined in resources/js/components/ui/index.js.
    expect(Object.keys(internalUiComponents).toSorted()).toEqual(expectedCmsPackageExports);

    // The @statamic/cms package has a UI module that exposes the same set of components.
    // It will expect it to be exposed to window.__STATAMIC__.ui.
    // Defined in resources/js/bootstrap/cms/ui.js
    expect(Object.keys(modules.ui).toSorted()).toEqual(expectedCmsPackageExports);

    // Finally, check that @statamic/cms/ui will have the same exports.
    // It's not the actual items, but the keys should match.
    // Defined in packages/cms/src/ui.js
    expect(Object.keys(await import('@statamic/cms/ui.js')).toSorted()).toEqual(expectedCmsPackageExports);
});
