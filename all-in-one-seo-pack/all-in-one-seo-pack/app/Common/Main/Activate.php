<?php
namespace AIOSEO\Plugin\Common\Main;

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class Activate {

	/**
	 * Construct method.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		register_activation_hook( AIOSEO_FILE, [ $this, 'activate' ] );
		register_deactivation_hook( AIOSEO_FILE, [ $this, 'deactivate' ] );

		// If Pro just deactivated the lite version, we need to manually run the activation hook, because it doesn't run here.
		$proDeactivatedLite = (bool) get_transient( 'aioseo_pro_just_deactivated_lite' );
		if ( $proDeactivatedLite ) {
			delete_transient( 'aioseo_pro_just_deactivated_lite', true );
			$this->activate( false );
		}
	}

	/**
	 * Runs on deactivation.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function deactivate() {
		aioseo()->access->removeCapabilities();
		\AIOSEO\Plugin\Common\Sitemap\Rewrite::removeRewriteRules( [], true );
	}
}