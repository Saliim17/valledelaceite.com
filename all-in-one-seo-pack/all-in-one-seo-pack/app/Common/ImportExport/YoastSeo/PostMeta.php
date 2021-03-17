<?php
namespace AIOSEO\Plugin\Common\ImportExport\YoastSeo;

use AIOSEO\Plugin\Common\ImportExport;
use AIOSEO\Plugin\Common\Models;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Imports the post meta from Yoast SEO.
 *
 * @since 4.0.0
 */
class PostMeta {

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function scheduleImport() {
		try {
			if ( as_next_scheduled_action( aioseo()->importExport->yoastSeo->postActionName ) ) {
				return;
			}

			if ( ! get_transient( 'aioseo_import_post_meta_yoast_seo' ) ) {
				set_transient( 'aioseo_import_post_meta_yoast_seo', time(), WEEK_IN_SECONDS );
			}

			as_schedule_single_action( time(), aioseo()->importExport->yoastSeo->postActionName, [], 'aioseo' );
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * Imports the post meta.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function importPostMeta() {
		$postsPerAction  = 100;
		$publicPostTypes = implode( "', '", aioseo()->helpers->getPublicPostTypes( true ) );
		$timeStarted     = gmdate( 'Y-m-d H:i:s', get_transient( 'aioseo_import_post_meta_yoast_seo' ) );

		$posts = aioseo()->db
			->start( 'posts' . ' as p' )
			->select( 'p.ID, p.post_type' )
			->join( 'postmeta as pm', '`p`.`ID` = `pm`.`post_id`' )
			->leftJoin( 'aioseo_posts as ap', '`p`.`ID` = `ap`.`post_id`' )
			->whereRaw( "pm.meta_key LIKE '_yoast_wpseo_%'" )
			->whereRaw( "( p.post_type IN ( '$publicPostTypes' ) )" )
			->whereRaw( "( ap.post_id IS NULL OR ap.updated < '$timeStarted' )" )
			->orderBy( 'p.ID DESC' )
			->limit( $postsPerAction )
			->run()
			->result();

		if ( ! $posts || ! count( $posts ) ) {
			delete_transient( 'aioseo_import_post_meta_yoast_seo' );
			return;
		}

		$mappedMeta = [
			'_yoast_wpseo_title'                 => 'title',
			'_yoast_wpseo_metadesc'              => 'description',
			'_yoast_wpseo_canonical'             => 'canonical_url',
			'_yoast_wpseo_meta-robots-noindex'   => 'robots_noindex',
			'_yoast_wpseo_meta-robots-nofollow'  => 'robots_nofollow',
			'_yoast_wpseo_meta-robots-adv'       => '',
			'_yoast_wpseo_focuskw'               => '',
			'_yoast_wpseo_focuskeywords'         => '',
			'_yoast_wpseo_opengraph-title'       => 'og_title',
			'_yoast_wpseo_opengraph-description' => 'og_description',
			'_yoast_wpseo_opengraph-image'       => 'og_image_custom_url',
			'_yoast_wpseo_twitter-title'         => 'twitter_title',
			'_yoast_wpseo_twitter-description'   => 'twitter_description',
			'_yoast_wpseo_twitter-image'         => 'twitter_image_custom_url',
			'_yoast_wpseo_schema_page_type'      => '',
			'_yoast_wpseo_schema_article_type'   => ''
		];

		foreach ( $posts as $post ) {
			$postMeta = aioseo()->db
				->start( 'postmeta' . ' as pm' )
				->select( 'pm.meta_key, pm.meta_value' )
				->where( 'pm.post_id', $post->ID )
				->whereRaw( "`pm`.`meta_key` LIKE '_yoast_wpseo_%'" )
				->run()
				->result();

			if ( ! $postMeta || ! count( $postMeta ) ) {
				continue;
			}

			$meta = [
				'post_id' => (int) $post->ID,
			];

			foreach ( $postMeta as $record ) {
				$name  = $record->meta_key;
				$value = $record->meta_value;

				if ( ! in_array( $name, array_keys( $mappedMeta ), true ) ) {
					continue;
				}

				switch ( $name ) {
					case '_yoast_wpseo_meta-robots-noindex':
					case '_yoast_wpseo_meta-robots-nofollow':
						if ( (bool) $value ) {
							$meta[ $mappedMeta[ $name ] ]       = ! empty( $value );
							$meta['robots_default'] = false;
						}
						break;
					case '_yoast_wpseo_meta-robots-adv':
						$values = explode( ',', $value );
						if ( $values ) {
							foreach ( $values as $value ) {
								$meta[ "robots_$value" ] = true;
							}
						}
						break;
					case '_yoast_wpseo_canonical':
						$meta[ $mappedMeta[ $name ] ] = esc_url( $value );
						break;
					case '_yoast_wpseo_opengraph-image':
						$meta['og_image_type']        = 'custom_image';
						$meta[ $mappedMeta[ $name ] ] = esc_url( $value );
						break;
					case '_yoast_wpseo_twitter-image':
						$meta['twitter_use_og']       = false;
						$meta['twitter_image_type']   = 'custom_image';
						$meta[ $mappedMeta[ $name ] ] = esc_url( $value );
						break;
					case '_yoast_wpseo_schema_page_type':
						$value = preg_replace( '#\s#', '', $value );
						if ( in_array( $post->post_type, [ 'post', 'page', 'attachment' ], true ) ) {
							break;
						}
						if ( in_array( $value, ImportExport\SearchAppearance::$supportedWebPageGraphs, true ) ) {
							$meta[ $mappedMeta[ $name ] ] = 'WebPage';
							$options          = new \stdClass();
							$options->webPage = [ 'webPageType' => $value ];
						}
						$meta['schema_type_options'] = wp_json_encode( $options );
						break;
					case '_yoast_wpseo_schema_article_type':
						$value = preg_replace( '#\s#', '', $value );
						if ( 'none' === lcfirst( $value ) ) {
							$meta[ $mappedMeta[ $name ] ] = 'None';
							break;
						}

						if ( in_array( $post->post_type, [ 'page', 'attachment' ], true ) ) {
							break;
						}

						$options = new \stdClass();
						if ( isset( $meta['schema_type_options'] ) ) {
							$options = json_decode( $meta['schema_type_options'] );
						}

						$options->article = [ 'articleType' => 'Article' ];
						if ( in_array( $value, ImportExport\SearchAppearance::$supportedArticleGraphs, true ) ) {
							$options->article = [ 'articleType' => $value ];
						} else {
							$options->article = [ 'articleType' => 'BlogPosting' ];
						}

						$meta['schema_type']         = 'Article';
						$meta['schema_type_options'] = wp_json_encode( $options );
						break;
					case '_yoast_wpseo_focuskw':
						$keyphrase = [
							'focus'      => [ 'keyphrase' => aioseo()->helpers->sanitizeOption( $value ) ],
							'additional' => []
						];
						$meta['keyphrases'] = wp_json_encode( $keyphrase );
						break;
					case '_yoast_wpseo_focuskeywords':
						$keyphrases = [];
						if ( ! empty( $meta[ $mappedMeta[ $name ] ] ) ) {
							$keyphrases = (array) json_decode( $meta[ $mappedMeta[ $name ] ] );
						}

						$yoastKeyphrases = json_decode( $value );
						for ( $i = 0; $i < count( $yoastKeyphrases ); $i++ ) {
							$keyphrase = [ 'keyphrase' => aioseo()->helpers->sanitizeOption( $yoastKeyphrases[ $i ]->keyword ) ];
							$keyphrases['additional'][ $i ] = $keyphrase;
						}

						if ( ! empty( $keyphrases ) ) {
							$meta['keyphrases'] = wp_json_encode( $keyphrases );
						}
						break;
					case '_yoast_wpseo_title':
					case '_yoast_wpseo_metadesc':
					case '_yoast_wpseo_opengraph-title':
					case '_yoast_wpseo_opengraph-description':
						if ( 'page' === $post->post_type ) {
							$value = preg_replace( '#%%primary_category%%#', '', $value );
							$value = preg_replace( '#%%excerpt%%#', '', $value );
						}
						$value = aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $value );
					default:
						$meta[ $mappedMeta[ $name ] ] = esc_html( wp_strip_all_tags( strval( $value ) ) );
						break;
				}
			}

			$aioseoPost = Models\Post::getPost( (int) $post->ID );
			$aioseoPost->set( $meta );
			$aioseoPost->save();
		}

		if ( count( $posts ) === $postsPerAction ) {
			try {
				as_schedule_single_action( time() + 5, aioseo()->importExport->yoastSeo->postActionName, [], 'aioseo' );
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		} else {
			delete_transient( 'aioseo_import_post_meta_yoast_seo' );
		}
	}
}