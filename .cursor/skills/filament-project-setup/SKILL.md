---
name: filament-project-setup
description: "Use this skill when starting or bootstrapping a Laravel Filament project, configuring a Filament panel, setting initial Filament conventions, or adding Portuguese/English localization for a Filament app. Covers Filament setup, Laravel localization, pt_BR as the default locale, English fallback, Laravel Lang publisher JSON files, and optional Filament language switching."
---

# Filament Project Setup

Use this skill only for initial Filament project setup or when the user asks to prepare a repeatable bootstrap for a Filament app. Do not run setup commands until the user explicitly approves.

## First Checks

- Confirm the installed Laravel, Filament, Livewire, and Tailwind versions before choosing commands or examples.
- Consult version-specific documentation before changing Filament code, preferably through Laravel Boost `search-docs` when available.
- Follow existing project conventions, especially existing `app/Filament`, `app/Providers/Filament`, `config`, `lang`, and `.env.example` patterns.
- Do not edit `vendor/` files. Publish package config/views/lang files only when the project needs to override them.

## Recommended Localization Packages

- Use `laravel-lang/lang` as the default package for Laravel core translations. It already requires `laravel-lang/publisher`, so do not install `laravel-lang/publisher` separately.
- `laravel-lang/publisher` provides the `lang:*` Artisan commands and configuration publishing. The `laravel-lang/lang` package supplies the actual Laravel translation strings used by `lang:update`.
- Use `bezhansalleh/filament-language-switch` only when the Filament panel needs a visible language switcher in the UI. This plugin does not replace Laravel language files; it only lets users switch supported locales in Filament.

## Localization Bootstrap

When the user approves running the localization setup, propose these steps:

```bash
composer require --dev laravel-lang/lang
php artisan vendor:publish --tag="localization"
```

Set these environment values in `.env` and mirror safe defaults in `.env.example` when the project tracks one:

```dotenv
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=pt_BR
LOCALIZATION_INLINE=true
```

Then add Portuguese and English locales:

```bash
php artisan lang:add pt_BR en
php artisan lang:update
```

Expected language structure after the setup:

```text
lang/
  en.json
  pt_BR.json
  en/
  pt_BR/
```

If the JSON files are not created by the installed publisher version, create `lang/en.json` and `lang/pt_BR.json` with `{}` and keep using Laravel JSON translation strings with `__('Text')`.

## Filament Language Switcher

Only add this when the user wants users to switch language inside the Filament panel:

```bash
composer require bezhansalleh/filament-language-switch
```

Register the supported locales in an application service provider, usually `AppServiceProvider::boot()`:

```php
use BezhanSalleh\LanguageSwitch\LanguageSwitch;

public function boot(): void
{
    LanguageSwitch::configureUsing(function (LanguageSwitch $switch): void {
        $switch
            ->locales(['pt_BR', 'en'])
            ->labels([
                'pt_BR' => 'Portugues',
                'en' => 'English',
            ]);
    });
}
```

Use ASCII labels unless the file already uses Unicode. If the project prefers accents in UI text, `Português` is acceptable.

## Filament Setup Conventions

- Prefer Filament v5 APIs and verify examples against v5 documentation before using them.
- Keep PanelProvider configuration focused: panel id/path, auth middleware, theme, plugins, discovery paths, and navigation behavior.
- Prefer Filament Resources, Pages, Forms, Tables, Actions, Widgets, and Relation Managers over custom controllers for admin workflows.
- Use translation strings for user-facing labels, navigation labels, form labels, table headings, actions, notifications, validation text, and empty states.
- For hierarchical Filament resource translations, use `lang/{locale}/filamentphp-resources.php` with `resources.{resource}.{navigation,labels,form.fields,table.columns,infolist.entries,filters,actions,import}`.
- Do not store resource translation maps in root `{locale}.json`; Laravel JSON translation files are flat and are better for inline strings.
- Keep labels consistent across Portuguese and English translation files.
- Do not publish Filament views or translations unless the project needs to override package defaults.

## NativePHP Desktop Note

For NativePHP desktop projects, remember that runtime database behavior may differ from normal Laravel. If an Artisan command must target the desktop database, use the project's existing NativePHP guidance, such as prefixing with `NATIVEPHP_RUNNING=true` when required.

## Verification

After setup changes are approved and applied:

- Run the smallest relevant test or smoke check for the Filament panel.
- Check that `config('app.locale')` is `pt_BR` and `config('app.fallback_locale')` is `en`.
- Check that `lang/pt_BR.json` and `lang/en.json` exist.
- If using the language switcher, open the Filament panel and verify both `pt_BR` and `en` appear.
