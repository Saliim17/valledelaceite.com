<?php
namespace AIOSEO\Plugin\Common\Social;

/**
 * Handles the Open Graph meta.
 *
 * @since 4.0.0
 */
class Facebook {

	/**
	 * Returns the Open Graph image URL.
	 *
	 * @since 4.0.0
	 *
	 * @return string The image URL.
	 */
	public function getImage() {
		$post = aioseo()->helpers->getPost();
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$image = aioseo()->options->social->facebook->homePage->image;
			if ( empty( $image ) ) {
				$image = aioseo()->social->image->getImage( 'facebook', aioseo()->options->social->facebook->general->defaultImageSourcePosts, $post );
			}

			return $image;
		}

		$metaData = aioseo()->meta->metaData->getMetaData( $post );

		$image = '';
		if ( ! empty( $metaData ) ) {
			$imageSource = ! empty( $metaData->og_image_type ) && 'default' !== $metaData->og_image_type
				? $metaData->og_image_type
				: aioseo()->options->social->facebook->general->defaultImageSourcePosts;

			$image = aioseo()->social->image->getImage( 'facebook', $imageSource, $post );
		}

		if ( ! $image ) {
			$image = aioseo()->helpers->getSiteLogoUrl();
		}

		// Allow users to control the default image per post type.
		return apply_filters(
			'aioseo_opengraph_default_image',
			$image,
			[
				$post,
				$this->getObjectType()
			]
		);
	}

	/**
	 * Returns the width of the Open Graph image.
	 *
	 * @since 4.0.0
	 *
	 * @return string The image width.
	 */
	public function getImageWidth() {
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$width = aioseo()->options->social->facebook->homePage->imageWidth;
			return $width ? $width : aioseo()->options->social->facebook->general->defaultImagePostsWidth;
		}

		$metaData = aioseo()->meta->metaData->getMetaData();
		if ( ! empty( $metaData->og_custom_image_width ) ) {
			return $metaData->og_custom_image_width;
		}

		$image = $this->getImage();
		if ( is_array( $image ) ) {
			return $image[1];
		}

		return aioseo()->options->social->facebook->general->defaultImagePostsWidth;
	}

	/**
	 * Returns the height of the Open Graph image.
	 *
	 * @since 4.0.0
	 *
	 * @return string The image height.
	 */
	public function getImageHeight() {
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$height = aioseo()->options->social->facebook->homePage->imageHeight;
			return $height ? $height : aioseo()->options->social->facebook->general->defaultImagePostsHeight;
		}

		$metaData = aioseo()->meta->metaData->getMetaData();
		if ( ! empty( $metaData->og_custom_image_height ) ) {
			return $metaData->og_custom_image_height;
		}

		$image = $this->getImage();
		if ( is_array( $image ) ) {
			return $image[2];
		}
		return aioseo()->options->social->facebook->general->defaultImagePostsHeight;
	}

	/**
	 * Returns the Open Graph video URL.
	 *
	 * @since 4.0.0
	 *
	 * @return string The video URL.
	 */
	public function getVideo() {
		$metaData = aioseo()->meta->metaData->getMetaData();
		return ! empty( $metaData->og_video ) ? $metaData->og_video : '';
	}

	/**
	 * Returns the width of the video.
	 *
	 * @since 4.0.0
	 *
	 * @return string The video width.
	 */
	public function getVideoWidth() {
		$metaData = aioseo()->meta->metaData->getMetaData();
		return ! empty( $metaData->og_video_width ) ? $metaData->og_video_width : '';
	}

	/**
	 * Returns the height of the video.
	 *
	 * @since 4.0.0
	 *
	 * @return string The video height.
	 */
	public function getVideoHeight() {
		$metaData = aioseo()->meta->metaData->getMetaData();
		return ! empty( $metaData->og_video_height ) ? $metaData->og_video_height : '';
	}

	/**
	 * Returns the site name.
	 *
	 * @since 4.0.0
	 *
	 * @return string The site name.
	 */
	public function getSiteName() {
		$title = aioseo()->helpers->decodeHtmlEntities( aioseo()->tags->replaceTags( aioseo()->options->social->facebook->general->siteName, get_the_ID() ) );
		if ( ! $title ) {
			$title = aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) );
		}
		return wp_strip_all_tags( $title );
	}

	/**
	 * Returns the Open Graph object type.
	 *
	 * @since 4.0.0
	 *
	 * @return string The object type.
	 */
	public function getObjectType() {
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$type = aioseo()->options->social->facebook->homePage->objectType;
			return $type ? $type : 'website';
		}

		$post     = aioseo()->helpers->getPost();
		$metaData = aioseo()->meta->metaData->getMetaData( $post );
		if ( ! empty( $metaData->og_object_type ) && 'default' !== $metaData->og_object_type ) {
			return $metaData->og_object_type;
		}

		$postType          = get_post_type();
		$options           = aioseo()->options->noConflict();
		$defaultObjectType = $options->social->facebook->general->dynamic->postTypes->has( $postType )
			? $options->social->facebook->general->dynamic->postTypes->$postType->objectType
			: '';

		return ! empty( $defaultObjectType ) ? $defaultObjectType : 'article';
	}

	/**
	 * Returns the Open Graph title for the current page.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post|integer $post The post object or ID (optional).
	 * @return string                The Open Graph title.
	 */
	public function getTitle( $post = null ) {
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$title = aioseo()->meta->title->prepareTitle( aioseo()->options->social->facebook->homePage->title );
			return $title ? $title : aioseo()->meta->title->getTitle();
		}

		$post     = aioseo()->helpers->getPost( $post );
		$metaData = aioseo()->meta->metaData->getMetaData( $post );

		$title = '';
		if ( ! empty( $metaData->og_title ) ) {
			$title = aioseo()->meta->title->prepareTitle( $metaData->og_title );
		}
		return $title ? $title : aioseo()->meta->title->getPostTitle( $post );
	}

	/**
	 * Returns the Open Graph description.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post|integer $post The post object or ID (optional).
	 * @return string                The Open Graph description.
	 */
	public function getDescription( $post = null ) {
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$description = aioseo()->meta->description->prepareDescription( aioseo()->options->social->facebook->homePage->description );
			return $description ? $description : aioseo()->meta->description->getDescription();
		}

		$post     = aioseo()->helpers->getPost( $post );
		$metaData = aioseo()->meta->metaData->getMetaData( $post );

		$description = '';
		if ( ! empty( $metaData->og_description ) ) {
			$description = aioseo()->meta->description->prepareDescription( $metaData->og_description );
		}
		return $description ? $description : aioseo()->meta->description->getPostDescription( $post );
	}

	/**
	 * Returns the Open Graph article section name.
	 *
	 * @since 4.0.0
	 *
	 * @return string The article section name.
	 */
	public function getSection() {
		$metaData = aioseo()->meta->metaData->getMetaData();
		return ! empty( $metaData->og_article_section ) ? $metaData->og_article_section : '';
	}

	/**
	 * Returns the Open Graph publisher URL.
	 *
	 * @since 4.0.0
	 *
	 * @return string The Open Graph publisher URL.
	 */
	public function getPublisher() {
		if ( ! aioseo()->options->social->profiles->sameUsername->enable ) {
			return aioseo()->options->social->profiles->urls->facebookPageUrl;
		}

		$username = aioseo()->options->social->profiles->sameUsername->username;
		return ( $username && in_array( 'facebookPageUrl', aioseo()->options->social->profiles->sameUsername->included, true ) )
			? 'https://facebook.com/' . $username
			: '';
	}

	/**
	 * Returns the published time.
	 *
	 * @since 4.0.0
	 *
	 * @return string The published time.
	 */
	public function getPublishedTime() {
		$post = aioseo()->helpers->getPost();
		return $post ? gmdate( 'Y-m-d\TH:i:s\Z', mysql2date( 'U', aioseo()->helpers->getPost()->post_date_gmt ) ) : '';
	}

	/**
	 * Returns the last modified time.
	 *
	 * @since 4.0.0
	 *
	 * @return string The last modified time.
	 */
	public function getModifiedTime() {
		$post = aioseo()->helpers->getPost();
		return $post ? gmdate( 'Y-m-d\TH:i:s\Z', mysql2date( 'U', aioseo()->helpers->getPost()->post_modified_gmt ) ) : '';
	}


	/**
	 * Returns the Open Graph author.
	 *
	 * @since 4.0.0
	 *
	 * @return string The Open Graph author.
	 */
	public function getAuthor() {
		$post = aioseo()->helpers->getPost();
		if ( ! $post || ! aioseo()->options->social->facebook->general->showAuthor ) {
			return '';
		}

		$postAuthor = get_the_author_meta( 'aioseo_facebook', $post->post_author );
		return ! empty( $postAuthor ) ? $postAuthor : aioseo()->options->social->facebook->advanced->authorUrl;
	}

	/**
	 * Returns the Open Graph article tags.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function getArticleTags() {
		$post     = aioseo()->helpers->getPost();
		$metaData = aioseo()->meta->metaData->getMetaData( $post );
		$tags     = ! empty( $metaData->og_article_tags ) ? aioseo()->meta->keywords->extractMetaKeywords( $metaData->og_article_tags ) : [];

		if (
			$post &&
			aioseo()->options->social->facebook->advanced->enable &&
			aioseo()->options->social->facebook->advanced->generateArticleTags
		) {
			if ( aioseo()->options->social->facebook->advanced->useKeywordsInTags ) {
				$keywords = aioseo()->meta->keywords->getKeywords();
				$keywords = aioseo()->tags->parseCustomFields( $keywords );
				$keywords = aioseo()->meta->keywords->keywordStringToList( $keywords );
				$tags     = array_merge( $tags, $keywords );
			}

			if ( aioseo()->options->social->facebook->advanced->useCategoriesInTags ) {
				$tags = array_merge( $tags, aioseo()->social->helpers->getAllCategories( $post->ID ) );
			}

			if ( aioseo()->options->social->facebook->advanced->usePostTagsInTags ) {
				$tags = array_merge( $tags, aioseo()->social->helpers->getAllTags( $post->ID ) );
			}
		}

		return aioseo()->meta->keywords->getUniqueKeywords( $tags, false );
	}
}