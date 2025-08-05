export default function registerGlobalCommandPalette() {
    Statamic.$commandPalette.add({
        text: __('Statamic Documentation'),
        category: 'Miscellaneous',
        icon: 'book-next-page',
        url: 'https://statamic.dev',
        openNewTab: true,
        when: () => Statamic.$permissions.has('super'),
    });
}
