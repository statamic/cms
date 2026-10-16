import useAiChat from '@/composables/use-ai-chat.js';

export default function registerGlobalCommandPalette() {
    // Global command palette link items should be registered and cached on the PHP side,
    // but we can register global JS-based command palette items here if need be...
    //
    // Statamic.$commandPalette.add({
    //     text: __('Example Link Command'),
    //     icon: 'wand',
    //     when: () => Statamic.$permissions.has('super'),
    //     action: () => { ... },
    // });

    // Prototype: open the AI chat from the palette. It's listed with its keyboard shortcut, like the other commands that have one.
    const { focus } = useAiChat();

    Statamic.$commandPalette.add({
        category: Statamic.$commandPalette.category.Actions,
        text: __('Ask'),
        icon: 'ai-chat',
        keys: 'mod+/',
        persist: true,
        // The palette hands focus back to the page after it has closed, so wait for that before moving focus into the chat
        action: () => setTimeout(focus, 450),
    });
}
