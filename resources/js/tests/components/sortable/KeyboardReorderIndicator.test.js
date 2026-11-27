import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import KeyboardReorderIndicator from '@/components/sortable/KeyboardReorderIndicator.vue';

const keys = (props) => mount(KeyboardReorderIndicator, { props: { moving: true, ...props } }).findAll('kbd').map((kbd) => kbd.text());

test('a list shows only ↑ ↓', () => {
    expect(keys({})).toEqual(['↑', '↓']);
    expect(keys({ can: { in: true, out: true } })).toEqual(['↑', '↓']);
});

test('a tree shows ← and → only when they would do something', () => {
    expect(keys({ horizontal: true, can: { in: false, out: false } })).toEqual(['↑', '↓']);
    expect(keys({ horizontal: true, can: { in: true, out: false } })).toEqual(['↑', '↓', '→']);
    expect(keys({ horizontal: true, can: { in: false, out: true } })).toEqual(['←', '↑', '↓']);
    expect(keys({ horizontal: true, can: { in: true, out: true } })).toEqual(['←', '↑', '↓', '→']);
});

test('nothing shows when it is not moving', () => {
    expect(mount(KeyboardReorderIndicator, { props: { moving: false, horizontal: true, can: { in: true, out: true } } }).findAll('kbd')).toHaveLength(0);
});
