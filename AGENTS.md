# Agent Workflow

## Project Overview

WP Maintenance Mode (LightStart) is a WordPress plugin by Themeisle that displays a maintenance mode, coming soon, or landing page to visitors. Text domain: `wp-maintenance-mode`. Option key: `wpmm_settings`. All constants prefixed with `WPMM_`.

## Commands

```bash
# Install dependencies
composer install
npm install

# Lint (PHP_CodeSniffer with WordPress coding standards)
composer run lint

# Auto-fix coding standards
composer run format

# Run PHPUnit tests
./vendor/bin/phpunit

# Run a single test file
./vendor/bin/phpunit tests/generic-test.php

# Build assets (minify JS + CSS via Grunt)
npm run build        # or: npx grunt

# Watch for asset changes during development
npx grunt watch
```

## Architecture

### Entry Point & Bootstrap

`wp-maintenance-mode.php` — Main plugin file. Defines all `WPMM_*` constants (paths, URLs), requires function files, then loads the two core singleton classes via `plugins_loaded`.

### Core Classes (`includes/classes/`)

- **`WP_Maintenance_Mode`** — Frontend singleton. Handles maintenance mode display logic, page templates, redirect rules, multisite/network mode support, and shortcode registration. Loaded on every request.
- **`WP_Maintenance_Mode_Admin`** — Admin singleton. Settings page, AJAX handlers (prefixed `wp_ajax_wpmm_*`), wizard flow, template management, subscriber export, and notices. Only loaded in `is_admin()` context.
- **`WP_Maintenance_Mode_Shortcodes`** + `shortcodes/` — Shortcode registration (e.g., login form shortcode).

### Functions (`includes/functions/`)

- **`hooks.php`** — Plugin header filters and email content type hook.
- **`helpers.php`** — Utility functions (e.g., `wpmm_get_option()`).

### Views (`views/`)

PHP template files for admin settings tabs (`settings.php`, `contact.php`, `google-analytics.php`), the frontend maintenance page (`maintenance.php`), wizard (`wizard.php`), network settings, notices, and sidebar.

### Assets

- `assets/js/` — Frontend (`scripts.js`) and admin (`scripts-admin.js`, `scripts-admin-global.js`) JavaScript, plus chatbot (`bot.js`, `bot.async.js`). Grunt minifies to `.min.js`.
- `assets/css/` — Stylesheets minified via PostCSS/cssnano to `.min.css`.
- `assets/templates/` — Block template JSON files organized by category: `coming-soon/`, `maintenance/`, `landing-page/`.

### Settings Structure

Plugin settings stored as serialized array in `wpmm_settings` option, with sections: `general` (status, network_mode), plus additional tabs managed by the admin class. Network/multisite settings stored in `wpmm_settings_network`.

## Coding Standards

- WordPress-Core standards enforced via PHPCS (`phpcs.xml`), with specific exclusions (no Yoda conditions required, loose comparison allowed, etc.)
- i18n text domain: `wp-maintenance-mode`
- PHP minimum: 7.0 (runtime set in PHPCS)
- Assets use `.min.` suffix in production; controlled by `SCRIPT_DEBUG` constant
