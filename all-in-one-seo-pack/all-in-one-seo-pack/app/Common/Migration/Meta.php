<?php
namespace AIOSEO\Plugin\Common\Migration;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

use AIOSEO\Plugin\Common\Models;

/**
 * Migrates the post meta from V3.
 *
 * @since 4.0.0
 */
class Meta {
	/**
	 * Holds the old options array.
	 *
	 * @since 4.0.3
	 *
	 * @var array|null
	 */
	protected static $oldOptions = null;

	/**
	 * Migrates the plugin meta data.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function migrateMeta() {
		try {
			if ( as_next_scheduled_action( 'aioseo_migrate_post_meta' ) ) {
				return;
			}

			as_schedule_single_action( time(), 'aioseo_migrate_post_meta', [], 'aioseo' );
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * Migrates the post meta data from V3.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function migratePostMeta() {
		$postsPerAction  = 50;
		$publicPostTypes = implode( "', '", aioseo()->helpers->getPublicPostTypes( true ) );
		$timeStarted     = gmdate( 'Y-m-d H:i:s', get_transient( 'aioseo_v3_migration_in_progress_posts' ) );

		$postsToMigrate = aioseo()->db
			->start( 'posts' . ' as p' )
			->select( 'p.ID' )
			->leftJoin( 'aioseo_posts as ap', '`p`.`ID` = `ap`.`post_id`' )
			->whereRaw( "( ap.post_id IS NULL OR ap.updated < '$timeStarted' )" )
			->whereRaw( "( p.post_type IN ( '$publicPostTypes' ) )" )
			->orderBy( 'p.ID DESC' )
			->limit( $postsPerAction )
			->run()
			->result();

		if ( ! $postsToMigrate || ! count( $postsToMigrate ) ) {
			delete_transient( 'aioseo_v3_migration_in_progress_posts' );
			return;
		}

		foreach ( $postsToMigrate as $post ) {
			$newPostMeta = $this->getMigratedPostMeta( $post->ID );

			$aioseoPost = Models\Post::getPost( $post->ID );
			$aioseoPost->set( $newPostMeta );
			$aioseoPost->save();

			$this->migrateAdditionalPostMeta( $post->ID );
		}

		if ( count( $postsToMigrate ) === $postsPerAction ) {
			try {
				as_schedule_single_action( time() + 30, 'aioseo_migrate_post_meta', [], 'aioseo' );
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		} else {
			delete_transient( 'aioseo_v3_migration_in_progress_posts' );
		}
	}

	/**
	 * Returns the migrated post meta for a given post.
	 *
	 * @since 4.0.3
	 *
	 * @param  int   $postId The post ID.
	 * @return array $meta   The post meta.
	 */
	public function getMigratedPostMeta( $postId ) {
		if ( is_category() || is_tag() || is_tax() ) {
			return [];
		}

		if ( null === self::$oldOptions ) {
			self::$oldOptions = get_option( 'aioseop_options' );
		}

		if ( empty( self::$oldOptions ) ) {
			return [];
		}

		$post     = get_post( $postId );
		$postMeta = aioseo()->db
			->start( 'postmeta' . ' as pm' )
			->select( 'pm.meta_key, pm.meta_value' )
			->where( 'pm.post_id', $post->ID )
			->whereRaw( "`pm`.`meta_key` LIKE '_aioseop_%'" )
			->run()
			->result();

		$mappedMeta = [
			'_aioseop_title'              => 'title',
			'_aioseop_description'        => 'description',
			'_aioseop_custom_link'        => 'canonical_url',
			'_aioseop_sitemap_exclude'    => '',
			'_aioseop_disable'            => '',
			'_aioseop_noindex'            => 'robots_noindex',
			'_aioseop_nofollow'           => 'robots_nofollow',
			'_aioseop_sitemap_priority'   => 'priority',
			'_aioseop_sitemap_frequency'  => 'frequency',
			'_aioseop_keywords'           => 'keywords',
			'_aioseop_opengraph_settings' => ''
		];

		$meta = [
			'post_id' => $post->ID,
		];

		if ( ! $postMeta || ! count( $postMeta ) ) {
			return $meta;
		}

		foreach ( $postMeta as $record ) {
			$name  = $record->meta_key;
			$value = $record->meta_value;

			if ( ! in_array( $name, array_keys( $mappedMeta ), true ) ) {
				continue;
			}

			switch ( $name ) {
				case '_aioseop_description':
					$meta[ $mappedMeta[ $name ] ] = aioseo()->helpers->sanitizeOption( aioseo()->migration->helpers->macrosToSmartTags( $value ) );
					break;
				case '_aioseop_title':
					if ( ! empty( $value ) ) {
						$meta[ $mappedMeta[ $name ] ] = $this->getTitleValue( $post, $value );
					}
					break;
				case '_aioseop_sitemap_exclude':
					if ( empty( $value ) ) {
						break;
					}
					$this->migrateExcludedPost( $postId );
					break;
				case '_aioseop_disable':
					if ( empty( $value ) ) {
						break;
					}
					$this->migrateSitemapExcludedPost( $postId );
					break;
				case '_aioseop_noindex':
				case '_aioseop_nofollow':
					if ( 'on' === (string) $value ) {
						$meta['robots_default']       = false;
						$meta[ $mappedMeta[ $name ] ] = true;
					} elseif ( 'off' === (string) $value ) {
						$meta['robots_default'] = false;
					}
					break;
				case '_aioseop_keywords':
					$meta[ $mappedMeta[ $name ] ] = aioseo()->migration->helpers->oldKeywordsToNewKeywords( $value );
					break;
				case '_aioseop_opengraph_settings':
					$meta += $this->convertOpenGraphMeta( $value );
					break;
				case '_aioseop_sitemap_priority':
				case '_aioseop_sitemap_frequency':
					if ( empty( $value ) ) {
						$meta[ $mappedMeta[ $name ] ] = 'default';
						break;
					}
					$meta[ $mappedMeta[ $name ] ] = $value;
					break;
				default:
					$meta[ $mappedMeta[ $name ] ] = esc_html( wp_strip_all_tags( strval( $value ) ) );
					break;
			}
		}
		return $meta;
	}

	/**
	 * Migrates a given disabled post from V3.
	 *
	 * @since 4.0.3
	 *
	 * @param  int  $postId The post ID.
	 * @return void
	 */
	private function migrateExcludedPost( $postId ) {
		$post = get_post( $postId );
		if ( ! is_object( $post ) ) {
			return;
		}

		aioseo()->options->sitemap->general->advancedSettings->enable = true;
		$excludedPosts = aioseo()->options->sitemap->general->advancedSettings->excludePosts;

		foreach ( $excludedPosts as $excludedPost ) {
			$excludedPost = json_decode( $excludedPost );
			if ( $excludedPost->value === $postId ) {
				return;
			}
		}

		$excludedPost = [
			'value' => $post->ID,
			'type'  => $post->post_type,
			'label' => $post->post_title,
			'link'  => get_permalink( $post )
		];

		$excludedPosts[] = wp_json_encode( $excludedPost );
		aioseo()->options->sitemap->general->advancedSettings->excludePosts = $excludedPosts;

		$deprecatedOptions = aioseo()->internalOptions->internal->deprecatedOptions;
		if ( ! in_array( 'excludePosts', $deprecatedOptions, true ) ) {
			array_push( $deprecatedOptions, 'excludePosts' );
			aioseo()->internalOptions->internal->deprecatedOptions = $deprecatedOptions;
		}
	}

	/**
	 * Migrates a given sitemap excluded post from V3.
	 *
	 * @since 4.0.3
	 *
	 * @param  int  $postId The post ID.
	 * @return void
	 */
	private function migrateSitemapExcludedPost( $postId ) {
		$post = get_post( $postId );
		if ( ! is_object( $post ) ) {
			return;
		}

		$excludedPosts = aioseo()->options->deprecated->searchAppearance->advanced->excludePosts;
		foreach ( $excludedPosts as $excludedPost ) {
			$excludedPost = json_decode( $excludedPost );
			if ( $excludedPost->value === $postId ) {
				return;
			}
		}

		$excludedPost = [
			'value' => $post->ID,
			'type'  => $post->post_type,
			'label' => $post->post_title,
			'link'  => get_permalink( $post )
		];

		$excludedPosts[] = wp_json_encode( $excludedPost );
		aioseo()->options->deprecated->searchAppearance->advanced->excludePosts = $excludedPosts;
	}

	/**
	 * Migrates additional post meta data.
	 *
	 * @since 4.0.2
	 *
	 * @param  int  $postId The post ID.
	 * @return void
	 */
	protected function migrateAdditionalPostMeta( $postId ) {
		return $postId;
	}

	/**
	 * Maps the old Open Graph meta to the social meta columns in V4.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $ogMeta The old V3 Open Graph meta.
	 * @return array $meta   The mapped meta.
	 */
	public function convertOpenGraphMeta( $ogMeta ) {
		$ogMeta = maybe_unserialize( $ogMeta );

		if ( ! is_array( $ogMeta ) ) {
			return [];
		}

		$mappedSocialMeta = [
			'aioseop_opengraph_settings_title'             => 'og_title',
			'aioseop_opengraph_settings_desc'              => 'og_description',
			'aioseop_opengraph_settings_image'             => 'og_image_custom_url',
			'aioseop_opengraph_settings_imagewidth'        => 'og_image_width',
			'aioseop_opengraph_settings_imageheight'       => 'og_image_height',
			'aioseop_opengraph_settings_video'             => 'og_video',
			'aioseop_opengraph_settings_videowidth'        => 'og_video_width',
			'aioseop_opengraph_settings_videoheight'       => 'og_video_height',
			'aioseop_opengraph_settings_category'          => 'og_object_type',
			'aioseop_opengraph_settings_section'           => 'og_article_section',
			'aioseop_opengraph_settings_tag'               => 'og_article_tags',
			'aioseop_opengraph_settings_setcard'           => 'twitter_card',
			'aioseop_opengraph_settings_customimg_twitter' => 'twitter_image_custom_url',
		];

		$meta = [];
		foreach ( $ogMeta as $name => $value ) {
			if ( ! in_array( $name, array_keys( $mappedSocialMeta ), true ) ) {
				continue;
			}

			switch ( $name ) {
				case 'aioseop_opengraph_settings_desc':
				case 'aioseop_opengraph_settings_title':
					$meta[ $mappedSocialMeta[ $name ] ] = aioseo()->helpers->sanitizeOption( aioseo()->migration->helpers->macrosToSmartTags( $value ) );
					break;
				case 'aioseop_opengraph_settings_image':
					$value = strval( $value );
					if ( empty( $value ) ) {
						break;
					}

					$meta['og_image_type']              = 'custom_image';
					$meta[ $mappedSocialMeta[ $name ] ] = strval( $value );
					break;
				case 'aioseop_opengraph_settings_video':
					$meta[ $mappedSocialMeta[ $name ] ] = esc_url( $value );
					break;
				case 'aioseop_opengraph_settings_customimg_twitter':
					$value = strval( $value );
					if ( empty( $value ) ) {
						break;
					}
					$meta['twitter_image_type']         = 'custom_image';
					$meta['twitter_use_og']             = false;
					$meta[ $mappedSocialMeta[ $name ] ] = strval( $value );
					break;
				case 'aioseop_opengraph_settings_imagewidth':
				case 'aioseop_opengraph_settings_imageheight':
				case 'aioseop_opengraph_settings_videowidth':
				case 'aioseop_opengraph_settings_videoheight':
					$value = intval( $value );
					if ( ! $value || $value <= 0 ) {
						break;
					}
					$meta[ $mappedSocialMeta[ $name ] ] = $value;
					break;
				case 'aioseop_opengraph_settings_tag':
					$meta[ $mappedSocialMeta[ $name ] ] = aioseo()->migration->helpers->oldKeywordsToNewKeywords( $value );
					break;
				default:
					$meta[ $mappedSocialMeta[ $name ] ] = esc_html( strval( $value ) );
					break;
			}
		}

		return $meta;
	}

	/**
	 * Returns the title as it was in V3.
	 *
	 * @since 4.0.0
	 *
	 * @param  int    $post     The post object.
	 * @param  string $seoTitle The old SEO title.
	 * @return string           The title.
	 */
	protected function getTitleValue( $post, $seoTitle = '' ) {
		if ( ! is_object( $post ) ) {
			return '';
		}

		$titleFormat = '#post_title #separator_sa #site_title';
		if ( aioseo()->options->searchAppearance->dynamic->postTypes->has( $post->post_type ) ) {
			$titleFormat = aioseo()->options->searchAppearance->dynamic->postTypes->{$post->post_type}->title;
		}

		$seoTitle = preg_replace( '/(#post_title)/', $seoTitle, $titleFormat );
		return aioseo()->helpers->sanitizeOption( aioseo()->migration->helpers->macrosToSmartTags( $seoTitle ) );
	}
}