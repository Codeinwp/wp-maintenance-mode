#!/bin/sh

# Prepare the wp-env instance for development and tests (wp-env afterStart hook).

# Pretty permalinks: the Playwright global-setup talks to /wp-json/.
npx wp-env run cli wp rewrite structure '/%postname%/'

# Provision the plugin's default settings; while wpmm_settings is missing the
# plugin re-flags the install as fresh on every request.
npx wp-env run cli wp eval 'WP_Maintenance_Mode::single_activate();'

# Skip the setup wizard redirect so the settings screens are directly reachable.
npx wp-env run cli wp option update wpmm_fresh_install 0

# Use the classic (pre-Gutenberg) maintenance screen: the modules settings UI
# (subscribe, countdown, social) only renders in this mode, and the block-based
# flow needs Otter Blocks plus the wizard, which the E2E suite does not cover.
npx wp-env run cli wp option update wpmm_new_look 0
