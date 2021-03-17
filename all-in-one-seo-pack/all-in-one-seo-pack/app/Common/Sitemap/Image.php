<?php
namespace AIOSEO\Plugin\Common\Sitemap;

/**
 * Determines which images are included in a post/term.
 *
 * @since 4.0.0
 */
class Image {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.5
	 */
	public function __construct() {
		// Column may not have been created yet.
		if ( ! aioseo()->db->columnExists( 'aioseo_posts', 'image_scan_date' ) ) {
			return;
		}

		add_action( 'aioseo_image_sitemap_scan', [ $this, 'scanPosts' ] );

		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		add_action( 'wp_insert_post', [ $this, 'scanPostManual' ], 10, 2 );

		// Action Scheduler hooks.
		add_filter( 'init', [ $this, 'scheduleScan' ], 3001 );
	}

	/**
	 * Schedules the image sitemap scan.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function scheduleScan() {
		try {
			if ( ! as_next_scheduled_action( 'aioseo_image_sitemap_scan' ) ) {
				as_schedule_single_action( time() + 10, 'aioseo_image_sitemap_scan', [], 'aioseo' );
			}
		} catch ( \RuntimeException $e ) {
			// Do nothing.
		}
	}

	/**
	 * Triggers a manual scan of a post.
	 *
	 * @since 4.0.5
	 *
	 * @param  int          $postId The post ID.
	 * @param  WP_Post|null $post   The post object.
	 * @return void
	 */
	public function scanPostManual( $postId, $post = null ) {
		if ( ! aioseo()->helpers->isValidPost( $post ) ) {
			return;
		}

		static $isScanned = false;
		if ( $isScanned ) {
			return;
		}

		$isScanned = true;
		$this->scanPost( $postId );
	}

	/**
	 * Scans posts for images.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function scanPosts() {
		$postsPerScan = apply_filters( 'aioseo_image_sitemap_posts_per_scan', 50 );
		$postTypes    = implode( "', '", aioseo()->helpers->getPublicPostTypes( true ) );

		$posts = aioseo()->db
			->start( aioseo()->db->db->posts . ' as p', true )
			->select( '`p`.`ID`, `p`.`post_type`, `p`.`post_content`, `p`.`post_excerpt`, `p`.`post_modified_gmt`' )
			->leftJoin( 'aioseo_posts as ap', '`ap`.`post_id` = `p`.`ID`' )
			->whereRaw( '( `ap`.`id` IS NULL OR `p`.`post_modified_gmt` > `ap`.`video_scan_date` OR `ap`.`image_scan_date` IS NULL )' )
			->where( 'p.post_status', 'publish' )
			->whereRaw( "`p`.`post_type` IN ( '$postTypes' )" )
			->limit( $postsPerScan )
			->run()
			->result();

		if ( ! $posts ) {
			return;
		}

		foreach ( $posts as $post ) {
			$this->scanPost( $post );
		}
	}

	/**
	 * Returns the image entries for a given post.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post|int $post The post object or ID.
	 * @return array             The image entries.
	 */
	public function scanPost( $post ) {
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		if ( ! empty( $post->post_password ) ) {
			return $this->updatePost( $post->ID );
		}

		if ( 'attachment' === $post->post_type ) {
			if ( ! wp_attachment_is( 'image', $post ) ) {
				return $this->updatePost( $post->ID );
			}
			$image = $this->buildEntries( [ $post->ID ] );
			return $this->updatePost( $post->ID, $image );
		}

		$postContent = $this->doShortcodes( $post->post_content );
		// Trim both internal and external whitespace.
		$postContent = preg_replace( '/\s\s+/u', ' ', trim( $postContent ) );

		$urls = $this->extract( $postContent );

		if ( has_post_thumbnail( $post ) ) {
			$urls[] = get_the_post_thumbnail_url( $post );
		}

		$urls = $this->filter( $urls );
		$urls = $this->removeImageDimensions( $urls );

		if ( ! $urls ) {
			return $this->updatePost( $post->ID );
		}

		$ids = [];
		foreach ( $urls as $url ) {
			// Get the ID of the image so we can get its meta data. If there's no ID, then it's probably an external image.
			$id = attachment_url_to_postid( $url );
			if ( $id ) {
				$ids[] = $id;
				continue;
			}
			$ids[] = $url;
		}

		$images = array_slice( $this->buildEntries( $ids ), 0, 1000 );
		return $this->updatePost( $post->ID, $images );
	}

	/**
	 * Returns the image entries for a given term.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Term $term The term object.
	 * @return array         The image entries.
	 */
	public function term( $term ) {
		if ( aioseo()->options->sitemap->general->advancedSettings->excludeImages ) {
			return [];
		}

		$id = get_term_meta( $term->term_id, 'thumbnail_id', true );
		if ( ! $id ) {
			return [];
		}
		return $this->buildEntries( [ $id ] );
	}

	/**
	 * Builds the image entries.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $ids Either a numeric post ID or the image URL if the ID of the image couldn't be found.
	 * @return array      The image entries.
	 */
	private function buildEntries( $ids ) {
		$entries = [];
		foreach ( $ids as $id ) {
			if ( ! is_numeric( $id ) ) {
				$entries[] = [ 'image:loc' => aioseo()->sitemap->helpers->formatUrl( $id ) ];
				continue;
			}

			$entries[] = [
				'image:loc'     => aioseo()->sitemap->helpers->formatUrl( wp_get_attachment_url( $id ) ),
				'image:title'   => get_the_title( $id ),
				'image:caption' => wp_get_attachment_caption( $id )
			];
		}
		return $entries;
	}

	/**
	 * Extracts all image URls from the post content.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $content The post content.
	 * @return array           The image URLs.
	 */
	private function extract( $content ) {
		preg_match_all( '#<img[^>]+src="([^">]+)"#', $content, $matches );
		if ( ! $matches[1] ) {
			return [];
		}

		$urls = [];
		foreach ( $matches[1] as $url ) {
			$urls[] = aioseo()->helpers->makeUrlAbsolute( $url );
		}
		return array_unique( $urls );
	}

	/**
	 * Removes all URLs that aren't on our domain whitelist.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $urls The image URLs.
	 * @return array       The remaining image URLs.
	 */
	private function filter( $urls ) {
		$allowedDomains = apply_filters( 'aioseo_sitemap_image_domains', [
			aioseo()->helpers->localizedUrl( '/' ),
			'wp.com'
		] );

		if ( ! count( $allowedDomains ) ) {
			return [];
		}

		$remainingUrls = [];
		foreach ( $urls as $url ) {
			foreach ( $allowedDomains as $domain ) {
				if ( preg_match( "#.*$domain.*#", $url ) ) {
					$remainingUrls[] = $url;
					continue;
				}
			}
		}
		return $remainingUrls;
	}

	/**
	 * Removes image dimensions from the slug.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $urls         The image URLs.
	 * @return array $preparedUrls The formatted image URLs.
	 */
	private function removeImageDimensions( $urls ) {
		$preparedUrls = [];
		foreach ( $urls as $url ) {
			$preparedUrls[] = aioseo()->helpers->removeImageDimensions( $url );
		}
		return array_filter( $preparedUrls );
	}

	/**
	 * Runs all allowed shortcodes so that we can extract images from embedded galleries.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $content The post content.
	 * @return string          The parsed post content.
	 */
	private function doShortcodes( $content ) {
		$shortcodes = apply_filters( 'aioseo_sitemap_image_galleries', [
			'WordPress Core' => 'gallery',
			'NextGen #1'     => 'ngg',
			'NextGen #2'     => 'ngg_images'
		] );

		if ( ! count( $shortcodes ) ) {
			return $content;
		}

		$matches = [];
		foreach ( $shortcodes as $k => $v ) {
			preg_match_all( "#\[.*$v.*]#", $content, $found );
			$matches = $matches + $found;
		}

		if ( count( $matches ) ) {
			$content = do_shortcode( $content );
		}
		return $content;
	}

	/**
	 * Stores the image data for a given post in our DB table.
	 *
	 * @since 4.0.5
	 *
	 * @param  int   $postId The post ID.
	 * @param  array $images The images.
	 * @return void
	 */
	private function updatePost( $postId, $images = [] ) {
		$post                    = \AIOSEO\Plugin\Common\Models\Post::getPost( $postId );
		$meta                    = $post->exists() ? [] : aioseo()->migration->meta->getMigratedPostMeta( $postId );
		$meta['post_id']         = $postId;
		$meta['images']          = ! empty( $images ) ? $images : null;
		$meta['image_scan_date'] = gmdate( 'Y-m-d H:i:s' );

		$post->set( $meta );
		$post->save();
	}
}