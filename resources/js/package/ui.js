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

export const AuthCard = (typeof window !== 'undefined' && window.StatamicCms?.ui?.AuthCard) || createProxy();
export const Badge = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Badge) || createProxy();
export const Button = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Button) || createProxy();
export const ButtonGroup = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ButtonGroup) || createProxy();
export const Calendar = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Calendar) || createProxy();
export const Card = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Card) || createProxy();
export const CardList = (typeof window !== 'undefined' && window.StatamicCms?.ui?.CardList) || createProxy();
export const CardListItem = (typeof window !== 'undefined' && window.StatamicCms?.ui?.CardListItem) || createProxy();
export const CardPanel = (typeof window !== 'undefined' && window.StatamicCms?.ui?.CardPanel) || createProxy();
export const CharacterCounter = (typeof window !== 'undefined' && window.StatamicCms?.ui?.CharacterCounter) || createProxy();
export const Checkbox = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Checkbox) || createProxy();
export const CheckboxGroup = (typeof window !== 'undefined' && window.StatamicCms?.ui?.CheckboxGroup) || createProxy();
export const CodeEditor = (typeof window !== 'undefined' && window.StatamicCms?.ui?.CodeEditor) || createProxy();
export const Combobox = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Combobox) || createProxy();
export const Context = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Context) || createProxy();
export const ContextFooter = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ContextFooter) || createProxy();
export const ContextItem = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ContextItem) || createProxy();
export const ContextLabel = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ContextLabel) || createProxy();
export const ContextMenu = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ContextMenu) || createProxy();
export const ContextSeparator = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ContextSeparator) || createProxy();
export const CreateForm = (typeof window !== 'undefined' && window.StatamicCms?.ui?.CreateForm) || createProxy();
export const DatePicker = (typeof window !== 'undefined' && window.StatamicCms?.ui?.DatePicker) || createProxy();
export const DateRangePicker = (typeof window !== 'undefined' && window.StatamicCms?.ui?.DateRangePicker) || createProxy();
export const Description = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Description) || createProxy();
export const DragHandle = (typeof window !== 'undefined' && window.StatamicCms?.ui?.DragHandle) || createProxy();
export const Dropdown = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Dropdown) || createProxy();
export const DropdownItem = (typeof window !== 'undefined' && window.StatamicCms?.ui?.DropdownItem) || createProxy();
export const DropdownLabel = (typeof window !== 'undefined' && window.StatamicCms?.ui?.DropdownLabel) || createProxy();
export const DropdownMenu = (typeof window !== 'undefined' && window.StatamicCms?.ui?.DropdownMenu) || createProxy();
export const DropdownSeparator = (typeof window !== 'undefined' && window.StatamicCms?.ui?.DropdownSeparator) || createProxy();
export const Editable = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Editable) || createProxy();
export const ErrorMessage = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ErrorMessage) || createProxy();
export const EmptyStateItem = (typeof window !== 'undefined' && window.StatamicCms?.ui?.EmptyStateItem) || createProxy();
export const EmptyStateMenu = (typeof window !== 'undefined' && window.StatamicCms?.ui?.EmptyStateMenu) || createProxy();
export const Field = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Field) || createProxy();
export const FieldsProvider = (typeof window !== 'undefined' && window.StatamicCms?.ui?.FieldsProvider) || createProxy();
export const Header = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Header) || createProxy();
export const Heading = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Heading) || createProxy();
export const Icon = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Icon) || createProxy();
export const Input = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Input) || createProxy();
export const Label = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Label) || createProxy();
export const Listing = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Listing) || createProxy();
export const ListingCustomizeColumns = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ListingCustomizeColumns) || createProxy();
export const ListingFilters = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ListingFilters) || createProxy();
export const ListingHeaderCell = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ListingHeaderCell) || createProxy();
export const ListingPagination = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ListingPagination) || createProxy();
export const ListingPresets = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ListingPresets) || createProxy();
export const ListingPresetTrigger = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ListingPresetTrigger) || createProxy();
export const ListingRowActions = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ListingRowActions) || createProxy();
export const ListingSearch = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ListingSearch) || createProxy();
export const ListingTable = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ListingTable) || createProxy();
export const ListingTableBody = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ListingTableBody) || createProxy();
export const ListingTableHead = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ListingTableHead) || createProxy();
export const ListingToggleAll = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ListingToggleAll) || createProxy();
export const LivePreview = (typeof window !== 'undefined' && window.StatamicCms?.ui?.LivePreview) || createProxy();
export const Modal = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Modal) || createProxy();
export const ModalClose = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ModalClose) || createProxy();
export const ModalTitle = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ModalTitle) || createProxy();
export const Pagination = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Pagination) || createProxy();
export const Panel = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Panel) || createProxy();
export const PanelFooter = (typeof window !== 'undefined' && window.StatamicCms?.ui?.PanelFooter) || createProxy();
export const PanelHeader = (typeof window !== 'undefined' && window.StatamicCms?.ui?.PanelHeader) || createProxy();
export const Popover = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Popover) || createProxy();
export const PublishComponents = (typeof window !== 'undefined' && window.StatamicCms?.ui?.PublishComponents) || createProxy();
export const PublishContainer = (typeof window !== 'undefined' && window.StatamicCms?.ui?.PublishContainer) || createProxy();
export const publishContextKey = (typeof window !== 'undefined' && window.StatamicCms?.ui?.publishContextKey) || createProxy();
export const injectPublishContext = (typeof window !== 'undefined' && window.StatamicCms?.ui?.injectPublishContext) || createProxy();
export const PublishField = (typeof window !== 'undefined' && window.StatamicCms?.ui?.PublishField) || createProxy();
export const PublishFields = (typeof window !== 'undefined' && window.StatamicCms?.ui?.PublishFields) || createProxy();
export const PublishForm = (typeof window !== 'undefined' && window.StatamicCms?.ui?.PublishForm) || createProxy();
export const PublishLocalizations = (typeof window !== 'undefined' && window.StatamicCms?.ui?.PublishLocalizations) || createProxy();
export const PublishSections = (typeof window !== 'undefined' && window.StatamicCms?.ui?.PublishSections) || createProxy();
export const PublishTabs = (typeof window !== 'undefined' && window.StatamicCms?.ui?.PublishTabs) || createProxy();
export const Radio = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Radio) || createProxy();
export const RadioGroup = (typeof window !== 'undefined' && window.StatamicCms?.ui?.RadioGroup) || createProxy();
export const Select = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Select) || createProxy();
export const Separator = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Separator) || createProxy();
export const Slider = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Slider) || createProxy();
export const Skeleton = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Skeleton) || createProxy();
export const SplitterGroup = (typeof window !== 'undefined' && window.StatamicCms?.ui?.SplitterGroup) || createProxy();
export const SplitterPanel = (typeof window !== 'undefined' && window.StatamicCms?.ui?.SplitterPanel) || createProxy();
export const SplitterResizeHandle = (typeof window !== 'undefined' && window.StatamicCms?.ui?.SplitterResizeHandle) || createProxy();
export const StatusIndicator = (typeof window !== 'undefined' && window.StatamicCms?.ui?.StatusIndicator) || createProxy();
export const Subheading = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Subheading) || createProxy();
export const Switch = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Switch) || createProxy();
export const TabContent = (typeof window !== 'undefined' && window.StatamicCms?.ui?.TabContent) || createProxy();
export const Table = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Table) || createProxy();
export const TableCell = (typeof window !== 'undefined' && window.StatamicCms?.ui?.TableCell) || createProxy();
export const TableColumn = (typeof window !== 'undefined' && window.StatamicCms?.ui?.TableColumn) || createProxy();
export const TableColumns = (typeof window !== 'undefined' && window.StatamicCms?.ui?.TableColumns) || createProxy();
export const TableRow = (typeof window !== 'undefined' && window.StatamicCms?.ui?.TableRow) || createProxy();
export const TableRows = (typeof window !== 'undefined' && window.StatamicCms?.ui?.TableRows) || createProxy();
export const TabList = (typeof window !== 'undefined' && window.StatamicCms?.ui?.TabList) || createProxy();
export const TabProvider = (typeof window !== 'undefined' && window.StatamicCms?.ui?.TabProvider) || createProxy();
export const Tabs = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Tabs) || createProxy();
export const TabTrigger = (typeof window !== 'undefined' && window.StatamicCms?.ui?.TabTrigger) || createProxy();
export const Textarea = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Textarea) || createProxy();
export const TimePicker = (typeof window !== 'undefined' && window.StatamicCms?.ui?.TimePicker) || createProxy();
export const ToggleGroup = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ToggleGroup) || createProxy();
export const ToggleItem = (typeof window !== 'undefined' && window.StatamicCms?.ui?.ToggleItem) || createProxy();
export const Tooltip = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Tooltip) || createProxy();
export const Widget = (typeof window !== 'undefined' && window.StatamicCms?.ui?.Widget) || createProxy();