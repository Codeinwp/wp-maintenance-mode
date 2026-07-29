/**
 * WordPress dependencies
 */
import { RequestUtils } from '@wordpress/e2e-test-utils-playwright';

async function globalSetup( config ) {
	const { storageState, baseURL } = config.projects[ 0 ].use;
	const storageStatePath =
		typeof storageState === 'string' ? storageState : undefined;

	const requestUtils = await RequestUtils.setup( {
		baseURL,
		storageStatePath,
	} );

	// Authenticate and persist the admin storage state so specs start logged in.
	await requestUtils.setupRest();
}

export default globalSetup;
