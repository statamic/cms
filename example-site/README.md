# Statamic UI Test App

A minimal Vue 3 app for testing @statamic/ui components.

## Setup

```bash
npm install
npm run dev
```

## Usage

1. Open `src/App.vue`
2. Import any component you want to test:
   ```js
   import { Button, Card, Modal } from '@statamic/ui'
   ```
3. Add it to the template:
   ```vue
   <Button variant="primary">Test Button</Button>
   ```
4. Save and see it in the browser

## Available Components

The @statamic/ui package includes 100+ components:
- `Button`, `Input`, `Select`, `Checkbox`, `Radio`
- `Card`, `Modal`, `Panel`, `Popover`
- `Table`, `Pagination`, `Badge`
- `Icon` (500+ icons)
- And many more...

## Development

- Hot reload enabled
- Tailwind CSS included
- Browser will open automatically at http://localhost:3000