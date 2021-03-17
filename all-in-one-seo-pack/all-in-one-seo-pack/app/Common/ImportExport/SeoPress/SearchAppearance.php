<?php
namespace AIOSEO\Plugin\Common\ImportExport\SeoPress;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Search Appearance settings.
 *
 * @since 4.0.0
 */
class SearchAppearance {

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->options = get_option( 'seopress_titles_option_name' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateTitleFormats();

	}

	/**
	 * Migrates the title format settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateTitleFormats() {
		if ( isset( $this->options['seopress_titles_home_site_title'] ) ) {
			aioseo()->options->searchAppearance->global->siteTitle =
				aioseo()->importExport->seoPress->helpers->macrosToSmartTags( $this->options['seopress_titles_home_site_title'] );
		}
	}

}