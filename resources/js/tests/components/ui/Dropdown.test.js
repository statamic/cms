import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import Dropdown from '@/components/ui/Dropdown/Dropdown.vue';

window.__ = (key) => key;

function mountDropdown(provide = {}) {
    return mount(Dropdown, {
        global: {
            provide,
            stubs: {
                DropdownMenuRoot: { props: ['modal'], template: '<div :data-modal="String(modal ?? \'default\')"><slot /></div>' },
                DropdownMenuTrigger: true,
                DropdownMenuPortal: true,
            },
        },
    });
}

test('dropdowns are modal', () => {
    expect(mountDropdown().find('[data-modal]').attributes('data-modal')).toBe('true');
});

// Modal menus mark everything else aria-hidden, including the editor's other nodes, which makes
// ProseMirror re-parse them and recreate the set the dropdown lives in.
test('dropdowns inside a bard field are not modal', () => {
    expect(mountDropdown({ isInBardField: true }).find('[data-modal]').attributes('data-modal')).toBe('false');
});
