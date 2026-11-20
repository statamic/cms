import { mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import CodeMirror from 'codemirror';
import { computed, ref } from 'vue';
import * as Globals from '@/bootstrap/globals';
import MarkdownFieldtype from '@/components/fieldtypes/markdown/MarkdownFieldtype.vue';
import { containerContextKey } from '@/components/ui/Publish/Container.vue';
import { availableButtons } from '@/components/fieldtypes/markdown/buttons';

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

function headingEditor(text) {
    const codemirror = new CodeMirror.Doc(text);
    codemirror.focus = vi.fn();

    return { codemirror, toggleHeading: MarkdownFieldtype.methods.toggleHeading };
}

test.each([1, 2, 3, 4, 5, 6])('the H%i button toggles its heading level', (level) => {
    const editor = headingEditor('hello world');
    const button = availableButtons().find((button) => button.name === `h${level}`);

    editor.codemirror.setCursor({ line: 0, ch: 5 });
    button.command(editor);
    expect(editor.codemirror.getValue()).toBe(`${'#'.repeat(level)} hello world`);

    button.command(editor);
    expect(editor.codemirror.getValue()).toBe('hello world');

    expect(editor.codemirror.focus).toHaveBeenCalledTimes(2);
});

test('heading buttons replace existing levels across selected lines', () => {
    const editor = headingEditor('# First\n  ### Second\nPlain text\n####### Not a heading');
    editor.codemirror.setSelection({ line: 0, ch: 0 }, { line: 3, ch: 24 });
    editor.toggleHeading(2);

    expect(editor.codemirror.getValue()).toBe('## First\n  ## Second\n## Plain text\n## ####### Not a heading');
});

test.each([
    ['bold', '**'],
    ['italic', '_'],
    ['strikethrough', '~~'],
    ['code', '`'],
])('the %s button toggles a selection spanning multiple lines', (type, delimiter) => {
    const codemirror = new CodeMirror.Doc('first line\nsecond line');
    codemirror.focus = vi.fn();
    const { toggleInline, getText } = MarkdownFieldtype.methods;
    const editor = { codemirror, toggleInline, getText };

    codemirror.setSelection({ line: 0, ch: 0 }, { line: 1, ch: 11 });
    editor.selections = codemirror.listSelections();
    editor.toggleInline(type);
    expect(codemirror.getValue()).toBe(`${delimiter}first line\nsecond line${delimiter}`);

    editor.selections = codemirror.listSelections();
    editor.toggleInline(type);
    expect(codemirror.getValue()).toBe('first line\nsecond line');
});

test.each([
    ['bold', ' **first line\nsecond line**\n'],
    ['code', '` first line\nsecond line\n`'],
])('the %s button handles surrounding whitespace', (type, formatted) => {
    const codemirror = new CodeMirror.Doc(' first line\nsecond line\n');
    codemirror.focus = vi.fn();
    const { toggleInline, getText } = MarkdownFieldtype.methods;
    const editor = { codemirror, toggleInline, getText };

    codemirror.setSelection({ line: 0, ch: 0 }, { line: 2, ch: 0 });
    editor.selections = codemirror.listSelections();
    editor.toggleInline(type);
    expect(codemirror.getValue()).toBe(formatted);

    editor.selections = codemirror.listSelections();
    editor.toggleInline(type);
    expect(codemirror.getValue()).toBe(' first line\nsecond line\n');
});

test('inline buttons wrap a whitespace-only selection as is', () => {
    const codemirror = new CodeMirror.Doc('before\n\n\nafter');
    codemirror.focus = vi.fn();
    const { toggleInline, getText } = MarkdownFieldtype.methods;
    const editor = { codemirror, toggleInline, getText };

    codemirror.setSelection({ line: 1, ch: 0 }, { line: 2, ch: 0 });
    editor.selections = codemirror.listSelections();
    editor.toggleInline('bold');
    expect(codemirror.getValue()).toBe('before\n**\n**\nafter');
});
