import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import AssetFull from '@/components/fieldtypes/assets/AssetFull.vue';

const asset = {
    id: 'main::photo.jpg',
    basename: 'photo.jpg',
    extension: 'jpg',
    isImage: true,
    isViewable: true,
    isEditable: true,
    preview: '/assets/photo.jpg',
    thumbnail: '/cp/thumbnails/photo/small',
    values: { alt: 'A photo', focus: '50-50-1' },
};

const preview = (props = {}) => mount(AssetFull, {
    shallow: true,
    props: { asset, ...props },
});

test('full previews use the full image and fall back to a file icon on failure', async () => {
    const component = preview();

    expect(component.find('img').attributes('src')).toBe(asset.preview);

    await component.find('img').trigger('error');

    expect(component.find('img').exists()).toBe(false);
    expect(component.findComponent({ name: 'FileIcon' }).exists()).toBe(true);
});

test.each([
    ['25-50-2', '0% 50%', '50% 50%', 'scale(2)'],
    ['75-50-1', '100% 50%', '50% 50%', 'scale(1)'],
    ['0-0-1', '0% 50%', '0% 0%', 'scale(1)'],
    ['100-100-1', '100% 50%', '100% 100%', 'scale(1)'],
])('full previews center and clamp the focal point %s', async (focus, objectPosition, transformOrigin, transform) => {
    const component = preview({
        aspectRatio: '1/1',
        asset: { ...asset, values: { ...asset.values, focus } },
    });

    await component.setData({ imageRatio: 2 });

    expect(component.vm.previewStyle).toEqual({ objectPosition, transformOrigin, transform });
});

test('changing the focal point and aspect ratio refreshes the crop without reloading the image', async () => {
    const component = preview({ aspectRatio: '1/1' });
    await component.setData({ imageRatio: 0.5 });

    await component.setProps({ asset: { ...asset, values: { ...asset.values, focus: '50-75-1' } } });

    expect(component.vm.previewStyle.objectPosition).toBe('50% 100%');

    await component.setProps({ aspectRatio: '1/2' });

    expect(component.vm.previewStyle.objectPosition).toBe('50% 50%');
    expect(component.vm.previewStyle.transformOrigin).toBe('50% 75%');
});
