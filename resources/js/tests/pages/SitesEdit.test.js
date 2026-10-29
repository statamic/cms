import { mount } from '@vue/test-utils';
import { afterEach, expect, test, vi } from 'vitest';
import { data_set } from '@/bootstrap/globals';
import Edit from '@/pages/sites/Edit.vue';

let multisiteEnabled = false;

globalThis.Statamic = {
    $config: { get: (key) => (key === 'multisiteEnabled' ? multisiteEnabled : undefined) },
    $keys: { bindGlobal: () => ({ destroy: () => {} }) },
    $commandPalette: { add: () => {}, category: { Actions: 'Actions' } },
};

function mountEdit(initialValues) {
    return mount(Edit, {
        props: { blueprint: null, initialValues, meta: {}, updateUrl: '/update' },
        global: {
            stubs: {
                Head: true,
                Header: true,
                CommandPaletteItem: true,
                Button: true,
                PublishContainer: true,
                DocsCallout: true,
            },
            mocks: {
                $commandPalette: { category: { Actions: 'Actions' } },
            },
        },
    });
}

afterEach(() => {
    multisiteEnabled = false;
    vi.restoreAllMocks();
});

test('it warns before saving a changed site handle', () => {
    const confirm = vi.spyOn(window, 'confirm').mockReturnValue(false);
    const wrapper = mountEdit({ handle: 'default', name: 'Default' });

    data_set(wrapper.vm.values, 'handle', 'main');
    wrapper.vm.save();

    expect(confirm).toHaveBeenCalledOnce();
});

test('it warns before saving a changed handle when multisite is enabled', () => {
    multisiteEnabled = true;
    const confirm = vi.spyOn(window, 'confirm').mockReturnValue(false);
    const wrapper = mountEdit({ sites: [{ handle: 'default' }, { handle: 'french' }] });

    data_set(wrapper.vm.values, 'sites.0.handle', 'english');
    wrapper.vm.save();

    expect(confirm).toHaveBeenCalledOnce();
});
