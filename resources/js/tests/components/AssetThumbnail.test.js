import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import Thumbnail from '@/components/assets/Browser/Thumbnail.vue';

const stubs = { 'file-icon': true };

test('a raster thumbnail is decorative by default', () => {
    const img = mount(Thumbnail, {
        props: { asset: { thumbnail: '/cp/thumbnails/abc/small' } },
        global: { stubs },
    }).find('img');

    expect(img.exists()).toBe(true);
    expect(img.attributes('alt')).toBe('');
});

test('an svg thumbnail is decorative by default', () => {
    const img = mount(Thumbnail, {
        props: { asset: { is_svg: true, url: '/assets/logo.svg' } },
        global: { stubs },
    }).find('img');

    expect(img.exists()).toBe(true);
    expect(img.attributes('alt')).toBe('');
});

test('alt can be supplied when the thumbnail has no adjacent name', () => {
    const img = mount(Thumbnail, {
        props: { asset: { thumbnail: '/cp/thumbnails/abc/small' }, alt: 'sample-one.png' },
        global: { stubs },
    }).find('img');

    expect(img.attributes('alt')).toBe('sample-one.png');
});
