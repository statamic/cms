// Runtime UI exports - provided by window.StatamicCms.ui
// Addons should not compile Statamic's source code
const ui = (typeof window !== 'undefined' && window.StatamicCms?.ui) || {};
const createProxy = () => new Proxy({}, { get: () => createProxy(), set: () => true, has: () => true });

export const AuthCard = ui.AuthCard || createProxy();
export const Badge = ui.Badge || createProxy();
export const Button = ui.Button || createProxy();
export const ButtonGroup = ui.ButtonGroup || createProxy();
export const Calendar = ui.Calendar || createProxy();
export const Card = ui.Card || createProxy();
export const CardList = ui.CardList || createProxy();
export const CardListItem = ui.CardListItem || createProxy();
export const CardPanel = ui.CardPanel || createProxy();
export const CharacterCounter = ui.CharacterCounter || createProxy();
export const Checkbox = ui.Checkbox || createProxy();
export const CheckboxGroup = ui.CheckboxGroup || createProxy();
export const CodeEditor = ui.CodeEditor || createProxy();
export const Combobox = ui.Combobox || createProxy();
export const Context = ui.Context || createProxy();
export const ContextFooter = ui.ContextFooter || createProxy();
export const ContextItem = ui.ContextItem || createProxy();
export const ContextLabel = ui.ContextLabel || createProxy();
export const ContextMenu = ui.ContextMenu || createProxy();
export const ContextSeparator = ui.ContextSeparator || createProxy();
export const CreateForm = ui.CreateForm || createProxy();
export const DatePicker = ui.DatePicker || createProxy();
export const DateRangePicker = ui.DateRangePicker || createProxy();
export const Description = ui.Description || createProxy();
export const DragHandle = ui.DragHandle || createProxy();
export const Dropdown = ui.Dropdown || createProxy();
export const DropdownItem = ui.DropdownItem || createProxy();
export const DropdownLabel = ui.DropdownLabel || createProxy();
export const DropdownMenu = ui.DropdownMenu || createProxy();
export const DropdownSeparator = ui.DropdownSeparator || createProxy();
export const Editable = ui.Editable || createProxy();
export const ErrorMessage = ui.ErrorMessage || createProxy();
export const EmptyStateItem = ui.EmptyStateItem || createProxy();
export const EmptyStateMenu = ui.EmptyStateMenu || createProxy();
export const Field = ui.Field || createProxy();
export const FieldsProvider = ui.FieldsProvider || createProxy();
export const Header = ui.Header || createProxy();
export const Heading = ui.Heading || createProxy();
export const Icon = ui.Icon || createProxy();
export const Input = ui.Input || createProxy();
export const Label = ui.Label || createProxy();
export const Listing = ui.Listing || createProxy();
export const ListingCustomizeColumns = ui.ListingCustomizeColumns || createProxy();
export const ListingFilters = ui.ListingFilters || createProxy();
export const ListingHeaderCell = ui.ListingHeaderCell || createProxy();
export const ListingPagination = ui.ListingPagination || createProxy();
export const ListingPresets = ui.ListingPresets || createProxy();
export const ListingPresetTrigger = ui.ListingPresetTrigger || createProxy();
export const ListingRowActions = ui.ListingRowActions || createProxy();
export const ListingSearch = ui.ListingSearch || createProxy();
export const ListingTable = ui.ListingTable || createProxy();
export const ListingTableBody = ui.ListingTableBody || createProxy();
export const ListingTableHead = ui.ListingTableHead || createProxy();
export const ListingToggleAll = ui.ListingToggleAll || createProxy();
export const LivePreview = ui.LivePreview || createProxy();
export const Modal = ui.Modal || createProxy();
export const ModalClose = ui.ModalClose || createProxy();
export const ModalTitle = ui.ModalTitle || createProxy();
export const Pagination = ui.Pagination || createProxy();
export const Panel = ui.Panel || createProxy();
export const PanelFooter = ui.PanelFooter || createProxy();
export const PanelHeader = ui.PanelHeader || createProxy();
export const Popover = ui.Popover || createProxy();
export const PublishComponents = ui.PublishComponents || createProxy();
export const PublishContainer = ui.PublishContainer || createProxy();
export const publishContextKey = ui.publishContextKey || createProxy();
export const injectPublishContext = ui.injectPublishContext || createProxy();
export const PublishField = ui.PublishField || createProxy();
export const PublishFields = ui.PublishFields || createProxy();
export const PublishForm = ui.PublishForm || createProxy();
export const PublishLocalizations = ui.PublishLocalizations || createProxy();
export const PublishSections = ui.PublishSections || createProxy();
export const PublishTabs = ui.PublishTabs || createProxy();
export const Radio = ui.Radio || createProxy();
export const RadioGroup = ui.RadioGroup || createProxy();
export const Select = ui.Select || createProxy();
export const Separator = ui.Separator || createProxy();
export const Slider = ui.Slider || createProxy();
export const Skeleton = ui.Skeleton || createProxy();
export const SplitterGroup = ui.SplitterGroup || createProxy();
export const SplitterPanel = ui.SplitterPanel || createProxy();
export const SplitterResizeHandle = ui.SplitterResizeHandle || createProxy();
export const StatusIndicator = ui.StatusIndicator || createProxy();
export const Subheading = ui.Subheading || createProxy();
export const Switch = ui.Switch || createProxy();
export const TabContent = ui.TabContent || createProxy();
export const Table = ui.Table || createProxy();
export const TableCell = ui.TableCell || createProxy();
export const TableColumn = ui.TableColumn || createProxy();
export const TableColumns = ui.TableColumns || createProxy();
export const TableRow = ui.TableRow || createProxy();
export const TableRows = ui.TableRows || createProxy();
export const TabList = ui.TabList || createProxy();
export const TabProvider = ui.TabProvider || createProxy();
export const Tabs = ui.Tabs || createProxy();
export const TabTrigger = ui.TabTrigger || createProxy();
export const Textarea = ui.Textarea || createProxy();
export const TimePicker = ui.TimePicker || createProxy();
export const ToggleGroup = ui.ToggleGroup || createProxy();
export const ToggleItem = ui.ToggleItem || createProxy();
export const Tooltip = ui.Tooltip || createProxy();
export const Widget = ui.Widget || createProxy();

// Legacy CommonJS export for compatibility
export default {
    AuthCard: ui.AuthCard || createProxy(),
    Badge: ui.Badge || createProxy(),
    Button: ui.Button || createProxy(),
    ButtonGroup: ui.ButtonGroup || createProxy(),
    Calendar: ui.Calendar || createProxy(),
    Card: ui.Card || createProxy(),
    CardList: ui.CardList || createProxy(),
    CardListItem: ui.CardListItem || createProxy(),
    CardPanel: ui.CardPanel || createProxy(),
    CharacterCounter: ui.CharacterCounter || createProxy(),
    Checkbox: ui.Checkbox || createProxy(),
    CheckboxGroup: ui.CheckboxGroup || createProxy(),
    CodeEditor: ui.CodeEditor || createProxy(),
    Combobox: ui.Combobox || createProxy(),
    Context: ui.Context || createProxy(),
    ContextFooter: ui.ContextFooter || createProxy(),
    ContextItem: ui.ContextItem || createProxy(),
    ContextLabel: ui.ContextLabel || createProxy(),
    ContextMenu: ui.ContextMenu || createProxy(),
    ContextSeparator: ui.ContextSeparator || createProxy(),
    CreateForm: ui.CreateForm || createProxy(),
    DatePicker: ui.DatePicker || createProxy(),
    DateRangePicker: ui.DateRangePicker || createProxy(),
    Description: ui.Description || createProxy(),
    DragHandle: ui.DragHandle || createProxy(),
    Dropdown: ui.Dropdown || createProxy(),
    DropdownItem: ui.DropdownItem || createProxy(),
    DropdownLabel: ui.DropdownLabel || createProxy(),
    DropdownMenu: ui.DropdownMenu || createProxy(),
    DropdownSeparator: ui.DropdownSeparator || createProxy(),
    Editable: ui.Editable || createProxy(),
    ErrorMessage: ui.ErrorMessage || createProxy(),
    EmptyStateItem: ui.EmptyStateItem || createProxy(),
    EmptyStateMenu: ui.EmptyStateMenu || createProxy(),
    Field: ui.Field || createProxy(),
    FieldsProvider: ui.FieldsProvider || createProxy(),
    Header: ui.Header || createProxy(),
    Heading: ui.Heading || createProxy(),
    Icon: ui.Icon || createProxy(),
    Input: ui.Input || createProxy(),
    Label: ui.Label || createProxy(),
    Listing: ui.Listing || createProxy(),
    ListingCustomizeColumns: ui.ListingCustomizeColumns || createProxy(),
    ListingFilters: ui.ListingFilters || createProxy(),
    ListingHeaderCell: ui.ListingHeaderCell || createProxy(),
    ListingPagination: ui.ListingPagination || createProxy(),
    ListingPresets: ui.ListingPresets || createProxy(),
    ListingPresetTrigger: ui.ListingPresetTrigger || createProxy(),
    ListingRowActions: ui.ListingRowActions || createProxy(),
    ListingSearch: ui.ListingSearch || createProxy(),
    ListingTable: ui.ListingTable || createProxy(),
    ListingTableBody: ui.ListingTableBody || createProxy(),
    ListingTableHead: ui.ListingTableHead || createProxy(),
    ListingToggleAll: ui.ListingToggleAll || createProxy(),
    LivePreview: ui.LivePreview || createProxy(),
    Modal: ui.Modal || createProxy(),
    ModalClose: ui.ModalClose || createProxy(),
    ModalTitle: ui.ModalTitle || createProxy(),
    Pagination: ui.Pagination || createProxy(),
    Panel: ui.Panel || createProxy(),
    PanelFooter: ui.PanelFooter || createProxy(),
    PanelHeader: ui.PanelHeader || createProxy(),
    Popover: ui.Popover || createProxy(),
    PublishComponents: ui.PublishComponents || createProxy(),
    PublishContainer: ui.PublishContainer || createProxy(),
    publishContextKey: ui.publishContextKey || createProxy(),
    injectPublishContext: ui.injectPublishContext || createProxy(),
    PublishField: ui.PublishField || createProxy(),
    PublishFields: ui.PublishFields || createProxy(),
    PublishForm: ui.PublishForm || createProxy(),
    PublishLocalizations: ui.PublishLocalizations || createProxy(),
    PublishSections: ui.PublishSections || createProxy(),
    PublishTabs: ui.PublishTabs || createProxy(),
    Radio: ui.Radio || createProxy(),
    RadioGroup: ui.RadioGroup || createProxy(),
    Select: ui.Select || createProxy(),
    Separator: ui.Separator || createProxy(),
    Slider: ui.Slider || createProxy(),
    Skeleton: ui.Skeleton || createProxy(),
    SplitterGroup: ui.SplitterGroup || createProxy(),
    SplitterPanel: ui.SplitterPanel || createProxy(),
    SplitterResizeHandle: ui.SplitterResizeHandle || createProxy(),
    StatusIndicator: ui.StatusIndicator || createProxy(),
    Subheading: ui.Subheading || createProxy(),
    Switch: ui.Switch || createProxy(),
    TabContent: ui.TabContent || createProxy(),
    Table: ui.Table || createProxy(),
    TableCell: ui.TableCell || createProxy(),
    TableColumn: ui.TableColumn || createProxy(),
    TableColumns: ui.TableColumns || createProxy(),
    TableRow: ui.TableRow || createProxy(),
    TableRows: ui.TableRows || createProxy(),
    TabList: ui.TabList || createProxy(),
    TabProvider: ui.TabProvider || createProxy(),
    Tabs: ui.Tabs || createProxy(),
    TabTrigger: ui.TabTrigger || createProxy(),
    Textarea: ui.Textarea || createProxy(),
    TimePicker: ui.TimePicker || createProxy(),
    ToggleGroup: ui.ToggleGroup || createProxy(),
    ToggleItem: ui.ToggleItem || createProxy(),
    Tooltip: ui.Tooltip || createProxy(),
    Widget: ui.Widget || createProxy()
};