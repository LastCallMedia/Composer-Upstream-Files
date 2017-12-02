Composer Upstream Files Plugin
==============================

This [Composer](https://getcomposer.org/) plugin allows you to update files belonging to your application from various upstream sources.  There are a couple of different use cases for this:

* You have files that are required for specific versions of your application dependencies, but which need to live as part of your application.  For example, Drupal's `index.php` file is required, but must live outside of the `core` directory when you use a Composer based installation.
* You have files you want to allow modifications to (so they're not appropriate for inclusion in a library), but that you want to keep in sync between different projects.  For example, a default `.eslintrc` or `phpcs.xml.dist`.

In all cases, the files that would be managed by this plugin should be committed to the application's repository.  _This plugin does not automatically update these files_ - it only provides a Composer command you can use to update them -- you should do this periodically, then review and commit the resulting changes.

Installation
------------
Install this plugin in your application with Composer:
```bash
composer require --dev lastcall/composer-upstream-files
```

Usage
-----
Upstream files are defined in the `extra` section of your `composer.json`.  Example:
```json
{
  "name": "my-awesome-site",
  "require": {
    "my/package": "^1.0.0"
  },
  "extra": {
    "upstream-files": {
      "files": {
        "https://raw.githubusercontent.com/LastCallMedia/Drupal-Scaffold/circle20/.editorconfig": ".editorconfig",
        "https://raw.githubusercontent.com/LastCallMedia/Drupal-Scaffold/circle20/web/.htaccess": "web/.htaccess"
      }
    }
  }
}
```
In this example, we've defined two files with upstream sources.  The key of the `files` array is the source, and the value is the destination.  When we run `composer upstream-files:update`, both files will be refreshed from their respective URLs.  We would then review and commit the changes.

This plugin also supports using tokens to reduce the amount of stuff you have to type and update.  Tokens are enclosed in double brackets.  Example:
```json
{
  "extra": {
    "upstream-files": {
      "tokens": {
        "scaffold": "https://raw.githubusercontent.com/LastCallMedia/Drupal-Scaffold",
        "drupal": "https://raw.githubusercontent.com/drupal/drupal/{{drupal/core.version}}"
      },
      "files": {
        "{{drupal}}/index.php": "web/index.php",
        "{{scaffold}}/.editorconfig": ".editorconfig"
      }
    }
  }
}
```
You can define any tokens you want to use under the `tokens` key, and use them as replacements for your `files`.  Additionally, you can reference the current version of any installed package by using `{{PACKAGENAME.version}}` (as we did with drupal/core above).  Tokens are replaced recursively, so if your token contains a token, that's fine.
