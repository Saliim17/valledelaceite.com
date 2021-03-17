<?php
namespace AIOSEO\Plugin\Common\ImportExport\YoastSeo;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the General Settings.
 *
 * @since 4.0.0
 */
class GeneralSettings {

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->options = get_option( 'wpseo' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->checkIfTrueSeoIsDisabled();

		$settings = [
			'googleverify'       => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'google' ] ],
			'msverify'           => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'bing' ] ],
			'yandexverify'       => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'yandex' ] ],
			'baiduverify'        => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'baidu' ] ],
			'enable_xml_sitemap' => [ 'type' => 'boolean', 'newOption' => [ 'sitemap', 'general', 'enable' ] ],
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Checks if TrueSEO should be disabled.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function checkIfTrueSeoIsDisabled() {
		if ( ! isset( $this->options['content_analysis_active'] ) || ! isset( $this->options['keyword_analysis_active'] ) ) {
			return;
		}

		if ( ! $this->options['content_analysis_active'] && ! $this->options['keyword_analysis_active'] ) {
			aioseo()->options->advanced->truSeo = false;
		}
	}
}