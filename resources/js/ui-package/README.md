# @statamic/ui

Standalone UI components from Statamic CMS, ready to use in any Vue 3 project.

## Installation

```bash
npm install @statamic/ui
```

## Usage

### Basic Setup

```js
// Import components
import { Button, Card, Modal } from '@statamic/ui'
import '@statamic/ui/style.css'

// Use in your Vue app
export default {
  components: {
    Button,
    Card,
    Modal
  }
}
```

### With Tailwind CSS

If you're using Tailwind CSS v4 in your project, the components will integrate seamlessly. Make sure to include the styles:

```css
@import '@statamic/ui/style.css';
```

### Component Examples

```vue
<template>
  <div>
    <!-- Button variations -->
    <Button variant="primary">Primary Button</Button>
    <Button variant="ghost">Ghost Button</Button>
    
    <!-- Card with content -->
    <Card>
      <Heading>Card Title</Heading>
      <Description>This is a card description.</Description>
    </Card>
    
    <!-- Modal -->
    <Modal v-model:open="showModal">
      <ModalTitle>Modal Title</ModalTitle>
      <p>Modal content goes here.</p>
    </Modal>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { 
  Button, 
  Card, 
  Heading, 
  Description, 
  Modal, 
  ModalTitle 
} from '@statamic/ui'

const showModal = ref(false)
</script>
```

## Available Components

The package exports 100+ UI components including:

- **Forms**: `Button`, `Input`, `Select`, `Checkbox`, `Radio`, `Switch`, `Textarea`
- **Layout**: `Card`, `Panel`, `Modal`, `Popover`, `Dropdown`
- **Data**: `Table`, `Listing`, `Pagination`
- **Navigation**: `Tabs`, `Context` menus
- **Feedback**: `Badge`, `StatusIndicator`, `Skeleton`
- **Media**: `Icon` (with 500+ built-in icons)
- And many more...

## Requirements

- Vue 3.4+
- Tailwind CSS 4.0+ (for proper styling)

## Development

This package is automatically generated from the Statamic CMS codebase. To rebuild:

```bash
npm run build-ui-package
```

## License

MIT License - see the main Statamic CMS repository for details.