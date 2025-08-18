// Runtime exports for UI components
// These are provided by window.StatamicCms during runtime
// Addons should not compile Statamic's source code
if (typeof window !== 'undefined' && window.StatamicCms?.ui) {
    // Export all UI components from the global StatamicCms
    const ui = window.StatamicCms.ui;

    // Re-export individual components
    for (const [name, component] of Object.entries(ui)) {
        if (typeof component !== 'undefined') {
            globalThis[name] = component;
        }
    }
}

// For build-time/SSR, provide empty exports to satisfy module resolution
const createProxy = () => new Proxy({}, {
    get: () => createProxy(),
    set: () => true,
    has: () => true
});

const StatamicCms = typeof window !== 'undefined' && window.StatamicCms ? window.StatamicCms : {};

export const AuthCard = StatamicCms.ui?.AuthCard || createProxy();
export const Badge = StatamicCms.ui?.Badge || createProxy();
export const Button = StatamicCms.ui?.Button || createProxy();
export const ButtonGroup = StatamicCms.ui?.ButtonGroup || createProxy();
export const Calendar = StatamicCms.ui?.Calendar || createProxy();
export const Card = StatamicCms.ui?.Card || createProxy();
export const CardList = StatamicCms.ui?.CardList || createProxy();
export const CardListItem = StatamicCms.ui?.CardListItem || createProxy();
export const CardPanel = StatamicCms.ui?.CardPanel || createProxy();
export const CharacterCounter = StatamicCms.ui?.CharacterCounter || createProxy();
export const Checkbox = StatamicCms.ui?.Checkbox || createProxy();
export const CheckboxGroup = StatamicCms.ui?.CheckboxGroup || createProxy();
export const CodeEditor = StatamicCms.ui?.CodeEditor || createProxy();
export const Combobox = StatamicCms.ui?.Combobox || createProxy();
export const Context = StatamicCms.ui?.Context || createProxy();
export const ContextFooter = StatamicCms.ui?.ContextFooter || createProxy();
export const ContextItem = StatamicCms.ui?.ContextItem || createProxy();
export const ContextLabel = StatamicCms.ui?.ContextLabel || createProxy();
export const ContextMenu = StatamicCms.ui?.ContextMenu || createProxy();
export const ContextSeparator = StatamicCms.ui?.ContextSeparator || createProxy();
export const CreateForm = StatamicCms.ui?.CreateForm || createProxy();
export const DatePicker = StatamicCms.ui?.DatePicker || createProxy();
export const DateRangePicker = StatamicCms.ui?.DateRangePicker || createProxy();
export const Description = StatamicCms.ui?.Description || createProxy();
export const DragHandle = StatamicCms.ui?.DragHandle || createProxy();
export const Dropdown = StatamicCms.ui?.Dropdown || createProxy();
export const DropdownItem = StatamicCms.ui?.DropdownItem || createProxy();
export const DropdownLabel = StatamicCms.ui?.DropdownLabel || createProxy();
export const DropdownMenu = StatamicCms.ui?.DropdownMenu || createProxy();
export const DropdownSeparator = StatamicCms.ui?.DropdownSeparator || createProxy();
export const Editable = StatamicCms.ui?.Editable || createProxy();
export const ErrorMessage = StatamicCms.ui?.ErrorMessage || createProxy();
export const EmptyStateItem = StatamicCms.ui?.EmptyStateItem || createProxy();
export const EmptyStateMenu = StatamicCms.ui?.EmptyStateMenu || createProxy();
export const Field = StatamicCms.ui?.Field || createProxy();
export const FieldsProvider = StatamicCms.ui?.FieldsProvider || createProxy();
export const Header = StatamicCms.ui?.Header || createProxy();
export const Heading = StatamicCms.ui?.Heading || createProxy();
export const Icon = StatamicCms.ui?.Icon || createProxy();
export const Input = StatamicCms.ui?.Input || createProxy();
export const Label = StatamicCms.ui?.Label || createProxy();
export const Listing = StatamicCms.ui?.Listing || createProxy();
export const ListingCustomizeColumns = StatamicCms.ui?.ListingCustomizeColumns || createProxy();
export const ListingFilters = StatamicCms.ui?.ListingFilters || createProxy();
export const ListingHeaderCell = StatamicCms.ui?.ListingHeaderCell || createProxy();
export const ListingPagination = StatamicCms.ui?.ListingPagination || createProxy();
export const ListingPresets = StatamicCms.ui?.ListingPresets || createProxy();
export const ListingPresetTrigger = StatamicCms.ui?.ListingPresetTrigger || createProxy();
export const ListingRowActions = StatamicCms.ui?.ListingRowActions || createProxy();
export const ListingSearch = StatamicCms.ui?.ListingSearch || createProxy();
export const ListingTable = StatamicCms.ui?.ListingTable || createProxy();
export const ListingTableBody = StatamicCms.ui?.ListingTableBody || createProxy();
export const ListingTableHead = StatamicCms.ui?.ListingTableHead || createProxy();
export const ListingToggleAll = StatamicCms.ui?.ListingToggleAll || createProxy();
export const LivePreview = StatamicCms.ui?.LivePreview || createProxy();
export const Modal = StatamicCms.ui?.Modal || createProxy();
export const ModalClose = StatamicCms.ui?.ModalClose || createProxy();
export const ModalTitle = StatamicCms.ui?.ModalTitle || createProxy();
export const Pagination = StatamicCms.ui?.Pagination || createProxy();
export const Panel = StatamicCms.ui?.Panel || createProxy();
export const PanelFooter = StatamicCms.ui?.PanelFooter || createProxy();
export const PanelHeader = StatamicCms.ui?.PanelHeader || createProxy();
export const Popover = StatamicCms.ui?.Popover || createProxy();
export const PublishComponents = StatamicCms.ui?.PublishComponents || createProxy();
export const PublishContainer = StatamicCms.ui?.PublishContainer || createProxy();
export const publishContextKey = StatamicCms.ui?.publishContextKey || createProxy();
export const injectPublishContext = StatamicCms.ui?.injectPublishContext || createProxy();
export const PublishField = StatamicCms.ui?.PublishField || createProxy();
export const PublishFields = StatamicCms.ui?.PublishFields || createProxy();
export const PublishForm = StatamicCms.ui?.PublishForm || createProxy();
export const PublishLocalizations = StatamicCms.ui?.PublishLocalizations || createProxy();
export const PublishSections = StatamicCms.ui?.PublishSections || createProxy();
export const PublishTabs = StatamicCms.ui?.PublishTabs || createProxy();
export const Radio = StatamicCms.ui?.Radio || createProxy();
export const RadioGroup = StatamicCms.ui?.RadioGroup || createProxy();
export const Select = StatamicCms.ui?.Select || createProxy();
export const Separator = StatamicCms.ui?.Separator || createProxy();
export const Slider = StatamicCms.ui?.Slider || createProxy();
export const Skeleton = StatamicCms.ui?.Skeleton || createProxy();
export const SplitterGroup = StatamicCms.ui?.SplitterGroup || createProxy();
export const SplitterPanel = StatamicCms.ui?.SplitterPanel || createProxy();
export const SplitterResizeHandle = StatamicCms.ui?.SplitterResizeHandle || createProxy();
export const StatusIndicator = StatamicCms.ui?.StatusIndicator || createProxy();
export const Subheading = StatamicCms.ui?.Subheading || createProxy();
export const Switch = StatamicCms.ui?.Switch || createProxy();
export const TabContent = StatamicCms.ui?.TabContent || createProxy();
export const Table = StatamicCms.ui?.Table || createProxy();
export const TableCell = StatamicCms.ui?.TableCell || createProxy();
export const TableColumn = StatamicCms.ui?.TableColumn || createProxy();
export const TableColumns = StatamicCms.ui?.TableColumns || createProxy();
export const TableRow = StatamicCms.ui?.TableRow || createProxy();
export const TableRows = StatamicCms.ui?.TableRows || createProxy();
export const TabList = StatamicCms.ui?.TabList || createProxy();
export const TabProvider = StatamicCms.ui?.TabProvider || createProxy();
export const Tabs = StatamicCms.ui?.Tabs || createProxy();
export const TabTrigger = StatamicCms.ui?.TabTrigger || createProxy();
export const Textarea = StatamicCms.ui?.Textarea || createProxy();
export const TimePicker = StatamicCms.ui?.TimePicker || createProxy();
export const ToggleGroup = StatamicCms.ui?.ToggleGroup || createProxy();
export const ToggleItem = StatamicCms.ui?.ToggleItem || createProxy();
export const Tooltip = StatamicCms.ui?.Tooltip || createProxy();
export const Widget = StatamicCms.ui?.Widget || createProxy();
