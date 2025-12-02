import type { Meta, StoryObj } from '@storybook/vue3';
import { Radio, RadioGroup } from '@ui';

const meta = {
    title: 'Components/Radio',
    component: Radio,
    argTypes: {
        label: { control: 'text' },
        description: { control: 'text' },
        value: { control: 'text' },
        disabled: { control: 'boolean' },
        checked: { control: 'boolean' },
    },
} satisfies Meta<typeof Radio>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<RadioGroup name="favorite" label="Choose your favorite Star Wars movie">
    <Radio label="A New Hope" value="ep4" />
    <Radio label="Empire Strikes Back" value="ep5" />
    <Radio label="Return of the Jedi" value="ep6" />
</RadioGroup>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Radio, RadioGroup },
        template: defaultCode,
    }),
};

const descriptionsCode = `
<RadioGroup name="favorite" label="Choose your favorite meal">
    <Radio label="Breakfast" description="The morning meal. Should include eggs." value="breakfast" checked />
    <Radio label="Lunch" description="The mid-day meal. Should be protein heavy." value="lunch" />
    <Radio label="Dinner" description="The evening meal Should be delicious." value="dinner" />
</RadioGroup>
`;

export const _Descriptions: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: descriptionsCode }
        }
    },
    render: () => ({
        components: { Radio, RadioGroup },
        template: descriptionsCode,
    }),
};

const disabledCode = `
<RadioGroup name="favorite" label="Choose your favorite Star Wars movie">
    <Radio label="A New Hope" value="ep4"/>
    <Radio label="Empire Strikes Back" value="ep5" />
    <Radio label="Return of the Jedi" value="ep6" />
    <Radio disabled label="the Force Awakens" value="ep7" />
    <Radio disabled label="The Last Jedi" value="ep8" />
    <Radio disabled label="The Rise of Skywalker" value="ep9" />
</RadioGroup>
`;

export const _Disabled: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: disabledCode }
        }
    },
    render: () => ({
        components: { Radio, RadioGroup },
        template: disabledCode,
    }),
};
