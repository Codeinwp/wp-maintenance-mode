<?php
/**
 * Tests for the block-theme style flow: the `wpmm_head` / `wpmm_footer` callbacks
 * that print the head markup and then catch up on styles registered late.
 */

/**
 * Test_Block_Theme_Styles.
 */
class Test_Block_Theme_Styles extends WP_UnitTestCase {

	/**
	 * Frontend singleton.
	 *
	 * @var WP_Maintenance_Mode
	 */
	protected static $frontend;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$frontend = WP_Maintenance_Mode::get_instance();
	}

	public function tear_down() {
		remove_action( 'wp_head', array( $this, 'print_head_style' ) );
		remove_action( 'wp_head', array( $this, 'print_late_style' ) );
		remove_action( 'wp_head', array( $this, 'print_style_lookalikes' ) );

		$prop = new ReflectionProperty( 'WP_Maintenance_Mode', 'style_buffer' );
		$prop->setAccessible( true );
		$prop->setValue( self::$frontend, array() );

		parent::tear_down();
	}

	public function print_head_style() {
		echo '<style id="wpmm-head-style">.wpmm-head{color:red}</style>';
	}

	public function print_style_lookalikes() {
		echo '<script>const sample = \'<style>.wpmm-in-script{color:lime}</style>\';</script>';
		echo '<!-- <style>.wpmm-in-comment{color:teal}</style> -->';
	}

	public function print_late_style() {
		echo '<style id="wpmm-late-style">.wpmm-late{color:blue}</style>';
	}

	public function test_the_head_callback_prints_the_head_markup() {
		add_action( 'wp_head', array( $this, 'print_head_style' ) );

		ob_start();
		self::$frontend->remember_style_fse();
		$head = ob_get_clean();

		$this->assertStringContainsString( '.wpmm-head{color:red}', $head );
	}

	public function test_the_footer_callback_prints_only_the_styles_missing_from_the_head() {
		add_action( 'wp_head', array( $this, 'print_head_style' ) );

		ob_start();
		self::$frontend->remember_style_fse();
		ob_end_clean();

		add_action( 'wp_head', array( $this, 'print_late_style' ) );

		ob_start();
		self::$frontend->add_style_fse();
		$footer = ob_get_clean();

		$this->assertStringContainsString( '.wpmm-late{color:blue}', $footer );
		$this->assertStringNotContainsString( '.wpmm-head{color:red}', $footer );
	}

	public function test_the_footer_callback_prints_the_styles_when_the_head_callback_never_ran() {
		add_action( 'wp_head', array( $this, 'print_late_style' ) );

		ob_start();
		self::$frontend->add_style_fse();
		$footer = ob_get_clean();

		$this->assertStringContainsString( '.wpmm-late{color:blue}', $footer );
	}

	public function test_the_footer_callback_ignores_style_markup_inside_scripts_and_comments() {
		add_action( 'wp_head', array( $this, 'print_style_lookalikes' ) );

		ob_start();
		self::$frontend->add_style_fse();
		$footer = ob_get_clean();

		$this->assertStringNotContainsString( '.wpmm-in-script', $footer );
		$this->assertStringNotContainsString( '.wpmm-in-comment', $footer );
	}
}
