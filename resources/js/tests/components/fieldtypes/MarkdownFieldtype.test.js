import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { computed, ref } from 'vue';
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

test('the CodeMirror instance is initialized with the content direction', async () => {
    const wrapper = mountField(ref('rtl'));
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.codemirror.getOption('direction')).toBe('rtl');
});

test('the CodeMirror instance reacts to content direction changes', async () => {
    const direction = ref('ltr');
    const wrapper = mountField(direction);
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.codemirror.getOption('direction')).toBe('ltr');

    direction.value = 'rtl';
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.codemirror.getOption('direction')).toBe('rtl');
});
