import { it, expect } from 'vitest';
import * as modules from '@/bootstrap/cms/index.js';
import * as uiComponents from '@/components/ui/index.js';

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
        'colorMode',
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
        'inertia',
        'keys',
        'permissions',
        'portals',
        'preferences',
        'progress',
        'reveal',
        'slug',
        'stacks',
        'toast',
    ];

    expect(Object.keys(modules.api).toSorted()).toEqual(expected)
    expect(Object.keys(await import('@statamic/cms/api.js')).toSorted()).toEqual(expected);
});

it('exports inertia', async () => {
    const expected = [
        'Form',
        'Head',
        'Link',
        'router',
        'toggleArchitecturalBackground',
        'useArchitecturalBackground',
        'useForm',
        'usePoll',
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

    const expectedCmsPackageExports = [
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
        'Stack',
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
        'registerIconSet',
        'registerIconSetFromStrings',
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
        'LivePreviewPopout',
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

    // UI components.
    // Defined in resources/js/components/ui/index.js.
    expect(Object.keys(uiComponents).toSorted()).toEqual(expectedCmsPackageExports);

    // The @statamic/cms package has a UI module that exposes the same set of components.
    // It will expect it to be exposed to window.__STATAMIC__.ui.
    // Defined in resources/js/bootstrap/cms/ui.js
    expect(Object.keys(modules.ui).toSorted()).toEqual(expectedCmsPackageExports);

    // Finally, check that @statamic/cms/ui will have the same exports.
    // It's not the actual items, but the keys should match.
    // Defined in packages/cms/src/ui.js
    expect(Object.keys(await import('@statamic/cms/ui.js')).toSorted()).toEqual(expectedCmsPackageExports);
});
