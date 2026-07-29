<?php
/**
 * Static-analysis stubs for optional integrations that are not installed
 * during analysis: Otter Blocks and Elementor. Loaded only by PHPStan
 * (see phpstan.neon); never at runtime.
 *
 * phpcs:ignoreFile
 */

namespace ThemeIsle\GutenbergBlocks\CSS {

	class CSS_Handler {

		/**
		 * @param int $post_id
		 * @return void
		 */
		public static function generate_css_file( $post_id ) {}
	}

	class Block_Frontend {

		/**
		 * @return static
		 */
		public static function instance() {}

		/**
		 * @param int $post_id
		 * @return void
		 */
		public function enqueue_google_fonts( $post_id ) {}
	}
}

namespace ThemeIsle\GutenbergBlocks\Integration {

	class Form_Data_Request {

		/**
		 * @param string $key
		 * @return mixed
		 */
		public function get_data_from_payload( $key ) {}
	}
}

namespace Elementor {

	class Plugin {
	}
}

namespace {

	// nginx-helper exposes its purger as the $nginx_purger global.
	class Purger {

		/**
		 * @return void
		 */
		public function purge_all() {}
	}
}
