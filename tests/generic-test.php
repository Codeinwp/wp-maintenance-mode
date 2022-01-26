<?php
/**
 * Generic test.
 */

/**
 * Test_Generic.
 */
class Test_Generic extends WP_UnitTestCase {
	public function test_constants() {
		$this->assertTrue( defined('WPMM_PATH') );
		$this->assertTrue( defined('WPMM_CLASSES_PATH') );
		$this->assertTrue( defined('WPMM_FUNCTIONS_PATH') );
		$this->assertTrue( defined('WPMM_LANGUAGES_PATH') );
		$this->assertTrue( defined('WPMM_VIEWS_PATH') );
		$this->assertTrue( defined('WPMM_CSS_PATH') );
		$this->assertTrue( defined('WPMM_URL') );
		$this->assertTrue( defined('WPMM_JS_URL') );
		$this->assertTrue( defined('WPMM_CSS_URL') );
		$this->assertTrue( defined('WPMM_IMAGES_URL') );
		$this->assertTrue( defined('WPMM_ASSETS_SUFFIX') && WPMM_ASSETS_SUFFIX === '.min' );
	}

	public function test_wpmm_maybe_define_constant() {

		wpmm_maybe_define_constant( 'SOMECONSTANT', 'exists' );
		wpmm_maybe_define_constant( 'SOMECONSTANT', 'again' );
		$this->assertTrue( defined('SOMECONSTANT') && SOMECONSTANT === 'exists' );
	}

	public function test_class_loading() {
		$this->assertTrue( class_exists('WP_Maintenance_Mode') );
	}

	public function test_wpmm_get_banners() {
		$banners = wpmm_get_banners();
		$this->assertNonEmptyMultidimensionalArray( $banners );
	}
}
