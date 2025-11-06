# Storybook Documentation

This directory contains Storybook configuration and stories for the Statamic UI component library.

## Getting Started

### Development

Start the Storybook development server:

```bash
npm run storybook
```

This will start Storybook at http://localhost:6006

### Building

Build a static version of Storybook:

```bash
npm run build-storybook
```

The static files will be output to `storybook-static/`.

## Structure

```
packages/ui/
├── .storybook/          # Storybook configuration
│   ├── main.ts         # Main config (plugins, addons)
│   ├── preview.ts      # Preview config (decorators, parameters)
│   └── vitest.setup.ts # Vitest integration setup
└── src/
    └── stories/        # Component stories
        ├── Introduction.mdx
        ├── Button.stories.ts
        ├── Input.stories.ts
        └── ...
```

## Stories

Stories are organized by component and follow this naming convention:
- `ComponentName.stories.ts` - Stories for a component

Each story file includes:
- **Meta configuration** - Component metadata, args, arg types
- **Default story** - Basic usage example
- **Variant stories** - Different states and variations
- **Interactive stories** - Complex scenarios with user interaction

## Writing Stories

Example story structure:

```typescript
import type { Meta, StoryObj } from '@storybook/vue3';
import { ref } from 'vue';
import MyComponent from '../MyComponent.vue';

const meta = {
  title: 'Components/MyComponent',
  component: MyComponent,
  tags: ['autodocs'],
  argTypes: {
    variant: {
      control: 'select',
      options: ['default', 'primary'],
    },
  },
} satisfies Meta<typeof MyComponent>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  render: (args) => ({
    components: { MyComponent },
    setup() {
      const value = ref('');
      return { args, value };
    },
    template: '<MyComponent v-bind="args" v-model="value" />',
  }),
};
```

## Addons

The following Storybook addons are configured:

- **@chromatic-com/storybook** - Visual testing
- **@storybook/addon-docs** - Auto-generated documentation
- **@storybook/addon-a11y** - Accessibility testing
- **@storybook/addon-vitest** - Vitest integration for component testing

## Features

### Auto-generated Documentation

Stories with the `autodocs` tag automatically generate documentation from:
- Component props and their types
- ArgTypes configuration
- JSDoc comments

### Accessibility Testing

All stories are automatically checked for accessibility issues. View the A11y panel to see:
- WCAG violations
- Best practice recommendations
- Color contrast issues

### Visual Testing

Use Chromatic for visual regression testing:
1. Stories are automatically captured as baselines
2. Changes in UI are flagged for review
3. Approve or reject visual changes

### Component Testing

With Vitest integration, you can run tests against your stories:

```bash
npm test
```

## Configuration

### Vite Config

The `.storybook/main.ts` file includes Vite configuration:
- Vue plugin for .vue file support
- Tailwind CSS v4 integration
- Path aliases for imports

### Preview Config

The `.storybook/preview.ts` file includes:
- Global styles (ui.css import)
- Background color options
- Accessibility testing configuration

## Tips

1. **Use Controls** - Make stories interactive with argTypes controls
2. **Show States** - Create stories for different states (loading, error, etc.)
3. **Test Accessibility** - Check the A11y panel for each component
4. **Document Edge Cases** - Create stories for unusual prop combinations
5. **Keep Stories Simple** - Each story should demonstrate one thing clearly

## Troubleshooting

### Styles not loading
Make sure `ui.css` is imported in `.storybook/preview.ts`

### Components not rendering
Check that Vue plugin is configured in `.storybook/main.ts`

### Dark mode not working
Verify Tailwind CSS is properly configured with the `@tailwindcss/vite` plugin

### Icons not displaying
Ensure icon registry is properly initialized in your components

## Resources

- [Storybook Documentation](https://storybook.js.org/docs)
- [Storybook for Vue](https://storybook.js.org/docs/vue/get-started/introduction)
- [Writing Stories](https://storybook.js.org/docs/vue/writing-stories/introduction)
- [Accessibility Addon](https://storybook.js.org/addons/@storybook/addon-a11y)

