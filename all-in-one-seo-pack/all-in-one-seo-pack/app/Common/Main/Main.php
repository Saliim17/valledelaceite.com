<?php
namespace AIOSEO\Plugin\Common\Main;

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class Main {
	/**
	 * Construct method.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->media = new Media();

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueueFrontEndAssets' ] );
		add_action( 'admin_footer', [ $this, 'adminFooter' ] );
		add_action( 'wp_footer', [ $this, 'adminFooter' ] );
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function enqueueAssets() {
		// Scripts.
		aioseo()->helpers->enqueueScript(
			'aioseo-app',
			'js/app.js'
		);
		aioseo()->helpers->enqueueScript(
			'aioseo-vendors',
			'js/chunk-vendors.js'
		);
		aioseo()->helpers->enqueueScript(
			'aioseo-common',
			'js/chunk-common.js'
		);

		// Styles.
		$rtl = is_rtl() ? '.rtl' : '';
		aioseo()->helpers->enqueueStyle(
			'aioseo-common',
			"css/chunk-common$rtl.css"
		);
		aioseo()->helpers->enqueueStyle(
			'aioseo-vendors',
			"css/chunk-vendors$rtl.css"
		);
		aioseo()->helpers->enqueueStyle(
			'aioseo-app-style',
			"css/app$rtl.css"
		);
	}

	/**
	 * Enqueue styles on the front-end.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function enqueueFrontEndAssets() {
		if ( ! is_user_logged_in() || ! current_user_can( 'aioseo_manage_seo' ) ) {
			return;
		}

		// Styles.
		aioseo()->helpers->enqueueStyle(
			'aioseo-admin-bar',
			'css/aioseo-admin-bar.css'
		);
	}

	/**
	 * Enqueue the footer file to let vue attach.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function adminFooter() {
		echo '<div id="aioseo-admin"></div>';
	}
}