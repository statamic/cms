import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { computed, ref } from 'vue';
import { createPinia } from 'pinia';
import * as Globals from '@/bootstrap/globals';
import MarkdownFieldtype from '@/components/fieldtypes/markdown/MarkdownFieldtype.vue';
import { containerContextKey } from '@/components/ui/Publish/Container.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.cp_url = (url) => url;
window.__ = (key) => key;

function mountField(direction) {
    return mount(MarkdownFieldtype, {
        props: {
            value: 'hello world',
            handle: 'markdown',
            config: { buttons: [] },
        },
        global: {
            plugins: [createPinia()],
            stubs: {
                'publish-field-fullscreen-header': true,
                'ui-icon': true,
            },
            mocks: {
                $events: { $on: () => {}, $off: () => {} },
            },
            provide: {
                [containerContextKey]: { direction },
            },
        },
    });
}

test('chrome does not get an explicit content direction', async () => {
    document.documentElement.setAttribute('dir', 'ltr');

    const wrapper = mountField(computed(() => 'rtl'));
    await wrapper.vm.$nextTick();

    const cheatsheetButton = wrapper.find('[aria-label="Show Markdown Cheatsheet"]');
    expect(cheatsheetButton.attributes('dir')).toBeUndefined();
});

// The editor is source/code-adjacent and is intentionally always ltr,
// regardless of content direction (see PR #10931 and its revert #10992).
test('the CodeMirror editor stays ltr regardless of content direction', async () => {
    const wrapper = mountField(ref('rtl'));
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.codemirror.getOption('direction')).toBe('ltr');
    expect(wrapper.vm.codemirror.getWrapperElement().getAttribute('dir')).toBeNull();
});

test('the preview pane gets the content direction', async () => {
    const wrapper = mountField(ref('rtl'));
    await wrapper.vm.$nextTick();

    // The preview pane is always in the DOM (toggled via v-show), so its
    // dir attribute can be asserted without switching into preview mode.
    expect(wrapper.find('.markdown-preview').attributes('dir')).toBe('rtl');
});
