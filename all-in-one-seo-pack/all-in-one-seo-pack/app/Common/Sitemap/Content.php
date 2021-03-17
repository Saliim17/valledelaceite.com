<?php
namespace AIOSEO\Plugin\Common\Sitemap;

/**
 * Determines which content should be included in the sitemap.
 *
 * @since 4.0.0
 */
class Content {
	/**
	 * This is cached so we don't do the lookup each query.
	 *
	 * @since 4.0.0
	 *
	 * @var boolean
	 */
	private static $wpml = null;

	/**
	 * Returns the entries for the requested sitemap.
	 *
	 * @since 4.0.0
	 *
	 * @return array The sitemap entries.
	 */
	public function get() {
		if ( ! in_array( aioseo()->sitemap->type, [ 'general', 'rss' ], true ) || ! $this->isEnabled() ) {
			return [];
		}

		switch ( aioseo()->sitemap->indexName ) {
			case 'rss':
				return $this->rss();
			case 'root':
				if ( 'root' === aioseo()->sitemap->indexName && aioseo()->options->sitemap->general->indexes ) {
					return aioseo()->sitemap->root->indexes();
				}
				// If indexes are disabled, throw all entries together into one big file.
				return $this->nonIndexed();
			default:
				// Check if requested index has a dedicated method.
				$methodName = aioseo()->helpers->dashesToCamelCase( aioseo()->sitemap->indexName );
				if ( method_exists( $this, $methodName ) ) {
					return $this->$methodName();
				}

				// Check if requested index is a registered post type.
				if ( in_array( aioseo()->sitemap->indexName, aioseo()->sitemap->helpers->includedPostTypes(), true ) ) {
					return $this->posts( aioseo()->sitemap->indexName );
				}

				// Check if requested index is a registered taxonomy.
				if ( in_array( aioseo()->sitemap->indexName, aioseo()->sitemap->helpers->includedTaxonomies(), true ) ) {
					return $this->terms( aioseo()->sitemap->indexName );
				}
		}
	}

	/**
	 * Checks if the requested sitemap is enabled.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether the sitemap is enabled.
	 */
	public function isEnabled() {
		$options = aioseo()->options->noConflict();
		if ( ! $options->sitemap->{aioseo()->sitemap->type}->enable ) {
			return false;
		}

		if (
			$options->sitemap->{aioseo()->sitemap->type}->has( 'indexes' ) &&
			! $options->sitemap->{aioseo()->sitemap->type}->indexes &&
			'root' !== aioseo()->sitemap->indexName
		) {
			return false;
		}

		if ( $options->sitemap->{aioseo()->sitemap->type}->postTypes->all ) {
			return true;
		}
		$included = aioseo()->sitemap->helpers->includedPostTypes();
		return ! empty( $included );
	}

	/**
	 * Returns all sitemap entries if indexing is disabled.
	 *
	 * @since 4.0.0
	 *
	 * @return array $entries The sitemap entries.
	 */
	private function nonIndexed() {
		$entries = array_merge( $this->addl(), $this->author(), $this->date(), $this->postArchive() );

		$postTypes = aioseo()->sitemap->helpers->includedPostTypes();
		if ( $postTypes ) {
			foreach ( $postTypes as $postType ) {
				$entries = array_merge( $entries, $this->posts( $postType ) );
			}
		}

		$taxonomies = aioseo()->sitemap->helpers->includedTaxonomies();
		if ( $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {
				$entries = array_merge( $entries, $this->terms( $taxonomy ) );
			}
		}
		return $entries;
	}

	/**
	 * Returns all post entries for a given post type.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $postType       The name of the post type.
	 * @param  array  $additionalArgs Any additional arguments for the post query.
	 * @return array                  The sitemap entries.
	 */
	public function posts( $postType, $additionalArgs = [] ) {
		$posts = aioseo()->sitemap->query->posts( $postType, $additionalArgs );
		if ( ! $posts ) {
			return [];
		}

		// Return if we're determining the root indexes.
		if ( ! empty( $additionalArgs['root'] ) ) {
			return $posts;
		}

		$entries          = [];
		$isStaticHomepage = 'page' === get_option( 'show_on_front' );
		$homePageId       = (int) get_option( 'page_on_front' );
		$excludeImages    = aioseo()->options->sitemap->general->advancedSettings->enable && aioseo()->options->sitemap->general->advancedSettings->excludeImages;
		foreach ( $posts as $post ) {
			$entry = [
				'loc'        => get_permalink( $post->ID ),
				'lastmod'    => aioseo()->helpers->formatDateTime( $post->post_modified_gmt ),
				'changefreq' => aioseo()->sitemap->priority->frequency( 'postTypes', $post, $postType ),
				'priority'   => aioseo()->sitemap->priority->priority( 'postTypes', $post, $postType ),
			];

			if ( ! $excludeImages ) {
				$metaData        = aioseo()->meta->metaData->getMetaData( $post->ID );
				$entry['images'] = ! empty( $metaData->images ) ? $metaData->images : [];
			}

			// Override priority/frequency for static homepage.
			if ( $isStaticHomepage && $homePageId === $post->ID ) {
				$entry['changefreq'] = aioseo()->sitemap->priority->frequency( 'homePage' );
				$entry['priority']   = aioseo()->sitemap->priority->priority( 'homePage' );
			}

			$entry = $this->localizeEntries( $entry, $post, $postType );
			if ( $entry ) {
				$entries[] = $entry;
			}
		}
		return apply_filters( 'aioseo_sitemap_posts', $entries, $postType );
	}

	/**
	 * Localize the entries if WPML (or others in the future) are active.
	 *
	 * @since 4.0.0
	 *
	 * @param  array   $entry    The entry.
	 * @param  WP_Post $post     The post object.
	 * @param  string  $postType The post type.
	 * @param  boolean $rss      Whether or not we are localizing for the RSS sitemap.
	 * @return array             The entry.
	 */
	private function localizeEntries( $entry, $post, $postType, $rss = false ) {
		if ( null === self::$wpml ) {
			self::$wpml = false;
			if ( aioseo()->helpers->isWpmlActive() ) {
				self::$wpml = apply_filters( 'wpml_default_language', null );
			}
		}

		if ( ! self::$wpml ) {
			return $entry;
		}

		$parentId = apply_filters( 'wpml_object_id', $post->ID, $postType, false, self::$wpml );
		if ( ! empty( $parentId ) && $parentId !== $post->ID && ! $rss ) {
			// Skip adding a translation to the entries.
			return null;
		}

		$translationGroupId = apply_filters( 'wpml_element_trid', null, $post->ID );
		$translations       = apply_filters( 'wpml_get_element_translations', null, $translationGroupId, $postType );
		if ( empty( $translations ) ) {
			return $entry;
		}

		$entry['languages'] = [];
		foreach ( $translations as $translation ) {
			if ( empty( $translation->element_id ) ) {
				continue;
			}

			$permalink = ! empty( $entry['loc'] ) ? $entry['loc'] : (
				! empty( $entry['guid'] ) ? $entry['guid'] : ''
			);

			$location = apply_filters( 'wpml_permalink', $permalink, $translation->language_code );
			if ( $rss ) {
				$entry['guid'] = $location;
				continue;
			}

			if ( $location === $permalink ) {
				$entry['language'] = $translation->language_code;
				continue;
			}

			$entry['languages'][] = [
				'language' => $translation->language_code,
				'location' => $location
			];
		}

		if ( empty( $entry['languages'] ) ) {
			unset( $entry['languages'] );
		}

		return $entry;
	}

	/**
	 * Returns all post archive entries.
	 *
	 * @since 4.0.0
	 *
	 * @return array $entries The sitemap entries.
	 */
	private function postArchive() {
		$entries = [];
		foreach ( aioseo()->sitemap->helpers->includedPostTypes() as $postType ) {
			if ( in_array( $postType, [ 'post', 'page', 'product' ], true ) ) {
				continue;
			}

			$post = aioseo()->db
				->start( aioseo()->db->db->posts . ' as p', true )
				->select( 'p.ID' )
				->where( 'p.post_status', 'publish' )
				->where( 'p.post_type', $postType )
				->limit( 1 )
				->run()
				->result();

			if ( ! $post ) {
				continue;
			}

			$url = get_post_type_archive_link( $postType );
			if ( $url ) {
				$entries[] = [
					'loc'        => $url,
					'lastmod'    => aioseo()->sitemap->helpers->lastModifiedPostTime( $postType ),
					'changefreq' => aioseo()->sitemap->priority->frequency( 'archive' ),
					'priority'   => aioseo()->sitemap->priority->priority( 'archive' ),
				];
			}
		}
		return apply_filters( 'aioseo_sitemap_post_archives', $entries );
	}

	/**
	 * Returns all term entries for a given taxonomy.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $taxonomy       The name of the taxonomy.
	 * @param  array  $additionalArgs Any additional arguments for the term query.
	 * @return array                  The sitemap entries.
	 */
	public function terms( $taxonomy, $additionalArgs = [] ) {
		$terms = aioseo()->sitemap->query->terms( $taxonomy, $additionalArgs );
		if ( ! $terms ) {
			return [];
		}

		// Get all registered post types for the taxonomy.
		$postTypes = [];
		foreach ( get_post_types() as $postType ) {
			$taxonomies = get_object_taxonomies( $postType );
			foreach ( $taxonomies as $name ) {
				if ( $taxonomy === $name ) {
					$postTypes[] = $postType;
				}
			}
		}

		// Return if we're determining the root indexes.
		if ( ! empty( $additionalArgs['root'] ) ) {
			return $terms;
		}

		$entries = [];
		foreach ( $terms as $term ) {
			$entries[] = [
				'loc'        => get_term_link( $term->term_id ),
				'lastmod'    => $this->getTermLastModified( $term->term_id ),
				'changefreq' => aioseo()->sitemap->priority->frequency( 'taxonomies', $term, $taxonomy ),
				'priority'   => aioseo()->sitemap->priority->priority( 'taxonomies', $term, $taxonomy ),
				'images'     => aioseo()->sitemap->image->term( $term )
			];
		}
		return apply_filters( 'aioseo_sitemap_terms', $entries, $taxonomy );
	}

	/**
	 * Adds the last modified date to a given term.
	 *
	 * @since 4.0.0
	 *
	 * @param  int    $termId The term ID.
	 * @return string         The lastmod timestamp.
	 */
	public function getTermLastModified( $termId ) {
		// @TODO: [V4+] Think about checking for excluded/noindexed posts.
		// Might be a hit on performance.
		$termRelationshipsTable = aioseo()->db->db->prefix . 'term_relationships';
		$lastModified = aioseo()->db
			->start( aioseo()->db->db->posts . ' as p', true )
			->select( 'MAX(`p`.`post_modified_gmt`) as last_modified' )
			->whereRaw( "
			( `p`.`ID` IN
				(
					SELECT `tr`.`object_id`
					FROM `$termRelationshipsTable` as tr
					WHERE `tr`.`term_taxonomy_id` = '$termId'
				)
			)" )
			->run()
			->result();

		if ( empty( $lastModified[0]->last_modified ) ) {
			return '';
		}

		return aioseo()->helpers->formatDateTime( $lastModified[0]->last_modified );
	}

	/**
	 * Returns all additional pages.
	 *
	 * @since 4.0.0
	 *
	 * @return array The sitemap entries.
	 */
	public function addl() {
		$entries = [];
		if ( 'posts' === get_option( 'show_on_front' ) || ! in_array( 'page', aioseo()->sitemap->helpers->includedPostTypes(), true ) ) {
			$frontPageId = (int) get_option( 'page_on_front' );
			$post        = aioseo()->helpers->getPost( $frontPageId );
			$entries[] = [
				'loc'        => aioseo()->helpers->localizedUrl( '/' ),
				'lastmod'    => $post ? aioseo()->helpers->formatDateTime( $post->post_modified_gmt ) : aioseo()->sitemap->helpers->lastModifiedPostTime(),
				'changefreq' => aioseo()->sitemap->priority->frequency( 'homePage' ),
				'priority'   => aioseo()->sitemap->priority->priority( 'homePage' ),
			];
		}

		if ( aioseo()->options->sitemap->general->additionalPages->enable ) {
			$additionalPages = aioseo()->options->sitemap->general->additionalPages->pages;
			$pages           = [];
			foreach ( $additionalPages as $page ) {
				$additionalPage = json_decode( $page );
				if ( empty( $additionalPage->url ) ) {
					continue;
				}

				$pages[] = $additionalPage;
			}

			if ( ! count( $pages ) ) {
				return $entries;
			}

			foreach ( $pages as $page ) {
				$entries[] = [
					'loc'        => $page->url,
					'lastmod'    => aioseo()->sitemap->helpers->lastModifiedAdditionalPage( $page ),
					'changefreq' => $page->frequency->value,
					'priority'   => $page->priority->value
				];
			}
		}
		return apply_filters( 'aioseo_sitemap_additional_pages', $entries );
	}

	/**
	 * Returns all author archive entries.
	 *
	 * @since 4.0.0
	 *
	 * @return array The sitemap entries.
	 */
	public function author() {
		if (
			! aioseo()->sitemap->helpers->lastModifiedPost() ||
			! aioseo()->options->sitemap->general->author ||
			! aioseo()->options->searchAppearance->archives->author->show ||
			(
				! aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->default &&
				aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->noindex
			) ||
			(
				aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->default &&
				(
					! aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default ||
					aioseo()->options->searchAppearance->advanced->globalRobotsMeta->noindex
				)
			)
		) {
			return [];
		}

		$args = [
			'has_published_posts' => [ 'post' ]
		];

		$authors = get_users( $args );
		if ( ! $authors ) {
			return [];
		}

		$entries = [];
		foreach ( $authors as $author ) {
			$entries[] = [
				'loc'        => get_author_posts_url( $author->ID ),
				'lastmod'    => aioseo()->sitemap->helpers->lastModifiedPostTime( 'post', [ 'author' => $author->ID ] ),
				'changefreq' => aioseo()->sitemap->priority->frequency( 'author' ),
				'priority'   => aioseo()->sitemap->priority->priority( 'author' ),
			];
		}
		return apply_filters( 'aioseo_sitemap_author_archives', $entries );
	}

	/**
	 * Returns all data archive entries.
	 *
	 * @since 4.0.0
	 *
	 * @return array The sitemap entries.
	 */
	public function date() {
		if (
			! aioseo()->sitemap->helpers->lastModifiedPost() ||
			! aioseo()->options->sitemap->general->date ||
			! aioseo()->options->searchAppearance->archives->date->show ||
			(
				! aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->default &&
				aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->noindex
			) ||
			(
				aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->default &&
				(
					! aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default ||
					aioseo()->options->searchAppearance->advanced->globalRobotsMeta->noindex
				)
			)
		) {
			return [];
		}

		global $wpdb;
		$dates = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				YEAR(post_date) AS `year`,
				MONTH(post_date) AS `month`,
				post_modified_gmt
			FROM {$wpdb->posts}
			WHERE post_type = %s AND post_status = 'publish'
			GROUP BY
				YEAR(post_date),
				MONTH(post_date)
			ORDER BY post_date ASC LIMIT %d",
			'post',
			50000
		) );

		if ( ! $dates ) {
			return [];
		}

		$entries = [];
		$year    = '';
		foreach ( $dates as $date ) {
			$entry = [
				'lastmod'    => aioseo()->helpers->formatDateTime( $date->post_modified_gmt ),
				'changefreq' => aioseo()->sitemap->priority->frequency( 'date' ),
				'priority'   => aioseo()->sitemap->priority->priority( 'date' ),
			];

			// Include each year only once.
			if ( $year !== $date->year ) {
				$year         = $date->year;
				$entry['loc'] = get_year_link( $date->year );
				$entries[]    = $entry;
			}
			$entry['loc'] = get_month_link( $date->year, $date->month );
			$entries[]    = $entry;
		}
		return apply_filters( 'aioseo_sitemap_date_archives', $entries );
	}

	/**
	 * Returns all entries for the RSS Sitemap.
	 *
	 * @since 4.0.0
	 *
	 * @return array The sitemap entries.
	 */
	public function rss() {
		$posts = aioseo()->sitemap->query->posts(
			aioseo()->sitemap->helpers->includedPostTypes(),
			[ 'orderBy' => '`p`.`post_modified_gmt` DESC' ]
		);

		if ( ! count( $posts ) ) {
			return [];
		}

		$entries = [];
		foreach ( $posts as $post ) {
			$entry = [
				'guid'        => get_permalink( $post->ID ),
				'title'       => get_the_title( $post ),
				'description' => get_post_field( 'post_excerpt', $post->ID ),
				'pubDate'     => aioseo()->helpers->formatDateTime( $post->post_modified_gmt )
			];

			$entries[] = $this->localizeEntries( $entry, $post, $post->post_type, true );
		}

		usort( $entries, function( $a, $b ) {
			return $a['pubDate'] < $b['pubDate'];
		});
		return apply_filters( 'aioseo_sitemap_rss', $entries );
	}
}