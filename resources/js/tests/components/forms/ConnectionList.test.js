import { mount } from '@vue/test-utils';
import { beforeEach, expect, test, vi } from 'vitest';
import { defineComponent, h } from 'vue';
import ConnectionList from '@/components/forms/connections/ConnectionList.vue';
import ConnectionFields from '@/components/forms/connections/ConnectionFields.vue';

vi.mock('@ui', async () => {
    const { defineComponent, h } = await import('vue');

    const passthrough = (name, props = []) => defineComponent({
        name,
        props,
        setup: (_, { slots }) => () => h('div', { 'data-component': name }, slots.default?.()),
    });

    const clickable = (name) => defineComponent({
        name,
        props: ['text'],
        emits: ['click'],
        setup: (props, { emit }) => () => h('button', { 'data-text': props.text, onClick: () => emit('click') }, props.text),
    });

    return {
        Button: clickable('Button'),
        DropdownItem: clickable('DropdownItem'),
        ConfirmationModal: passthrough('ConfirmationModal'),
        Description: passthrough('Description'),
        DragHandle: passthrough('DragHandle'),
        Dropdown: passthrough('Dropdown'),
        DropdownMenu: passthrough('DropdownMenu'),
        Switch: passthrough('Switch'),
        PublishContainer: passthrough('PublishContainer', ['blueprint', 'errors', 'modelValue', 'meta', 'extraValues', 'name', 'trackDirtyState']),
        PublishFieldsProvider: passthrough('PublishFieldsProvider', ['fields']),
        PublishFields: passthrough('PublishFields'),
    };
});

vi.mock('@/components/forms/connections/ConnectionRules.vue', async () => {
    const { defineComponent, h } = await import('vue');

    return {
        default: defineComponent({
            name: 'ConnectionRules',
            props: ['conditions', 'alwaysLabel', 'ifLabel'],
            setup: (_, { slots }) => () => h('div', { 'data-component': 'ConnectionRules' }, slots.then?.()),
        }),
    };
});

vi.mock('@/components/sortable/Sortable.js', async () => {
    const { defineComponent, h } = await import('vue');

    return { SortableList: defineComponent({ setup: (_, { slots }) => () => h('div', slots.default?.()) }) };
});

vi.mock('@api', () => ({ preferences: { get: (key, fallback) => fallback, set: vi.fn() } }));
vi.mock('@/bootstrap/globals', () => ({ __: (key) => key }));

const blueprint = {
    tabs: [
        {
            sections: [
                { fields: [{ handle: 'url' }, { handle: 'rows' }] },
                { fields: [{ handle: 'secret' }] },
            ],
        },
    ],
};

const defaults = {
    values: { url: null, rows: [] },
    meta: { url: null, rows: { existing: {} } },
};

const containers = (wrapper) => wrapper.findAllComponents({ name: 'PublishContainer' });

function makeList(props = {}, slots = {}) {
    const wrapper = mount(ConnectionList, {
        props: {
            modelValue: [],
            blueprint,
            defaults,
            'onUpdate:modelValue': (value) => wrapper.setProps({ modelValue: value }),
            ...props,
        },
        slots,
        global: { stubs: { teleport: true } },
    });

    return wrapper;
}

beforeEach(() => {
    vi.stubGlobal('__', (key) => key);
});

test('new connections get their own copy of the default meta', async () => {
    const wrapper = makeList();

    await wrapper.find('[data-text="Add Connection"]').trigger('click');
    await wrapper.find('[data-text="Add Connection"]').trigger('click');

    const [first, second] = containers(wrapper).map((container) => container.props('meta'));

    expect(first).toEqual(defaults.meta);
    expect(first).not.toBe(defaults.meta);
    expect(first).not.toBe(second);

    first.rows.existing.row = { mutated: true };

    expect(defaults.meta.rows.existing).toEqual({});
    expect(second.rows.existing).toEqual({});
});

test('existing connections are seeded from the meta prop', () => {
    const meta = { one: { url: null, rows: { existing: { row: { a: 1 } } } } };
    const wrapper = makeList({ modelValue: [{ id: 'one', conditions: [] }, { id: 'two', conditions: [] }], meta });

    const [one, two] = containers(wrapper).map((container) => container.props('meta'));

    expect(one).toEqual(meta.one);
    expect(one).not.toBe(meta.one);
    expect(two).toEqual(defaults.meta);
});

test('duplicating a connection clones its meta', async () => {
    const meta = { one: { url: null, rows: { existing: { row: { a: 1 } } } } };
    const wrapper = makeList({ modelValue: [{ id: 'one', url: 'https://example.com', conditions: [] }], meta });

    containers(wrapper)[0].props('meta').rows.existing.added = { b: 2 };

    await wrapper.find('[data-text="Duplicate"]').trigger('click');

    const [original, copy] = containers(wrapper).map((container) => container.props('meta'));

    expect(wrapper.props('modelValue')[1].url).toBe('https://example.com');
    expect(copy).toEqual({ url: null, rows: { existing: { row: { a: 1 }, added: { b: 2 } } } });
    expect(copy).not.toBe(original);
    expect(copy.rows.existing).not.toBe(original.rows.existing);
});

test('errors keep their full path within the connection', () => {
    const wrapper = makeList({
        modelValue: [{ id: 'one', conditions: [] }, { id: 'two', conditions: [] }],
        errors: {
            '0.url': ['Invalid URL'],
            '0.rows.1.label': ['Required'],
            '1.url': ['Other'],
        },
    });

    const [one, two] = containers(wrapper).map((container) => container.props('errors'));

    expect(one).toEqual({ url: ['Invalid URL'], 'rows.1.label': ['Required'] });
    expect(two).toEqual({ url: ['Other'] });
});

test('it renders rules and fields when no default slot is given', () => {
    const wrapper = makeList({
        modelValue: [{ id: 'one', url: 'https://example.com', conditions: [] }],
        name: 'webhook-connection',
        alwaysLabel: 'Always send',
        ifLabel: 'Send if...',
    });

    const rules = wrapper.findComponent({ name: 'ConnectionRules' });
    const container = rules.findComponent({ name: 'PublishContainer' });

    expect(rules.props()).toMatchObject({ alwaysLabel: 'Always send', ifLabel: 'Send if...' });
    expect(container.props()).toMatchObject({
        blueprint,
        name: 'webhook-connection-one',
        modelValue: wrapper.props('modelValue')[0],
        trackDirtyState: false,
    });
    expect(wrapper.findComponent({ name: 'PublishFieldsProvider' }).props('fields').map((field) => field.handle)).toEqual(['url', 'rows', 'secret']);
});

test('it renders only fields when rules are disabled', () => {
    const wrapper = makeList({ modelValue: [{ id: 'one', conditions: [] }], rules: false });

    expect(wrapper.findComponent({ name: 'ConnectionRules' }).exists()).toBe(false);
    expect(containers(wrapper)[0].props('name')).toBe('connection-one');
});

test('a default slot replaces the fallback body and receives meta', () => {
    const meta = { one: { url: 'meta' } };
    const slot = vi.fn(() => h('p', 'custom'));
    const wrapper = makeList({ modelValue: [{ id: 'one', conditions: [] }], meta }, { default: slot });

    expect(wrapper.findComponent({ name: 'ConnectionRules' }).exists()).toBe(false);
    expect(wrapper.text()).toContain('custom');
    expect(slot.mock.calls[0][0]).toMatchObject({ index: 0, errors: {}, meta: { url: 'meta' } });
});

test('connection fields can render a subset or exclude fields', () => {
    const fields = (props) => mount(ConnectionFields, {
        props: { connection: { id: 'one' }, blueprint, ...props },
    }).findComponent({ name: 'PublishFieldsProvider' }).props('fields').map((field) => field.handle);

    expect(fields({ fields: ['secret', 'url', 'missing'] })).toEqual(['secret', 'url']);
    expect(fields({ except: ['rows'] })).toEqual(['url', 'secret']);
});

test('connection fields work without a connection list', () => {
    const wrapper = mount(ConnectionFields, {
        props: {
            connection: { id: 'one' },
            blueprint,
            meta: { url: 'meta' },
            errors: { url: ['Invalid'] },
            extraValues: { consent: true },
            name: 'hubspot',
            bordered: false,
        },
    });

    expect(wrapper.findComponent({ name: 'PublishContainer' }).props()).toMatchObject({
        name: 'hubspot-one',
        meta: { url: 'meta' },
        errors: { url: ['Invalid'] },
        extraValues: { consent: true },
    });
    expect(wrapper.classes()).not.toContain('rounded-lg');
});

test('connection fields slots', () => {
    const wrapper = mount(ConnectionFields, {
        props: { connection: { id: 'one' }, blueprint },
        slots: {
            before: ({ connection }) => h('p', { class: 'before' }, connection.id),
            default: ({ pick }) => h('p', { class: 'picked' }, pick('secret', 'url').map((field) => field.handle).join(',')),
            after: () => h('p', { class: 'after' }, 'after'),
        },
    });

    expect(wrapper.find('.before').text()).toBe('one');
    expect(wrapper.find('.picked').text()).toBe('secret,url');
    expect(wrapper.find('.after').exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'PublishFieldsProvider' }).exists()).toBe(false);
    expect(wrapper.classes()).toContain('rounded-lg');
});
