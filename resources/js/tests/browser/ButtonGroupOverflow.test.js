import { afterEach, expect, test } from 'vitest';
import { mount } from '@vue/test-utils';
import { h } from 'vue';
import { Button, ButtonGroup } from '@ui';
import '../../../css/app.css';

let wrapper;
let host;
afterEach(() => {
    wrapper?.unmount();
    host?.remove();
});

const mountButtonGroup = (width) => {
    host = document.createElement('div');
    host.style.width = width;
    document.body.appendChild(host);

    wrapper = mount(
        {
            render: () =>
                h(ButtonGroup, { overflow: 'stack' }, () =>
                    ['Left aligned', 'Centered', 'Right aligned'].map((text) => h(Button, { text })),
                ),
        },
        { attachTo: host },
    );

    return wrapper.find('[data-ui-button-group]').element;
};

const isStacked = (group) => group.classList.contains('flex-col');

const waitFor = async (condition) => {
    for (let i = 0; i < 50 && !condition(); i++) {
        await new Promise((resolve) => setTimeout(resolve, 20));
    }
};

test('button group stays in a row when it fits', async () => {
    const group = mountButtonGroup('800px');

    await new Promise((resolve) => setTimeout(resolve, 200));

    expect(isStacked(group)).toBe(false);
});

test('button group stacks when it overflows', async () => {
    const group = mountButtonGroup('120px');

    await waitFor(() => isStacked(group));

    expect(isStacked(group)).toBe(true);
});

test('button group stacks and unstacks as it is resized', async () => {
    const group = mountButtonGroup('800px');

    host.style.width = '120px';
    await waitFor(() => isStacked(group));
    expect(isStacked(group)).toBe(true);

    host.style.width = '800px';
    await waitFor(() => !isStacked(group));
    expect(isStacked(group)).toBe(false);
});
