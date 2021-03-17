<?php
namespace AIOSEO\Plugin\Common\ImportExport\RankMath;

use AIOSEO\Plugin\Common\ImportExport;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Contains helper methods for the import from Rank Math.
 *
 * @since 4.0.0
 */
class Helpers extends ImportExport\Helpers {

	/**
	 * Converts the macros from Rank Math to our own smart tags.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string with macros.
	 * @return string $string The string with smart tags.
	 */
	public function macrosToSmartTags( $string ) {
		$macros = [
			'%sitename%'         => '#site_title',
			'%blog_title%'       => '#site_title',
			'%blog_description%' => '#tagline',
			'%sitedesc%'         => '#tagline',
			'%sep%'              => '#separator_sa',
			'%post_title%'       => '#post_title',
			'%page_title%'       => '#post_title',
			'%postname%'         => '#post_title',
			'%title%'            => '#post_title',
			'%seo_title%'        => '#post_title',
			'%excerpt%'          => '#post_excerpt',
			'%wc_shortdesc%'     => '#post_excerpt',
			'%category%'         => '#taxonomy_title',
			'%term%'             => '#taxonomy_title',
			'%term_description%' => '#taxonomy_description',
			'%currentdate%'      => '#current_date',
			'%currentday%'       => '#current_day',
			'%currentmonth%'     => '#current_month',
			'%name%'             => '#author_first_name #author_last_name',
			'%author%'           => '#author_first_name #author_last_name',
			'%date%'             => '#post_date',
			'%year%'             => '#current_year',
			'%user_description%' => '',
			'%search_query%'     => '#search_term',
			'%AUTHORLINK%'       => '#author_link',
			'%POSTLINK%'         => '#post_link',
			'%BLOGLINK%'         => '#site_link',
			/* '%seo_description%'  => '',
			'%wc_price%'         => '',
			'%page%'             => '',
			'%FEATUREDIMAGE%'    => '',
			'%filename%'         => '',*/
		];

		if ( preg_match( '#%BLOGDESCLINK%#', $string ) ) {
			$blogDescriptionLink = '<a href="' .
				aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'url' ) ) . '">' .
				aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) ) . ' - ' .
				aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'description' ) ) . '</a>';

			$string = str_replace( '%BLOGDESCLINK%', $blogDescriptionLink, $string );
		}

		if ( preg_match_all( '#%customfield\(([^%\s]*)\)%#', $string, $matches ) && ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $name ) {
				$string = preg_replace( "#%customfield\($name\)%#", "#custom_field-$name", $string );
			}
		}

		if ( preg_match_all( '#%customterm\(([^%\s]*)\)%#', $string, $matches ) && ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $name ) {
				$string = preg_replace( "#%customterm\($name\)%#", "#tax_name-$name", $string );
			}
		}

		foreach ( $macros as $macro => $tag ) {
			$string = preg_replace( "#$macro(?![a-zA-Z0-9_])#im", $tag, $string );
		}

		// Strip out all remaining tags.
		$string = preg_replace( '/%[^\%\s]*\([^\%]*\)%/i', '', preg_replace( '/%[^\%\s]*%/i', '', $string ) );
		return trim( $string );
	}
}