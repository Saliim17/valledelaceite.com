<?php
namespace AIOSEO\Plugin\Common\Admin;

use AIOSEO\Plugin\Common\Models;

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class PostSettings {
	/**
	 * Initialize the admin.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		if ( is_admin() ) {
			// Load Vue APP
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueuePostSettingsAssets' ] );

			// Add metabox
			add_action( 'add_meta_boxes', [ $this, 'addPostSettingsMetabox' ] );

			// Add metabox to terms on init hook
			add_action( 'init', [ $this, 'init' ], 1000 );

			// Save metabox
			add_action( 'save_post', [ $this, 'saveSettingsMetabox' ] );
			add_action( 'edit_attachment', [ $this, 'saveSettingsMetabox' ] );
			add_action( 'add_attachment', [ $this, 'saveSettingsMetabox' ] );
		}
	}

	/**
	 * Enqueues the JS/CSS for the on page/posts settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function enqueuePostSettingsAssets() {
		if (
			aioseo()->helpers->isScreenBase( 'event-espresso' ) ||
			aioseo()->helpers->isScreenBase( 'post' ) ||
			aioseo()->helpers->isScreenBase( 'term' ) ||
			aioseo()->helpers->isScreenBase( 'edit-tags' )
		) {
			$page = null;
			if (
				aioseo()->helpers->isScreenBase( 'event-espresso' ) ||
				aioseo()->helpers->isScreenBase( 'post' )
			) {
				$page = 'post';
			}

			aioseo()->helpers->enqueueScript(
				'aioseo-post-settings-metabox',
				'js/post-settings.js'
			);
			wp_localize_script(
				'aioseo-post-settings-metabox',
				'aioseo',
				aioseo()->helpers->getVueData( $page )
			);

			$rtl = is_rtl() ? '.rtl' : '';
			aioseo()->helpers->enqueueStyle(
				'aioseo-post-settings-metabox',
				"css/post-settings$rtl.css"
			);
		}

		$screen = get_current_screen();
		if ( 'attachment' === $screen->id ) {
			wp_enqueue_media();
		}
	}

	/**
	 * Adds a meta box to page/posts screens.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addPostSettingsMetabox() {
		$options  = aioseo()->options->noConflict();
		$screen   = get_current_screen();
		$postType = $screen->post_type;

		$pageAnalysisSettingsCapability = aioseo()->access->hasCapability( 'aioseo_page_analysis' );
		$generalSettingsCapability      = aioseo()->access->hasCapability( 'aioseo_page_general_settings' );
		$socialSettingsCapability       = aioseo()->access->hasCapability( 'aioseo_page_social_settings' );
		$schemaSettingsCapability       = aioseo()->access->hasCapability( 'aioseo_page_schema_settings' );
		$advancedSettingsCapability     = aioseo()->access->hasCapability( 'aioseo_page_advanced_settings' );

		if (
			$options->searchAppearance->dynamic->postTypes->has( $postType ) &&
			$options->searchAppearance->dynamic->postTypes->$postType->advanced->showMetaBox &&
			! (
				empty( $pageAnalysisSettingsCapability ) &&
				empty( $generalSettingsCapability ) &&
				empty( $socialSettingsCapability ) &&
				empty( $schemaSettingsCapability ) &&
				empty( $advancedSettingsCapability )
			)
		) {
			// Translators: 1 - The plugin short name ("AIOSEO").
			$aioseoMetaboxTitle = sprintf( esc_html__( '%1$s Settings', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_SHORT_NAME );

			add_meta_box(
				'aioseo-settings',
				$aioseoMetaboxTitle,
				[ $this, 'postSettingsMetabox' ],
				[ $postType ],
				'normal',
				apply_filters( 'aioseo_post_metabox_priority', 'high' )
			);
		}
	}

	/**
	 * Render the on page/posts settings metabox with Vue App wrapper.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post $post The current post.
	 * @return string
	 */
	public function postSettingsMetabox() {
		?>
		<div id="aioseo-post-settings-field">
			<input type="hidden" name="aioseo-post-settings" id="aioseo-post-settings" value="" />
			<?php wp_nonce_field( 'aioseoPostSettingsNonce', 'PostSettingsNonce' ); ?>
		</div>
		<div id="aioseo-post-settings-metabox">
			<div style="height:50px">
				<div class="aioseo-loading-spinner dark" style="top:calc( 50% - 17px);left:calc( 50% - 17px);">
					<div class="double-bounce1"></div>
					<div class="double-bounce2"></div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handles metabox saving.
	 *
	 * @since 4.0.3
	 *
	 * @param  int  $postId Post ID.
	 * @return void
	 */
	public function saveSettingsMetabox( $postId ) {
		// Ignore auto saving
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Security check
		if ( ! isset( $_POST['PostSettingsNonce'] ) || ! wp_verify_nonce( $_POST['PostSettingsNonce'], 'aioseoPostSettingsNonce' ) ) {
			return;
		}

		// If we don't have our post settings input, we can safely skip.
		if ( ! isset( $_POST['aioseo-post-settings'] ) ) {
			return;
		}

		// Check user permissions
		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return;
		}

		$currentPost = json_decode( stripslashes( $_POST['aioseo-post-settings'] ), true ); // phpcs:ignore HM.Security.ValidatedSanitizedInput

		Models\Post::savePost( $postId, $currentPost );

	}
}