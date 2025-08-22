# Struktal-Translator

This is a PHP library for translating texts in Struktal applications.

## Installation

To install this library, include it in your project using Composer:

```bash
composer require struktal/struktal-translator
```

## Usage

### Translations directory

In your project, you have to create a specific directory for translations.
This directory then contains more subdirectories—one for each language or locale.
Then, the subdirectories contain the translation files—one for each message domain:

```
📁 .../translations
├── 📁 en_US
│   ├── 📄 messages.json
│   └── 📄 emails.json
├── 📁 en_GB
│   ├── 📄 messages.json
│   └── 📄 emails.json
├── 📁 de_DE
│   ├── 📄 messages.json
│   └── 📄 emails.json
└── 📁 de_AT
    ├── 📄 messages.json
    └── 📄 emails.json
```

A translation file is a JSON file that contains key-value pairs for the translations.
For example, the `messages.json` file for the `en_US` locale might look like this:

```json
{
    "welcome_message": "Welcome to our application!",
    "goodbye_message": "Thank you for using our application!"
}
```

You can use placeholders in the translations, which will be replaced at runtime:

```json
{
    "welcome_message": "Welcome to our application, $$username$$!",
    "goodbye_message": "Thank you for using our application, $$username$$!"
}
```

### Setup

Before you can use this library, you need to customize a few parameters.
You can do this in the startup of your application:

```php
\struktal\Translator\Translator::setTranslationsDirectory("path/to/your/translations/root");
\struktal\Translator\Translator::setDomain("messages"); // Defines the file to read from the translations directory
\struktal\Translator\Translator::setLocale(
    \struktal\Translator\LanguageUtil::getPreferredLocale()
);
```

Then, you can use the library's features in your code.

### Translate texts

To translate texts, you can use the `Translator::translate()` method:

```php
\struktal\Translator\Translator::translate("welcome_message", [
    "username" => "John Doe"
]);
```

## License

This software is licensed under the MIT license.
See the [LICENSE](LICENSE) file for more information.
