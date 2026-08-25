import { mount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import * as Globals from '@/bootstrap/globals';
import BardFieldtype from '@/components/fieldtypes/bard/BardFieldtype.vue';
import { containerContextKey } from '@/components/ui/Publish/Container.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.cp_url = (url) => url;
window.__ = (key, replacements = {}) =>
    Object.entries(replacements).reduce((string, [key, value]) => string.replace(`:${key}`, value), key);
window.__n = (key, count) => key.split('|')[count === 1 ? 0 : 1].replace(/:count/g, count);

// Five words, twenty four characters.
const value = [{ type: 'paragraph', content: [{ type: 'text', text: 'One two three four five.' }] }];

async function mountField(config = {}) {
    const wrapper = mount(BardFieldtype, {
        props: {
            value,
            handle: 'content',
            config: { sets: [], buttons: ['bold'], ...config },
            meta: { existing: {}, collapsed: [], defaults: {}, new: {} },
        },
        global: {
            stubs: {
                'publish-field-fullscreen-header': true,
                'ui-icon': true,
                'set-picker': true,
            },
            mocks: {
                $bard: { extensionCallbacks: [], extensionReplacementCallbacks: [], buttonCallbacks: [] },
                $events: { $on: () => {}, $off: () => {} },
            },
            provide: {
                [containerContextKey]: {
                    values: { value: {} },
                    previews: { value: {} },
                    errors: { value: {} },
                    setFieldValue: vi.fn(),
                    setFieldMeta: vi.fn(),
                },
            },
        },
    });

    await vi.waitUntil(() => wrapper.vm.editor);
    await wrapper.vm.$nextTick();

    return wrapper;
}

async function type(wrapper, text) {
    wrapper.vm.editor.commands.insertContent(text);
    await wrapper.vm.$nextTick();
}

beforeEach(() => {
    window.Statamic = {
        $components: { has: () => true, register: () => {} },
        $fieldActions: { get: () => [] },
        $commandPalette: { preventIf: () => {}, add: () => {} },
        $config: { get: () => null },
    };
});

afterEach(() => vi.restoreAllMocks());

// Serializing the whole document is expensive, and the reading time is the only thing that needs it.
test('the document is not serialized to html on update when reading time is disabled', async () => {
    const wrapper = await mountField({ reading_time: false });
    const getHTML = vi.spyOn(wrapper.vm.editor, 'getHTML');

    await type(wrapper, ' six seven eight.');

    expect(getHTML).not.toHaveBeenCalled();
});

test('the document is serialized to html on update when reading time is enabled', async () => {
    const wrapper = await mountField({ reading_time: true });
    const getHTML = vi.spyOn(wrapper.vm.editor, 'getHTML');

    await type(wrapper, ' six seven eight.');

    expect(getHTML).toHaveBeenCalled();
    expect(wrapper.vm.html).toContain('six seven eight.');
});

test('reading time is rendered when enabled', async () => {
    const wrapper = await mountField({ reading_time: true });

    await type(wrapper, ' six seven eight.');

    expect(wrapper.vm.readingTime).toMatch(/^\d{2}:\d{2}$/);
    expect(wrapper.find('.bard-footer-toolbar').text()).toContain(wrapper.vm.readingTime);
});

// The counts come from the character count extension's storage rather than the serialized
// html, so they are rendered whether or not the html is being kept up to date.
test('word and character counts are rendered when reading time is disabled', async () => {
    const wrapper = await mountField({ reading_time: false, word_count: true, character_limit: 100 });

    expect(wrapper.find('.bard-footer-toolbar').text()).toBe('5 words, 24/100 characters');
});
