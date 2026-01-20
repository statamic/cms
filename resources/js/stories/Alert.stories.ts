import type {Meta, StoryObj} from '@storybook/vue3';
import {Alert} from '@ui';
import {icons} from './icons';

const meta = {
    title: 'Components/Alert',
    component: Alert,
    argTypes: {
        variant: {
            control: 'select',
            options: ['default', 'warning', 'error', 'success'],
        },
        icon: {
            control: 'select',
            options: icons,
        },
    },
} satisfies Meta<typeof Alert>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        text: 'This is a default alert message',
        variant: 'default',
    },
};

const defaultCode = `
<Alert variant="default" text="This is a default alert message" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Alert },
        template: defaultCode,
    }),
};

const variantsCode = `
<Alert variant="default" text="This is a default alert message" />
<Alert variant="warning" text="This is a warning alert message" />
<Alert variant="error" text="This is an error alert message" />
<Alert variant="success" text="This is a success alert message" />
`;

export const Variants: Story = {
    argTypes: {
        variant: { control: { disable: true } },
        text: { control: { disable: true } },
        icon: { control: { disable: true } },
    },
    parameters: {
        docs: {
            source: {
                code: variantsCode,
            },
        },
    },
    render: () => ({
        components: { Alert },
        template: `
            <div class="space-y-3">
                <Alert variant="default" text="This is a default alert message" />
                <Alert variant="warning" text="This is a warning alert message" />
                <Alert variant="error" text="This is an error alert message" />
                <Alert variant="success" text="This is a success alert message" />
            </div>
        `,
    }),
};

const customIconCode = `
<Alert variant="warning" icon="git" text="This alert has a custom icon" />
<Alert variant="default" icon="cog" text="This alert uses a custom icon" />
<Alert variant="success" icon="clipboard-check" text="Custom success icon" />
`;

export const CustomIcons: Story = {
    argTypes: {
        icon: { control: { disable: true } },
        text: { control: { disable: true } },
        variant: { control: { disable: true } },
    },
    parameters: {
        docs: {
            source: {
                code: customIconCode,
            },
        },
    },
    render: () => ({
        components: { Alert },
        template: `
            <div class="space-y-3">
                <Alert variant="warning" icon="git" text="This alert has a custom icon" />
                <Alert variant="default" icon="cog" text="This alert uses a custom icon" />
                <Alert variant="success" icon="clipboard-check" text="Custom success icon" />
            </div>
        `,
    }),
};

const slotContentCode = `
<Alert variant="success">
    <strong>Success!</strong> This alert uses a slot for custom content.
</Alert>
`;

export const SlotContent: Story = {
    tags: ['!dev'],
    argTypes: {
        text: { control: { disable: true } },
        variant: { control: { disable: true } },
    },
    parameters: {
        docs: {
            source: {
                code: slotContentCode,
            },
        },
    },
    render: () => ({
        components: { Alert },
        template: `
            <div class="space-y-3">
                <Alert variant="success">
                    <strong>Success!</strong> This alert uses a slot for custom content.
                </Alert>
                <Alert variant="default">
                    You can use <strong>bold text</strong>, <em>italic text</em>, and even <code>code</code> in slot content.
                </Alert>
            </div>
        `,
    }),
};

const richContentCode = `
<div class="space-y-3">
    <Alert variant="warning">
        <h1>Please run your migrations</h1>
        <p>The importer uses Laravel's job batching feature to keep track of the import progress, however, it requires a <code>job_batches</code> table in your database. Before you can run the importer, you will need to run <code>php artisan migrate</code>. This alert uses a heading for the title and a paragraph for the message.</p>
    </Alert>
    <Alert variant="default">
        <h2>New Feature Available</h2>
        <p>We've added support for custom field types. You can now create your own field types by extending the <code>Fieldtype</code> class. Check out the documentation for more details.</p>
    </Alert>
    <Alert variant="success">
        <h3>Backup Completed Successfully</h3>
        <p>Your site backup has been created and saved to <code>/storage/backups/site-2032-01-15.tar.gz</code>. The backup includes all content, assets, and configuration files.</p>
    </Alert>
    <Alert variant="error">
        <h4>Failed to Connect to Database</h4>
        <p>Unable to establish a connection to the database server. Please check your database configuration in <code>.env</code> and ensure the database server is running.</p>
    </Alert>
</div>
`;

const richContentTemplate = `
<div class="space-y-3">
    <Alert variant="warning">
        <h1>Please run your migrations</h1>
        <p>The importer uses Laravel's job batching feature to keep track of the import progress, however, it requires a <code>job_batches</code> table in your database. Before you can run the importer, you will need to run <code>php artisan migrate</code>. This alert uses a heading for the title and a paragraph for the message.</p>
    </Alert>
    <Alert variant="default">
        <h2>New Feature Available</h2>
        <p>We've added support for custom field types. You can now create your own field types by extending the <code>Fieldtype</code> class. Check out the documentation for more details.</p>
    </Alert>
    <Alert variant="success">
        <h3>Backup Completed Successfully</h3>
        <p>Your site backup has been created and saved to <code>/storage/backups/site-2032-01-15.tar.gz</code>. The backup includes all content, assets, and configuration files.</p>
    </Alert>
    <Alert variant="error">
        <h4>Failed to Connect to Database</h4>
        <p>Unable to establish a connection to the database server. Please check your database configuration in <code>.env</code> and ensure the database server is running.</p>
    </Alert>
</div>
`;

export const RichContent: Story = {
    argTypes: {
        text: { control: { disable: true } },
        variant: { control: { disable: true } },
    },
    parameters: {
        docs: {
            source: {
                code: richContentCode,
            },
        },
    },
    render: () => ({
        components: { Alert },
        template: richContentTemplate,
    }),
};

export const Warning: Story = {
    args: {
        text: 'This is a warning alert message',
        variant: 'warning',
    },
};

export const Error: Story = {
    args: {
        text: 'This is an error alert message',
        variant: 'error',
    },
};

export const Success: Story = {
    args: {
        text: 'This is a success alert message',
        variant: 'success',
    },
};
