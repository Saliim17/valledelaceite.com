<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs;

/**
 * WebSite graph class.
 *
 * @since 4.0.0
 */
class WebSite extends Graph {

	/**
	 * Returns the graph data.
	 *
	 * @since 4.0.0
	 *
	 * @return array $data The graph data.
	 */
	public function get() {
		$homeUrl = trailingslashit( home_url() );
		$data    = [
			'@type'       => 'WebSite',
			'@id'         => $homeUrl . '#website',
			'url'         => $homeUrl,
			'name'        => aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) ),
			'description' => aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'description' ) ),
			'publisher'   => [ '@id' => $homeUrl . '#' . aioseo()->options->searchAppearance->global->schema->siteRepresents ]
		];

		if ( aioseo()->options->searchAppearance->advanced->sitelinks ) {
			$data['potentialAction'] = [
				'@type'       => 'SearchAction',
				'target'      => $homeUrl . '?s={search_term_string}',
				'query-input' => 'required name=search_term_string',
			];
		}
		return $data;
	}
}