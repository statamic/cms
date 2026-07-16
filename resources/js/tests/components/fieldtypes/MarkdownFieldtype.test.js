import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { computed } from 'vue';
import * as Globals from '@/bootstrap/globals';
import MarkdownFieldtype from '@/components/fieldtypes/markdown/MarkdownFieldtype.vue';
import { containerContextKey } from '@/components/ui/Publish/Container.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.cp_url = (url) => url;
window.__ = (key) => key;

test('the editable surface gets content direction while chrome does not', async () => {
    document.documentElement.setAttribute('dir', 'ltr');

    const wrapper = mount(MarkdownFieldtype, {
        props: {
            value: 'hello world',
            handle: 'markdown',
            config: { buttons: [] },
        },
        global: {
            stubs: {
                'publish-field-fullscreen-header': true,
                'ui-icon': true,
            },
            mocks: {
                $events: { $on: () => {}, $off: () => {} },
            },
            provide: {
                [containerContextKey]: {
                    direction: computed(() => 'rtl'),
                },
            },
        },
    });

    await wrapper.vm.$nextTick();

    const editor = wrapper.find('.editor');
    expect(editor.attributes('dir')).toBe('rtl');

    const cheatsheetButton = wrapper.find('[aria-label="Show Markdown Cheatsheet"]');
    expect(cheatsheetButton.attributes('dir')).toBeUndefined();
});
