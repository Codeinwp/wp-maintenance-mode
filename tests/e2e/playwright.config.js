/**
 * External dependencies
 */
import fs from 'fs';
import path from 'path';
import { defineConfig, devices } from '@playwright/test';

const STORAGE_STATE_PATH =
	process.env.STORAGE_STATE_PATH ||
	path.join( process.cwd(), 'artifacts/storage-states/admin.json' );

// Port precedence: WP_BASE_URL > WP_ENV_PORT > .wp-env.override.json > 8888.
// The override file pins a per-checkout port (written by `npm run env:start`)
// and is read by wp-env itself, so the suite follows it with no env var.
const getOverridePort = () => {
	try {
		const override = JSON.parse(
			fs.readFileSync(
				path.join( process.cwd(), '.wp-env.override.json' ),
				'utf8'
			)
		);
		return parseInt( override.port, 10 ) || undefined;
	} catch ( e ) {
		return undefined;
	}
};

const WP_ENV_PORT = parseInt( process.env.WP_ENV_PORT || '', 10 ) || getOverridePort() || 8888;
const WP_BASE_URL = process.env.WP_BASE_URL || `http://localhost:${ WP_ENV_PORT }`;

// @wordpress/e2e-test-utils-playwright reads WP_BASE_URL directly (its
// RequestUtils falls back to localhost:8889), so export the resolved URL.
process.env.WP_BASE_URL = WP_BASE_URL;

export default defineConfig( {
	testDir: 'specs',
	outputDir: path.join( process.cwd(), 'artifacts/test-results' ),
	globalSetup: './global-setup.js',
	// Maintenance mode is a site-wide option: every spec flips global state,
	// so specs must never run in parallel. Do not raise the worker count.
	workers: 1,
	fullyParallel: false,
	forbidOnly: !! process.env.CI,
	retries: process.env.CI ? 2 : 0,
	reporter: process.env.CI ? [ [ 'github' ], [ 'list' ] ] : 'list',
	use: {
		baseURL: WP_BASE_URL,
		storageState: STORAGE_STATE_PATH,
		trace: 'retain-on-failure',
		video: 'off',
	},
	projects: [
		{
			name: 'chromium',
			use: { ...devices[ 'Desktop Chrome' ] },
		},
	],
} );
