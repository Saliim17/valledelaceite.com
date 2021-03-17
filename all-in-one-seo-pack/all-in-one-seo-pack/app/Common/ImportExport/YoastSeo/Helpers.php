<?php
namespace AIOSEO\Plugin\Common\ImportExport\YoastSeo;

use AIOSEO\Plugin\Common\ImportExport;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Contains helper methods for the import from Rank Math.
 *
 * @since 4.0.0
 */
class Helpers extends ImportExport\Helpers {

	/**
	 * Converts the macros from Yoast SEO to our own smart tags.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string with macros.
	 * @return string $string The string with smart tags.
	 */
	public function macrosToSmartTags( $string ) {
		$macros = [
			'%%sitename%%'             => '#site_title',
			'%%sitedesc%%'             => '#tagline',
			'%%sep%%'                  => '#separator_sa',
			'%%title%%'                => '#post_title',
			'%%term_title%%'           => '#taxonomy_title',
			'%%term_description%%'     => '#taxonomy_description',
			'%%category_description%%' => '#taxonomy_description',
			'%%tag_description%%'      => '#taxonomy_description',
			'%%primary_category%%'     => '#taxonomy_title',
			'%%archive_title%%'        => '#archive_title',
			'%%pagenumber%%'           => '#page_number',
			'%%caption%%'              => '#attachment_caption',
			'%%name%%'                 => '#author_first_name #author_last_name',
			'%%user_description%%'     => '#author_bio',
			'%%date%%'                 => '#archive_date',
			'%%currentday%%'           => '#current_day',
			'%%currentmonth%%'         => '#current_month',
			'%%currentyear%%'          => '#current_year',
			'%%searchphrase%%'         => '#search_term',
			'%%AUTHORLINK%%'           => '#author_link',
			'%%POSTLINK%%'             => '#post_link',
			'%%BLOGLINK%%'             => '#site_link',
			'%%category%%'             => '#categories',
			'%%parent_title%%'         => '#parent_title',
			'%%wc_sku%%'               => '#woocommerce_sku',
			'%%wc_price%%'             => '#woocommerce_price',
			'%%wc_brand%%'             => '#woocommerce_brand',
			'%%tag%%'                  => '',
			'%%excerpt%%'              => '#post_excerpt',
			'%%excerpt_only%%'         => '',
			'%%id%%'                   => '',
			'%%parent_title%%'         => '',
			'%%page%%'                 => '',
			'%%modified%%'             => '',
			'%%pt_single%%'            => '',
			'%%pt_plural%%'            => '',
			'%%pagetotal%%'            => '',
			'%%focuskw%%'              => '',
			'%%term404%%'              => '',
			'%%ct_desc_[^%]*%%'        => '',
			'%%[^%]*%%'                => ''
		];

		if ( preg_match( '#%%BLOGDESCLINK%%#', $string ) ) {
			$blogDescriptionLink = '<a href="' .
				aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'url' ) ) . '">' .
				aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) ) . ' - ' .
				aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'description' ) ) . '</a>';

			$string = str_replace( '%%BLOGDESCLINK%%', $blogDescriptionLink, $string );
		}

		if ( preg_match_all( '#%%cf_([^%]*)%%#', $string, $matches ) && ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $name ) {
				if ( ! preg_match( '#\s#', $name ) ) {
					$string = preg_replace( "#%%cf_$name%%#", "#custom_field-$name", $string );
				}
			}
		}

		if ( preg_match_all( '#%%tax_([^%]*)%%#', $string, $matches ) && ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $name ) {
				if ( ! preg_match( '#\s#', $name ) ) {
					$string = preg_replace( "#%%tax_$name%%#", "#tax_name-$name", $string );
				}
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