export const categories = {
    structure: { get title() { return __('Structure'); }, color: 'purple' },
    information: { get title() { return __('Information'); }, color: 'pink' },
    text: { get title() { return __('Text'); }, color: 'purple' },
    choice: { get title() { return __('Choice'); }, color: 'orange' },
    rate: { get title() { return __('Rate'); }, color: 'amber' },
    contact: { get title() { return __('Contact Info'); }, color: 'blue' },
    number: { get title() { return __('Number'); }, color: 'teal' },
    datetime: { get title() { return __('Date and Time'); }, color: 'fuchsia' },
    media: { get title() { return __('Media'); }, color: 'cyan' },
    payment: { get title() { return __('Payment'); }, color: 'green' },
    fieldsets: { get title() { return __('Fieldsets'); }, color: 'gray' },
    other: { get title() { return __('Other'); }, color: 'gray' },
};

export const collectsValue = (category) => !['information', 'structure'].includes(category);

export const categoryColorClasses = {
    purple: { dot: 'bg-purple-500', icon: 'text-purple-600 dark:text-purple-400' },
    pink: { dot: 'bg-pink-500', icon: 'text-pink-600 dark:text-pink-400' },
    orange: { dot: 'bg-orange-500', icon: 'text-orange-600 dark:text-orange-400' },
    amber: { dot: 'bg-green-500', icon: 'text-green-600 dark:text-green-400' },
    blue: { dot: 'bg-blue-500', icon: 'text-blue-600 dark:text-blue-400' },
    teal: { dot: 'bg-teal-500', icon: 'text-teal-600 dark:text-teal-400' },
    fuchsia: { dot: 'bg-fuchsia-500', icon: 'text-fuchsia-600 dark:text-fuchsia-400' },
    cyan: { dot: 'bg-cyan-500', icon: 'text-cyan-600 dark:text-cyan-400' },
    green: { dot: 'bg-green-500', icon: 'text-green-600 dark:text-green-400' },
    gray: { dot: 'bg-gray-500', icon: 'text-gray-600 dark:text-gray-400' },
};
