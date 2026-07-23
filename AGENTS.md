# Agent Workflow

## Project Overview

WP Maintenance Mode (LightStart) is a WordPress plugin by Themeisle that displays a maintenance mode, coming soon, or landing page to visitors. Text domain: `wp-maintenance-mode`. Option key: `wpmm_settings`. All constants prefixed with `WPMM_`.

## Commands

```bash
# Install dependencies (yarn.lock is the tracked lockfile; package-lock.json is
# ignored, and --ignore-engines is needed because the legacy eslint toolchain
# caps its node engines range below current node versions)
composer install
yarn install --frozen-lockfile --ignore-engines

# Environment (wp-env, requires Docker)
npm run env:start    # reuse a running instance, or pick a free port + start
npm run env:stop
npm run env:cleanup  # remove THIS checkout's containers/volumes (run before deleting a worktree)

# Lint / format (PHP_CodeSniffer with WordPress coding standards)
composer run lint    # on the host
npm run lint:php     # same, inside the wp-env container
npm run format:php

# PHP unit tests (run inside wp-env; no local MySQL/SVN setup needed)
npm run test:unit:php         # start env + run the suite
npm run test:unit:php:base    # run the suite against an already-running env

# E2E tests (Playwright; needs `npx playwright install chromium` once)
npm run test:e2e
npm run test:e2e:ui           # interactive UI mode

# Build assets (minify JS + CSS via Grunt)
npm run build        # or: npx grunt

# Watch for asset changes during development
npx grunt watch
```

Configs: PHPUnit `phpunit.xml` (+ `tests/bootstrap.php`), Playwright `tests/e2e/playwright.config.js`, wp-env `.wp-env.json` + gitignored `.wp-env.override.json`.

## wp-env Instance

Single environment (`"testsEnvironment": false`): one `wordpress`/`cli`/`mysql` container set serves dev, PHPUnit, and E2E. Login: `admin`/`password`. Port precedence everywhere: `WP_BASE_URL` > `WP_ENV_PORT` > `.wp-env.override.json` > `8888` — `npm run env:start` (`bin/wp-env-up.js`) probes for a free port and pins it in the override file, which the Playwright config reads too.

- PHPUnit runs in the container with `WORDPRESS_TABLE_PREFIX=wptests_` (baked into `test:unit:php:base`). Never drop that prefix override: the container's tests config otherwise points at the dev site's own `wp_` tables and the suite install WIPES the site.
- `bin/wp-env-setup.sh` (wp-env `afterStart`) sets pretty permalinks, provisions the plugin's default settings, and disables the setup-wizard redirect plus the block-based "new look" (`wpmm_new_look=0`) so the classic settings screens are testable. While `wpmm_settings` is missing, the plugin re-flags the install as fresh on every request — provision settings before touching `wpmm_fresh_install`.
- The environment runs with `SCRIPT_DEBUG=true`, so unminified assets load and no Grunt build is needed for tests; `WPMM_ASSETS_SUFFIX` is empty in this mode.

## E2E Rules (Important)

- `workers: 1`, always. Maintenance mode is a site-wide option; virtually every spec flips global state. Do not raise the worker count or add `fullyParallel`.
- Specs must leave maintenance mode OFF when they finish, or every later spec sees the 503 page.
- Use the helpers in `tests/e2e/utils.js` (`setMaintenanceMode`, `activateSettingsTab`, `saveSettingsForm`, `openAsVisitor`) instead of hand-rolling admin flows. They encode three non-obvious constraints:
  - After any settings save (form POST + redirect), headless Chromium stops producing animation frames for the page, so non-`force` Playwright interactions hang on the "element is stable" actionability check. Helpers use `force: true` plus explicit state assertions.
  - The settings tabs are hash-driven; navigating to the hash-less settings URL is a same-document navigation, so the previously active tab persists. Always `activateSettingsTab` explicitly.
  - The save redirect lands on `?updated=1#<tab>` and WordPress strips the query right after load — wait for the redirect response, not the URL.
- Visitor contexts: `browser.newContext()` inherits the admin `storageState`; `openAsVisitor` clears cookies to get a real logged-out visitor.

## Testing Practices (TDD)

- Red → green: write the failing test first, then only enough code to make it pass. One seam, one test, one minimal implementation per cycle — don't batch-write tests for imagined behavior (vertical slices, not horizontal). Refactoring is a separate review-stage step, not part of the loop.
- Test behavior through public seams, never internals: the admin-ajax endpoints (`wp_ajax_wpmm_*`), public helpers (`includes/functions/helpers.php`), rendered frontend output, and the settings screens. If a test breaks when you refactor but behavior hasn't changed, it's testing the wrong thing.
- Expected values must come from an independent source of truth (a known-good literal, the spec, a worked example) — never recompute them the way the code does.
- Plugin-specific seams and traps (see `tests/ajax-api-test.php`):
  - Several AJAX handlers guard with plain `die( $msg )` (not `wp_die`); those guard paths CANNOT be exercised through `_handleAjax` — a plain `die()` kills the PHPUnit process with exit code 0.
  - Handlers wrapped in `catch ( Exception )` swallow the suite's die-exception and answer twice; the test class installs a die handler that throws `WPMM_Ajax_Die_Signal extends Error` instead.
  - `_handleAjax()` fires `admin_init` without defining `DOING_AJAX`; keep `maybe_redirect` unhooked in tests or a fresh-install flag turns it into a process-killing wizard redirect.
  - The WP suite auto-excludes tests annotated `@group ajax` — don't add that annotation.
  - wp-env's sender address `wordpress@localhost` fails PHPMailer validation; filter `wp_mail_from` in mail tests.

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
