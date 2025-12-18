import type {Meta, StoryObj} from '@storybook/vue3';
import {Button, Description, Heading, HoverCard} from '@ui';

const meta = {
    title: 'Overlays/HoverCard',
    component: HoverCard,
    argTypes: {
        align: {
            control: 'select',
            options: ['start', 'center', 'end'],
            description: 'The preferred alignment against the trigger. May change when collisions occur. <br><br> Options: `start`, `center`, `end`',
        },
        arrow: {
            control: 'boolean',
            description: 'When `true`, an arrow is displayed near the trigger.'
        },
        delay: {
            control: 'number',
            description: 'The delay in milliseconds before the hover card opens.',
        },
        inset: {
            control: 'boolean',
            description: 'When `true`, the internal padding of the hover card is removed.',
        },
        offset: {
            control: 'number',
            description: 'The distance in pixels from the trigger.',
        },
        side: {
            control: 'select',
            options: ['top', 'bottom', 'left', 'right'],
            description: 'The preferred side of the trigger to render against when open. <br><br> Options: `top`, `bottom`, `left`, `right`',
        },
        open: {
            control: 'boolean',
            description: 'The controlled open state of the hover card.',
        },
        'update:open': {
            description: 'Event handler called when the open state of the hover card changes.',
            table: {
                category: 'events',
                type: { summary: '(value: boolean) => void' }
            }
        }
    },
} satisfies Meta<typeof HoverCard>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<HoverCard>
    <template #trigger>
        <Button text="Hover over me" variant="ghost" />
    </template>
    <Heading text="Quick Info" />
    <Description text="This is some helpful information that appears on hover." />
</HoverCard>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { HoverCard, Button, Heading, Description },
        template: defaultCode,
    }),
};

const directionCode = `
<div class="flex space-x-6 justify-center">
    <HoverCard side="top">
        <template #trigger>
            <Button text="↑ Top" size="sm" />
        </template>
        <Description text="Opens to the top" />
    </HoverCard>

    <HoverCard side="bottom">
        <template #trigger>
            <Button text="↓ Bottom" size="sm" />
        </template>
        <Description text="Opens to the bottom" />
    </HoverCard>

    <HoverCard side="left">
        <template #trigger>
            <Button text="← Left" size="sm" />
        </template>
        <Description text="Opens to the left" />
    </HoverCard>

    <HoverCard side="right">
        <template #trigger>
            <Button text="→ Right" size="sm" />
        </template>
        <Description text="Opens to the right" />
    </HoverCard>
</div>
`;

export const _Direction: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: directionCode }
        }
    },
    render: () => ({
        components: { HoverCard, Button, Description },
        template: directionCode,
    }),
};

const noArrowCode = `
<HoverCard :arrow="false">
    <template #trigger>
        <Button text="Hover over me" variant="ghost" />
    </template>
    <Description text="This hover card has no arrow pointing to the trigger." />
</HoverCard>
`;

export const _NoArrow: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: noArrowCode }
        }
    },
    render: () => ({
        components: { HoverCard, Button, Description },
        template: noArrowCode,
    }),
};

const customDelayCode = `
<div class="flex space-x-4">
    <HoverCard :delay="0">
        <template #trigger>
            <Button text="Instant" size="sm" variant="ghost" />
        </template>
        <Description text="Opens immediately" />
    </HoverCard>

    <HoverCard :delay="1000">
        <template #trigger>
            <Button text="Delayed" size="sm" variant="ghost" />
        </template>
        <Description text="Opens after 1 second" />
    </HoverCard>
</div>
`;

export const _CustomDelay: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: customDelayCode }
        }
    },
    render: () => ({
        components: { HoverCard, Button, Description },
        template: customDelayCode,
    }),
};
