import type {Meta, StoryObj} from '@storybook/vue3';
import {Alert, Heading, Description} from '@ui';
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
<Alert text="This is a default alert message" />
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
        template: `<div class="flex flex-col gap-2">${defaultCode}</div>`,
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



const headingsCode = `
<Alert heading="New Feature Available" text="We've added support for..." />
`;

export const Headings: Story = {
    argTypes: {
        icon: { control: { disable: true } },
        text: { control: { disable: true } },
        variant: { control: { disable: true } },
    },
    parameters: {
        docs: {
            source: {
                code: headingsCode,
            },
        },
    },
    render: () => ({
        components: { Alert },
        template: `
            <Alert heading="New Feature Available" text="We've added support for custom field types. You can now create your own field types by extending the <code>Fieldtype</code> class. Check out the documentation for more details." />
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
    <Alert variant="default">
        <Heading>Base Size (default)</Heading>
        <Description>This heading uses the default base size with Heading and Description components.</Description>
    </Alert>
    <Alert variant="warning">
        <Heading size="lg">Large Size</Heading>
        <Description>Here's an example of a larger heading with Heading and Description components. The Alert component automatically adjusts spacing for larger headings.</Description>
    </Alert>
    <Alert variant="success">
        <Heading size="xl">Extra Large Size</Heading>
        <Description>Here's an example of an extra large heading with Heading and Description components.</Description>
    </Alert>
    <Alert variant="success">
        <Heading size="2xl">2XL Size</Heading>
        <Description>Here's an example of the largest heading size with Heading and Description components. Note the adjusted spacing for larger headings.</Description>
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
    <Alert variant="default">
        <Heading>Base Size (default)</Heading>
        <Description>This heading uses the default base size with Heading and Description components.</Description>
    </Alert>
    <Alert variant="warning">
        <Heading size="lg">Large Size</Heading>
        <Description>Here's an example of a larger heading with Heading and Description components. The Alert component automatically adjusts spacing for larger headings.</Description>
    </Alert>
    <Alert variant="success">
        <Heading size="xl">Extra Large Size</Heading>
        <Description>Here's an example of an extra large heading with Heading and Description components.</Description>
    </Alert>
    <Alert variant="success">
        <Heading size="2xl">2XL Size</Heading>
        <Description>Here's an example of the largest heading size with Heading and Description components. Note the adjusted spacing for larger headings.</Description>
    </Alert>
</div>
`;

export const RichContent: Story = {
    argTypes: {
        text: { control: { disable: true } },
        variant: { control: { disable: true } },
        icon: { control: { disable: true } },
    },
    parameters: {
        docs: {
            description: {
                story: 'The Alert component supports both native HTML elements (h1-h6, p) and the Heading/Description components. Heading components support different sizes: `base`, `lg`, `xl`, and `2xl`. The Alert component automatically adjusts spacing for larger headings and both approaches receive consistent styling and color inheritance.',
            },
            source: {
                code: richContentCode,
            },
        },
    },
    render: () => ({
        components: { Alert, Heading, Description },
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

const headingAndDescriptionCode = `
<div class="space-y-3">
    <Alert variant="default">
        <Heading>Using Heading Component</Heading>
        <Description>This alert uses the Heading and Description components instead of native HTML elements.</Description>
    </Alert>
    <Alert variant="warning">
        <Heading size="lg">Warning: Action Required</Heading>
        <Description>This is a warning alert with a larger heading with Heading and Description components. The Heading component supports different sizes and inherits the alert's color scheme.</Description>
    </Alert>
    <Alert variant="success">
        <Heading size="xl">Backup Completed Successfully</Heading>
        <Description>This is an extra large heading with Heading and Description components. Your site backup has been created and saved to <code>/storage/backups/site-2032-01-15.tar.gz</code>. The backup includes all content, assets, and configuration files.</Description>
    </Alert>
    <Alert variant="error">
        <Heading level="2">Database Connection Failed</Heading>
        <Description>This is a heading level <code>2</code> example, with no heading size difference. Unable to establish a connection to the database server. Please check your database configuration in <code>.env</code> and ensure the database server is running.</Description>
    </Alert>
    <Alert variant="warning">
        <Heading icon="warning-diamond">Migration Required</Heading>
        <Description>This is a heading with an icon with Heading and Description components. The importer uses Laravel's job batching feature to keep track of the import progress, however, it requires a <code>job_batches</code> table in your database. Before you can run the importer, you will need to run <code>php artisan migrate</code>.</Description>
    </Alert>
</div>
`;

export const WithHeadingAndDescription: Story = {
    tags: ['!dev'],
    argTypes: {
        text: { control: { disable: true } },
        variant: { control: { disable: true } },
        icon: { control: { disable: true } },
    },
    parameters: {
        docs: {
            description: {
                story: 'The Alert component fully supports the Heading and Description components. When used together, they automatically inherit the Alert\'s variant colors and receive proper spacing and typography styles. This provides a consistent API whether you use native HTML elements (h1-h6, p) or the Heading/Description components.',
            },
            source: {
                code: headingAndDescriptionCode,
            },
        },
    },
    render: () => ({
        components: { Alert, Heading, Description },
        template: headingAndDescriptionCode,
    }),
};

