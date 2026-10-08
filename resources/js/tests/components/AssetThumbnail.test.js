import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import Thumbnail from '@/components/assets/Browser/Thumbnail.vue';
import AssetsIndexFieldtype from '@/components/fieldtypes/assets/AssetsIndexFieldtype.vue';

const stubs = { 'file-icon': true };

test('a raster thumbnail can be decorative', () => {
    const img = mount(Thumbnail, {
        props: { asset: { thumbnail: '/cp/thumbnails/abc/small' }, alt: '' },
        global: { stubs },
    }).find('img');

    expect(img.exists()).toBe(true);
    expect(img.attributes('alt')).toBe('');
});

test('an svg thumbnail can be decorative', () => {
    const img = mount(Thumbnail, {
        props: { asset: { is_svg: true, url: '/assets/logo.svg' }, alt: '' },
        global: { stubs },
    }).find('img');

    expect(img.exists()).toBe(true);
    expect(img.attributes('alt')).toBe('');
});

test('an asset index thumbnail names its link with the asset basename', () => {
    const wrapper = mount(AssetsIndexFieldtype, {
        props: {
            handle: 'assets',
            value: {
                assets: [{
                    id: 'assets::sample-one.png',
                    basename: 'sample-one.png',
                    thumbnail: '/cp/thumbnails/abc/small',
                    url: '/assets/sample-one.png',
                }],
                total: 1,
            },
            values: {},
        },
        global: { stubs },
    });

    expect(wrapper.get('a img').attributes('alt')).toBe('sample-one.png');
});
