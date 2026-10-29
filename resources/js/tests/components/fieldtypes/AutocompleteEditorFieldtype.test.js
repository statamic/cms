import { mount } from '@vue/test-utils';
import { describe, expect, test } from 'vitest';
import * as Globals from '@/bootstrap/globals';
import AutocompleteEditorFieldtype from '@/components/fieldtypes/AutocompleteEditorFieldtype.vue';
import AutocompleteEditor from '@/components/ui/AutocompleteEditor/AutocompleteEditor.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.__ = (key) => key;

const options = [
    { value: 'first_name', label: 'First Name' },
    { value: 'email', label: 'Email' },
];

async function mountField(value, config = {}) {
    const wrapper = mount(AutocompleteEditorFieldtype, {
        props: {
            value,
            handle: 'subject',
            meta: { options },
            config: { buttons: ['bold', 'italic', 'h2', 'bulletlist'], ...config },
        },
    });

    await wrapper.vm.$nextTick();
    await wrapper.vm.$nextTick();

    return wrapper;
}

const editorOf = (wrapper) => wrapper.findComponent(AutocompleteEditor);

const paragraph = (...content) => [{ type: 'paragraph', content }];

function emitContent(wrapper, content) {
    editorOf(wrapper).vm.$emit('update:modelValue', content);

    return wrapper.emitted('update:value').at(-1)[0];
}

test('markdown mode is the default and stores markdown', async () => {
    const wrapper = await mountField('Hi **there** [[ first_name ]]');

    expect(editorOf(wrapper).props('modelValue')).toEqual(
        paragraph(
            { type: 'text', text: 'Hi ' },
            { type: 'text', text: 'there', marks: [{ type: 'bold' }] },
            { type: 'text', text: ' ' },
            { type: 'mention', attrs: { value: 'first_name', label: 'First Name' } },
        ),
    );

    expect(emitContent(wrapper, paragraph({ type: 'text', text: '*a* [[' }))).toBe('\\*a\\* \\[\\[');
});

test('markdown mode keeps the toolbar and formatting', async () => {
    const wrapper = await mountField('', { markdown: true });

    expect(wrapper.findAll('button').map((button) => button.attributes('aria-label'))).toEqual([
        'Bold',
        'Italic',
        'Heading 2',
        'Unordered List',
    ]);
    expect(Object.keys(editorOf(wrapper).vm.editor.schema.marks)).toEqual(['bold', 'italic']);
});

test('text mode stores plain text', async () => {
    const wrapper = await mountField('**not bold** \\[[ x ]] [[ first_name ]]', { markdown: false });

    expect(editorOf(wrapper).props('modelValue')).toEqual(
        paragraph(
            { type: 'text', text: '**not bold** [[ x ]] ' },
            { type: 'mention', attrs: { value: 'first_name', label: 'First Name' } },
        ),
    );

    expect(emitContent(wrapper, paragraph({ type: 'text', text: '*a* & [[' }))).toBe('*a* & \\[[');
});

test('text mode hides the toolbar and allows no formatting, even when buttons are configured', async () => {
    const wrapper = await mountField('', { markdown: false });
    const { schema } = editorOf(wrapper).vm.editor;

    expect(editorOf(wrapper).props('inline')).toBe(true);
    expect(editorOf(wrapper).props('buttons')).toEqual([]);
    expect(wrapper.findAll('button')).toHaveLength(0);
    expect(Object.keys(schema.marks)).toEqual([]);
    expect(Object.keys(schema.nodes).sort()).toEqual(['doc', 'mention', 'paragraph', 'text']);
});

test('text mode forces inline even when configured as a block editor', async () => {
    const wrapper = await mountField('', { markdown: false, inline: false });

    expect(editorOf(wrapper).props('inline')).toBe(true);
});

test('text mode allows hard breaks when line breaks are enabled', async () => {
    const wrapper = await mountField('one\ntwo', { markdown: false, enable_line_breaks: true });

    expect(Object.keys(editorOf(wrapper).vm.editor.schema.nodes)).toContain('hardBreak');
    expect(editorOf(wrapper).props('modelValue')).toEqual(
        paragraph({ type: 'text', text: 'one' }, { type: 'hardBreak' }, { type: 'text', text: 'two' }),
    );
    expect(emitContent(wrapper, editorOf(wrapper).props('modelValue'))).toBe('one\ntwo');
});

describe.each([
    ['markdown', {}],
    ['text', { markdown: false }],
])('%s mode', (mode, config) => {
    test('mention labels are hydrated from the options', async () => {
        const wrapper = await mountField('[[ email ]]', config);

        expect(editorOf(wrapper).props('modelValue')).toEqual(
            paragraph({ type: 'mention', attrs: { value: 'email', label: 'Email' } }),
        );
    });

    test('a mention with no matching option renders as a missing chip', async () => {
        const wrapper = await mountField('[[ deleted ]]', config);
        const badge = wrapper.find('[data-ui-badge]');

        expect(badge.text()).toBe('deleted');
        expect(badge.classes()).toContain('line-through');
    });

    test('the empty value renders an empty editor and stores nothing', async () => {
        const wrapper = await mountField(null, config);

        expect(editorOf(wrapper).props('modelValue')).toEqual([]);
        expect(emitContent(wrapper, [{ type: 'paragraph' }])).toBe('');
    });
});
