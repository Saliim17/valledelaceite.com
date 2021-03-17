<?php
namespace AIOSEO\Plugin\Common\Social;

use AIOSEO\Plugin\Common\Models;

/**
 * Handles the Open Graph and Twitter Image.
 *
 * @since 4.0.0
 */
class Image {

	/**
	 * The default thumbnail size.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public $thumbnailSize;

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->thumbnailSize = apply_filters( 'aioseo_thumbnail_size', apply_filters( 'post_thumbnail_size', 'large' ) );
	}

	/**
	 * Returns the Facebook or Twitter image.
	 *
	 * @since 4.0.0
	 *
	 * @param  string       $type        The type (Facebook or Twitter).
	 * @param  string       $imageSource The image source.
	 * @param  WP_Post      $post        The post object.
	 * @return string|array              The image data.
	 */
	public function getImage( $type, $imageSource, $post ) {
		static $images = [];
		if ( isset( $images[ $type ] ) ) {
			return $images[ $type ];
		}

		switch ( $imageSource ) {
			case 'featured':
				$images[ $type ] = $this->getFeaturedImage( $post );
				$image           = $images[ $type ];
				break;
			case 'attach':
				$images[ $type ] = $this->getFirstAttachedImage( $post );
				$image           = $images[ $type ];
				break;
			case 'content':
				$image = $this->getFirstImageInContent( $post );
				break;
			case 'author':
				$image = $this->getAuthorAvatar( $post );
				break;
			case 'auto':
				$image = $this->getFirstAvailableImage( $post, $type );
				break;
			case 'custom':
				$image = $this->getCustomFieldImage( $post, $type );
				break;
			case 'custom_image':
				$metaData = aioseo()->meta->metaData->getMetaData( $post );
				if ( empty( $metaData ) ) {
					break;
				}
				$image = ( 'facebook' === lcfirst( $type ) ) ? $metaData->og_image_custom_url : $metaData->twitter_image_custom_url;
				break;
			case 'default':
			default:
				$image = aioseo()->options->social->$type->general->defaultImagePosts;
		}

		if ( empty( $image ) ) {
			$image = aioseo()->options->social->$type->general->defaultImagePosts;
		}

		if ( is_array( $image ) ) {
			$images[ $type ] = $image;
			return $images[ $type ];
		}

		$uploadDirUrl = aioseo()->helpers->escapeRegex( aioseo()->helpers->getWpContentUrl() );
		if ( ! preg_match( "/$uploadDirUrl.*/", $image ) ) {
			$images[ $type ] = $image;
			return $images[ $type ];
		}

		$attachmentId    = attachment_url_to_postid( aioseo()->helpers->removeImageDimensions( $image ) );
		$images[ $type ] = $attachmentId ? wp_get_attachment_image_src( $attachmentId, $this->thumbnailSize ) : $image;
		return $images[ $type ];
	}

	/**
	 * Returns the Featured Image for the post.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post $post The post object.
	 * @return array         The image data.
	 */
	public function getFeaturedImage( $post ) {
		$imageId = get_post_thumbnail_id( $post->ID );
		return $imageId ? wp_get_attachment_image_src( $imageId, $this->thumbnailSize ) : '';
	}

	/**
	 * Returns the first attached image.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post $post The post object.
	 * @return string        The image data.
	 */
	public function getFirstAttachedImage( $post ) {
		if ( 'attachment' === get_post_type( $post->ID ) ) {
			return wp_get_attachment_image_src( $post->ID, $this->thumbnailSize );
		}

		$attachments = get_children(
			[
				'post_parent'    => $post->ID,
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
			]
		);

		return $attachments && count( $attachments ) ? wp_get_attachment_image_src( array_values( $attachments )[0]->ID, $this->thumbnailSize ) : '';
	}

	/**
	 * Returns the first image found in the post content.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post $post  The post object.
	 * @return string         The image URL.
	 */
	public function getFirstImageInContent( $post ) {
		preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', $post->post_content, $matches );
		return ! empty( $matches[1][0] ) ? $matches[1][0] : '';
	}

	/**
	 * Returns the author avatar.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post $post The post object.
	 * @return string        The image URL.
	 */
	public function getAuthorAvatar( $post ) {
		$avatar = get_avatar( $post->post_author, 300 );
		preg_match( "/src='(.*?)'/i", $avatar, $matches );
		return ! empty( $matches[1] ) ? $matches[1] : '';
	}

	/**
	 * Returns the first available image.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post $post The post object.
	 * @param  string  $type The type of image (Facebook or Twitter).
	 * @return string  The image URL.
	 */
	public function getFirstAvailableImage( $post, $type ) {

		$image = $this->getCustomFieldImage( $post, $type );

		if ( ! $image ) {
			$image = $this->getFeaturedImage( $post );
		}

		if ( ! $image ) {
			$image = $this->getFirstAttachedImage( $post );
		}

		if ( ! $image ) {
			$image = $this->getFirstImageInContent( $post );
		}

		if ( ! $image && 'twitter' === lcfirst( $type ) ) {
			$image = aioseo()->options->social->twitter->homePage->image;
		}

		return $image ? $image : aioseo()->options->social->facebook->homePage->image;
	}

	/**
	 * Returns the image from a custom field.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post $post The post object.
	 * @param  string  $type The type of image (Facebook or Twitter).
	 * @return string        The image URL.
	 */
	public function getCustomFieldImage( $post, $type ) {
		$prefix = ( 'facebook' === lcfirst( $type ) ) ? 'og_' : 'twitter_';

		$aioseoPost   = Models\Post::getPost( $post->ID );
		$customFields = ! empty( $aioseoPost->{ $prefix . 'image_custom_fields' } )
			? $aioseoPost->{ $prefix . 'image_custom_fields' }
			: aioseo()->options->social->$type->general->customFieldImagePosts;

		if ( ! $customFields ) {
			return '';
		}

		$customFields = explode( ',', $customFields );
		foreach ( $customFields as $customField ) {
			$image = get_post_meta( $post->ID, $customField, true );

			if ( ! empty( $image ) ) {
				return $image;
			}
		}
		return '';
	}
}