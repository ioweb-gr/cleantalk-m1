# CleanTalk Antispam for OpenMage / Magento 1

CleanTalk Antispam module for OpenMage (formerly Magento 1.9).

## Requirements
- OpenMage / Magento 1.x
- PHP compatible with your OpenMage version

## Installation (Composer)

1) Ensure the Magento Composer Installer plugin is available in your project:
```bash
composer require magento-hackathon/magento-composer-installer:^3.0
```

2) Require this module:
```bash
composer require cleantalk/cleantalk-m1:^1.2.12
```

3) Flush cache and re-login to the admin.

## Installation (Composer, no Packagist)

1) Add the VCS repository:
```bash
composer config repositories.cleantalk-m1 vcs https://github.com/ioweb-gr/cleantalk-m1.git
```

2) Require the module (same as above):
```bash
composer require cleantalk/cleantalk-m1:^1.2.12
```

3) Flush cache and re-login to the admin.

## Installation (Manual)

Copy the `app/` directory contents into your Magento root, then clear cache.

## Configuration

Admin Panel → System → Configuration → CleanTalk → Anti-Spam.

### Request Exclusion Regex

Use `Request Exclusion Regex (one per line)` to skip CleanTalk checks for matching `REQUEST_URI` paths.
Patterns can be entered as full PCRE expressions (with delimiters) or plain regex text (delimiters optional).

## Support

Please contact CleanTalk support for questions or issues.
