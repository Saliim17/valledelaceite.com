<?php
namespace AIOSEO\Plugin\Common\Admin;

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class LocalBusinessSeo {

	private $blocks = [];

	/**
	 * Initialize the admin.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		if ( is_admin() ) {
			// Load Gutenberg components
			// add_action( 'admin_enqueue_scripts', [ $this, 'enqueueLocalBusinessSeoScripts' ] );

			// Add metabox
			// add_action( 'add_meta_boxes', [ $this, 'addLocalBusinessSeoSettingsMetabox' ] );

			// Add Gutenberg Blocks
			// $this->blocks[] = 'address';
			// $this->blocks[] = 'opening';
			// add_filter( 'block_categories', [ $this, 'registerBlockCategory' ], 10, 3 );
			// add_action( 'init', [ $this, 'registerBlocks' ] );
		}
	}

	/**
	 * Enqueues the JS/CSS for the on Local SEO settings.
	 *
	 * @return void
	 */
	public function enqueueLocalBusinessSeoScripts() {
		if (
			aioseo()->helpers->isScreenBase( 'event-espresso' ) ||
			aioseo()->helpers->isScreenBase( 'post' ) ||
			aioseo()->helpers->isScreenBase( 'term' ) ||
			aioseo()->helpers->isScreenBase( 'edit-tags' )
		) {
			aioseo()->helpers->enqueueScript(
				'aioseo-localbusinessseo-settings-metabox',
				'js/local-business-seo.js'
			);
			wp_localize_script(
				'aioseo-localbusinessseo-settings-metabox',
				'aioseo',
				aioseo()->helpers->getVueData()
			);
			$rtl = is_rtl() ? '.rtl' : '';
			aioseo()->helpers->enqueueStyle(
				'aioseo-localbusinessseo-settings-metabox',
				"css/local-business-seo$rtl.css"
			);
		}
	}

	/**
	 * Adds a meta box to Local SEO posts.
	 *
	 * @return void
	 */
	public function addLocalBusinessSeoSettingsMetabox() {
		add_meta_box(
			'aioseo-localbusinessseo',
			'AIOSEO Local',
			[ $this, 'localBusinessSEOSettingsMetabox' ],
			[ 'aioseo-location' ],
			'normal',
			'high'
		);
	}

	/**
	 * Render the Local SEO settings metabox with Vue App wrapper.
	 *
	 * @param  WP_Post $post The current post.
	 * @return string
	 */
	public function localBusinessSEOSettingsMetabox( $post ) {
		$path = 'M13.6449 2.35C12.1949 0.9 10.2049 0 7.99488 0C3.57488 0 0.00488281 3.58 0.00488281 8C0.00488281 12.42 3.57488 16 7.99488 16C11.7249 16 14.8349 13.45 15.7249 10H13.6449C12.8249 12.33 10.6049 14 7.99488 14C4.68488 14 1.99488 11.31 1.99488 8C1.99488 4.69 4.68488 2 7.99488 2C9.65488 2 11.1349 2.69 12.2149 3.78L8.99488 7H15.9949V0L13.6449 2.35Z'; // phpcs:ignore Generic.Files.LineLength.MaxExceeded
		?>
		<div id="aioseo-localbusinessseo-metabox">
			<svg class="loading" width="32" height="64" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="<?php echo esc_attr( $path ); ?>" fill="currentcolor"/>
			</svg>
		</div>
		<?php
	}

	// public function registerBlockCategory( $categories, $post ) {
	//  return array_merge(
	//      $categories,
	//      [
	//          [
	//              'slug'  => 'aioseo',
	//              'title' => AIOSEO_PLUGIN_SHORT_NAME,
	//          ],
	//      ]
	//  );
	// }

	// public function registerBlocks() {
	//  foreach ( $this->blocks as $block ) {

	//      require AIOSEO_DIR . '/app/Common/Blocks/' . $block . '/index.php';

	//      register_block_type(
	//          'aioseo/' . $block,
	//          [
	//              'render_callback' => 'renderCallback',
	//          ]
	//      );
	//  }
	// }

	// public function renderCallback( $block_attributes, $content ) {
	//  error_log( 'renderCallback' );
	//  return '<h1>Example block</h1>';
	// }
}