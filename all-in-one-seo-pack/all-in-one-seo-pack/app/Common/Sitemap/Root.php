<?php
namespace AIOSEO\Plugin\Common\Sitemap;

/**
 * Determines which indexes should appear in the sitemap root index.
 *
 * @since 4.0.0
 */
class Root {

	/**
	 * Returns the indexes for the sitemap root index.
	 *
	 * @since 4.0.0
	 *
	 * @return array The indexes.
	 */
	public function indexes() {
		if ( 'general' !== aioseo()->sitemap->type ) {
			foreach ( aioseo()->sitemap->addons as $addon => $classes ) {
				if ( ! empty( $classes['root'] ) ) {
					$indexes = $classes['root']->indexes();
					if ( $indexes ) {
						return $indexes;
					}
				}
			}
			return $indexes;
		}

		$filename   = aioseo()->sitemap->filename;
		$postTypes  = aioseo()->sitemap->helpers->includedPostTypes();
		$taxonomies = aioseo()->sitemap->helpers->includedTaxonomies();

		$pages = [];
		foreach ( aioseo()->options->sitemap->general->additionalPages->pages as $page ) {
			$additionalPage = json_decode( $page );
			if ( empty( $additionalPage->url ) ) {
				continue;
			}

			$pages[] = $additionalPage;
		}

		$indexes = [];
		if (
			'posts' === get_option( 'show_on_front' ) ||
			( aioseo()->options->sitemap->general->additionalPages->enable && count( $pages ) ) ||
			! in_array( 'page', $postTypes, true )
		) {
			$indexes[] = $this->buildAdditionalIndexes();
		}

		if ( $postTypes ) {
			$hasPostArchive = false;
			foreach ( $postTypes as $postType ) {
				$postIndexes = $this->buildIndexesPostType( $postType );
				$indexes     = array_merge( $indexes, $postIndexes );

				if ( empty( $postIndexes ) || $hasPostArchive || in_array( $postType, [ 'post', 'page', 'product' ], true ) ) {
					continue;
				}

				if ( get_post_type_archive_link( $postType ) ) {
					$hasPostArchive = true;
					$indexes[]      = [
						'loc'     => aioseo()->helpers->localizedUrl( "/post-archive-$filename.xml" ),
						'lastmod' => aioseo()->sitemap->helpers->lastModifiedPostTime( $postType )
					];
				}
			}
		}

		if ( $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {
				$indexes = array_merge( $indexes, $this->buildIndexesTaxonomy( $taxonomy ) );
			}
		}

		if (
			aioseo()->sitemap->helpers->lastModifiedPost() &&
			aioseo()->options->sitemap->general->author &&
			aioseo()->options->searchAppearance->archives->author->show &&
			(
				aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->default ||
				! aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->noindex
			) &&
			(
				aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default ||
				! aioseo()->options->searchAppearance->advanced->globalRobotsMeta->noindex
			)
		) {
			$indexes[] = $this->buildIndex( 'author' );
		}

		if (
			aioseo()->sitemap->helpers->lastModifiedPost() &&
			aioseo()->options->sitemap->general->date &&
			aioseo()->options->searchAppearance->archives->date->show &&
			(
				aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->default ||
				! aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->noindex
			) &&
			(
				aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default ||
				! aioseo()->options->searchAppearance->advanced->globalRobotsMeta->noindex
			)
		) {
			$indexes[] = $this->buildIndex( 'date' );
		}
		return $indexes;
	}

	/**
	 * Builds a given index.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $indexName The index name.
	 * @return array             The index.
	 */
	public function buildIndex( $indexName ) {
		$filename = aioseo()->sitemap->filename;
		return [
			'loc'     => aioseo()->helpers->localizedUrl( "/$indexName-$filename.xml" ),
			'lastmod' => aioseo()->sitemap->helpers->lastModifiedPostTime()
		];
	}

	/**
	 * Builds the additional pages index.
	 *
	 * @since 4.0.0
	 *
	 * @return array The index.
	 */
	public function buildAdditionalIndexes() {
		$filename = aioseo()->sitemap->filename;
		return [
			'loc'     => aioseo()->helpers->localizedUrl( "/addl-$filename.xml" ),
			'lastmod' => aioseo()->sitemap->helpers->lastModifiedAdditionalPagesTime()
		];
	}

	/**
	 * Builds indexes for all eligible posts of a given post type.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $postType The post type.
	 * @return array            The indexes.
	 */
	public function buildIndexesPostType( $postType ) {
		$posts = aioseo()->sitemap->content->posts( $postType, [ 'root' => true ] );

		if ( ! $posts ) {
			foreach ( aioseo()->sitemap->addons as $addon => $classes ) {
				if ( ! empty( $classes['root'] ) ) {
					$posts = $classes['root']->buildIndexesPostType( $postType );
					if ( $posts ) {
						return $this->buildIndexes( $postType, $posts );
					}
				}
			}
		}

		if ( ! $posts ) {
			return [];
		}
		return $this->buildIndexes( $postType, $posts );
	}

	/**
	 *Builds indexes for all eligible terms of a given taxonomy.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $taxonomy The taxonomy.
	 * @return array            The indexes.
	 */
	public function buildIndexesTaxonomy( $taxonomy ) {
		$terms = aioseo()->sitemap->content->terms( $taxonomy, [ 'root' => true ] );

		if ( ! $terms ) {
			foreach ( aioseo()->sitemap->addons as $addon => $classes ) {
				if ( ! empty( $classes['root'] ) ) {
					$terms = $classes['root']->buildIndexesTaxonomy( $taxonomy );
					if ( $terms ) {
						return $this->buildIndexes( $taxonomy, $terms );
					}
				}
			}
		}

		if ( ! $terms ) {
			return [];
		}

		return $this->buildIndexes( $taxonomy, $terms );
	}

	/**
	 * Builds indexes for a given type.
	 *
	 * Acts as a helper function for buildIndexesPostTypes() and buildIndexesTaxonomies().
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name    The name of the object parent.
	 * @param  array  $entries The sitemap entries.
	 * @return array  $indexes The indexes.
	 */
	public function buildIndexes( $name, $entries ) {
		$filename = aioseo()->sitemap->filename;
		$chunks   = aioseo()->sitemap->helpers->chunkEntries( $entries );
		$indexes  = [];
		for ( $i = 0; $i < count( $chunks ); $i++ ) {
			$chunk       = array_values( $chunks[ $i ] );
			$indexNumber = 1 < count( $chunks ) ? $i + 1 : '';
			$index       = [ 'loc' => aioseo()->helpers->localizedUrl( "/$name-$filename$indexNumber.xml" ) ];

			if ( isset( $entries[0]->ID ) ) {
				$ids = array_map( function( $post ) {
					return $post->ID;
				}, $chunk );
				$ids = implode( "', '", $ids );

				$lastModified = aioseo()->db
					->start( aioseo()->db->db->posts . ' as p', true )
					->select( 'MAX(`p`.`post_modified_gmt`) as last_modified' )
					->whereRaw( "( `p`.`ID` IN ( '$ids' ) )" )
					->run()
					->result();

				if ( ! empty( $lastModified[0]->last_modified ) ) {
					$index['lastmod'] = aioseo()->helpers->formatDateTime( $lastModified[0]->last_modified );
				}
				$indexes[] = $index;
				continue;
			}

			$termIds = [];
			foreach ( $chunk as $term ) {
				$termIds[] = $term->term_id;
			}
			$termIds = implode( "', '", $termIds );

			$termRelationshipsTable = aioseo()->db->db->prefix . 'term_relationships';
			$lastModified = aioseo()->db
				->start( aioseo()->db->db->posts . ' as p', true )
				->select( 'MAX(`p`.`post_modified_gmt`) as last_modified' )
				->whereRaw( "
				( `p`.`ID` IN
					(
						SELECT `tr`.`object_id`
						FROM `$termRelationshipsTable` as tr
						WHERE `tr`.`term_taxonomy_id` IN ( '$termIds' )
					)
				)" )
				->run()
				->result();

			if ( ! empty( $lastModified[0]->last_modified ) ) {
				$index['lastmod'] = aioseo()->helpers->formatDateTime( $lastModified[0]->last_modified );
			}
			$indexes[] = $index;
		}
		return $indexes;
	}
}