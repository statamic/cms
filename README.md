# Statamic 3 Alpha

Build better, easier to manage websites. Enjoy radical efficiency.

> **Note:** This repository contains the core code for the CMS. To build a website using Statamic, visit the main [Statamic repository](https://github.com/statamic/three-statamic).

## Learning Statamic

Learn all the ins and outs of Statamic over at our extensive [documentation][docs].

[docs]: https://docs.statamic.dev/

## Support

### Questions
Official support from the Statamic team can be found [through our website](https://statamic.com/support). Our helpful community is available at our [forum](https://statamic.com/forum) or on our [Discord server](https://statamic.com/discord).

### Bug Reports
Bug reports can be created [right here](https://github.com/statamic/three-cms/issues). Please include as much detail as possible. Code examples, exception messages, stack traces, and applicable content files are encouraged.

### Feature Requests
Feature requests should be created in the [statamic/ideas][ideas] repository.

## Contributing

Thank you for considering contributing to Statamic!

Before you spend time creating a pull request, please open an issue at the [statamic/ideas][ideas] repository so we can discuss the details.

### Testing

```
composer install
./vendor/bin/phpunit
```

### Translations

The source can be scanned for `__()` method usage and translation files will be generated.

```
php translator generate
```

By default, only existing languages will be generated. If you would like to contribute a new language, specify it as an argument:

```
php translator generate fr
```

Generating a translation file will populate it with empty strings. If you don't want to manually enter all the strings, you can have Google Translate do it. You need an API key.

```
php translator translate fr --key=123
```

You may review and update existing translation files. This provides a nice way to correct any mistakes. It will loop over each line in the requested file, show you the key, the English phrase, and the current translated phrase. You can enter a new translation if you like.

```
php translator review fr messages
```

[ideas]: https://github.com/statamic/ideas
