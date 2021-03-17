<?php
namespace AIOSEO\Plugin\Common\ImportExport\SeoPress;

use AIOSEO\Plugin\Common\ImportExport;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Contains helper methods for the import from SEOPress.
 *
 * @since 4.0.0
 */
class Helpers extends ImportExport\Helpers {

	/**
	 * Converts the macros from SEOPress to our own smart tags.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string with macros.
	 * @return string $string The string with smart tags.
	 */
	public function macrosToSmartTags( $string ) {
		$macros = [
			'%%tagline%%' => '#tagline',
		];

		foreach ( $macros as $macro => $tag ) {
			$string = preg_replace( "#$macro(?![a-zA-Z0-9_])#im", $tag, $string );
		}
		return $string;
	}
}