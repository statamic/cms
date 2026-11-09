import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import AssetUpload from '@/components/fieldtypes/assets/AssetUpload.vue';

test('upload progress follows the button perimeter without displaying the filename inline', async () => {
    const upload = mount(AssetUpload, {
        shallow: true,
        props: { basename: 'new.jpg', percent: 25 },
        global: { stubs: { 'confirmation-modal': true } },
    });

    expect(upload.attributes('role')).toBe('progressbar');
    expect(upload.attributes('aria-label')).toBe('new.jpg');
    expect(upload.attributes('aria-valuenow')).toBe('25');
    expect(upload.find('path').attributes('stroke-dashoffset')).toBe('75');
    expect(upload.text()).not.toContain('new.jpg');

    await upload.setProps({ percent: 100 });

    expect(upload.find('path').attributes('stroke-dashoffset')).toBe('0');
});

test('a failed upload keeps the existing conflict recovery actions', () => {
    const upload = mount(AssetUpload, {
        shallow: true,
        props: { basename: 'new.jpg', error: 'File already exists.', errorStatus: 409 },
        global: { stubs: { 'confirmation-modal': true } },
    });

    expect(upload.find('svg').exists()).toBe(false);
    expect(upload.attributes('role')).toBe('group');

    upload.vm.retryAndOverwrite();

    expect(upload.emitted('retry')).toEqual([[{ option: 'overwrite' }]]);
});
