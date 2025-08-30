# Statamic CMS - Claude AI Context

## Project Overview
This is the **Statamic CMS Core Package** - a Laravel-powered, flat-file (by default) CMS designed for building modern, easy-to-manage websites. This repository contains the core Composer package that gets installed into Laravel applications.

## Project Structure
```
├── src/                  # Core PHP source code (PSR-4: Statamic\)
│   ├── CP/               # Control Panel specific functionality  
│   └── ...               # Many other core modules
├── resources/
│   ├── js/               # Vue.js Control Panel frontend
│   │   ├── bootstrap/
│   │   │   ├── cms/      # Bootstraps the code necessary for the npm package
│   │   │   └── ...
│   │   ├── package/      # The @statamic/cms local npm package to be used by addons
│   │   ├── tests/        # JS test suite (Vitest)
│   │   └── ...
│   ├── css/              # Stylesheets
│   ├── views/            # Blade templates
│   └── lang/             # Translations
├── tests/                # PHPUnit test suite
├── config/               # Configuration files
└── routes/               # Route definitions
```

## Technology Stack
- **Backend**: Laravel 11+ / PHP 8+
- **Control Panel Frontend**: Vue 3 + TypeScript + Vite
- **Control Panel CSS**: Tailwind CSS 4
- **Database**: File-based "Stache" out of the box, but eloquent supported through the statamic/eloquent-driver composer package

## Development Commands

### PHP/Backend
```bash
# Run PHPUnit tests
./vendor/bin/phpunit
# OR use the batch file on Windows
phpunit.bat

# Code style/linting (Laravel Pint)
./vendor/bin/pint

# Run specific test
./vendor/bin/phpunit tests/path/to/TestFile.php
```

## Common Development Tasks
- **Adding new fieldtypes**: Extend `src/Fieldtypes/`
- **Control Panel features**: Work in `src/CP/` + `resources/js/`
- **API endpoints**: Check `src/API/` + `routes/api.php`
- **Entry/content handling**: See `src/Entries/`
- **Asset management**: See `src/Assets/`
- **Authentication**: See `src/Auth/`

## Testing
- Full PHPUnit test suite in `tests/`
- Frontend tests with Vitest
- Run tests before submitting PRs

## Important Notes
- This is the **core package only** - it requires a Laravel app to run
- The starter Laravel app is at [statamic/statamic](https://github.com/statamic/statamic)  
- Documentation site code is at [statamic.dev](https://statamic.dev)
- Do not commit compiled assets (`resources/dist/*`) - they're auto-generated
- Follow the contribution guidelines in [CONTRIBUTING.md](CONTRIBUTING.md)

## Build Process
The project uses Vite for asset compilation with separate configs:
- `vite.config.js` - Main Control Panel assets. e.g. The Vue application.
- `vite-frontend.config.js` - Frontend-specific assets. e.g. Things like conditional field logic to be used on front-end forms. 
- Control Panel assets are built to `resources/dist/` and `resources/dist-dev/`. The dev version enables Vue devtools.

## NPM Package Notes
- Our Vite bundle contains the Vue app, UI components, all the JS to make the Control Panel work.
- We provide a `@statamic/cms` node module for addons to use that provides core helpers, ui components, etc.
- Our Vite bundle assembles everything the node module needs in `resouces/js/bootstrap/cms` and makes it available in the `window.__STATAMIC__` object.
- The node module is defined in `resources/js/package` and resolves everything through the `window` object.
- Code needs to be in the `window` object to prevent addon bundles from re-including our code, and from needing to recompile our source files.

## Links
- [Main Documentation](https://statamic.dev/)
- [Application Repo](https://github.com/statamic/statamic) 
- [Discussions](https://github.com/statamic/cms/discussions)
- [Discord](https://statamic.com/discord)
