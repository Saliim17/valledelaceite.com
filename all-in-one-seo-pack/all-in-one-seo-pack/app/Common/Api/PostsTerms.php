<?php
namespace AIOSEO\Plugin\Common\Api;

use AIOSEO\Plugin\Common\Models;

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class PostsTerms {
	/**
	 * Searches for posts or terms by ID/name.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function searchForObjects( $request ) {
		$body = $request->get_json_params();

		if ( empty( $body['query'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No search term was provided.'
			], 400 );
		}
		if ( empty( $body['type'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No type was provided.'
			], 400 );
		}

		$objects = [];
		$options = aioseo()->options->noConflict();
		if ( 'posts' === $body['type'] ) {

			$postTypes = aioseo()->helpers->getPublicPostTypes( true );
			foreach ( $postTypes as $postType ) {
				// Check if post type isn't noindexed.
				if ( $options->searchAppearance->dynamic->postTypes->has( $postType ) && ! $options->searchAppearance->dynamic->postTypes->$postType->show ) {
					$postTypes = aioseo()->helpers->unsetValue( $postTypes, $postType );
				}
			}

			$objects = get_posts( [
				's'           => $body['query'],
				'numberposts' => 20,
				'post_status' => [ 'publish', 'draft' ],
				'post_type'   => $postTypes,
				'orderby'     => 'post_title'
			] );
		} elseif ( 'terms' === $body['type'] ) {

			$taxonomies = aioseo()->helpers->getPublicTaxonomies( true );
			foreach ( $taxonomies as $taxonomy ) {
				// Check if taxonomy isn't noindexed.
				if ( $options->searchAppearance->dynamic->taxonomies->has( $taxonomy ) && ! $options->searchAppearance->dynamic->taxonomies->$taxonomy->show ) {
					$taxonomies = aioseo()->helpers->unsetValue( $taxonomies, $taxonomy );
				}
			}

			$objects = get_terms( [
				'name__like' => $body['query'],
				'number'     => 20,
				'taxonomy'   => $taxonomies,
				'orderby'    => 'name'
			] );
		}

		if ( empty( $objects ) ) {
			return new \WP_REST_Response( [
				'success' => true,
				'objects' => []
			], 200 );
		}

		$parsed = [];
		foreach ( $objects as $object ) {
			if ( 'posts' === $body['type'] ) {
				$parsed[] = [
					'type'  => $object->post_type,
					'value' => $object->ID,
					'label' => $object->post_title,
					'link'  => get_permalink( $object->ID )
				];
			} elseif ( 'terms' === $body['type'] ) {
				$parsed[] = [
					'type'  => $object->taxonomy,
					'value' => $object->term_id,
					'label' => $object->name,
					'link'  => get_term_link( $object->term_id )
				];
			}
		}

		return new \WP_REST_Response( [
			'success' => true,
			'objects' => $parsed
		], 200 );
	}

	/**
	 * Get post data for fetch requests
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function getPostData( $request ) {
		$args = $request->get_query_params();

		if ( empty( $args['postId'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'No post ID was provided.', 'all-in-one-seo-pack' )
			], 400 );
		}

		$thePost = aioseo()->db
			->start( 'aioseo_posts' )
			->where( 'post_id', $args['postId'] )
			->run()
			->model( 'AIOSEO\\Plugin\\Common\\Models\\Post' );

		return new \WP_REST_Response( [
			'success'  => true,
			'post'     => $thePost,
			'postData' => [
				'parsedTitle'       => aioseo()->tags->replaceTags( $thePost->title, $args['postId'] ),
				'parsedDescription' => aioseo()->tags->replaceTags( $thePost->description, $args['postId'] ),
				'content'           => aioseo()->helpers->doShortcodes( aioseo()->helpers->getAnalysisContent( $args['postId'] ) ),
				'slug'              => get_post_field( 'post_name', $args['postId'] )
			]
		], 200 );
	}

	/**
	 * Update post settings.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function updatePosts( $request ) {
		$body   = $request->get_json_params();
		$postId = ! empty( $body['id'] ) ? intval( $body['id'] ) : null;

		if ( ! $postId ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Post ID is missing.', 'all-in-one-seo-pack' )
			], 400 );
		}

		$body['id']                  = $postId;
		$body['title']               = ! empty( $body['title'] ) ? sanitize_text_field( $body['title'] ) : null;
		$body['description']         = ! empty( $body['description'] ) ? sanitize_text_field( $body['description'] ) : null;
		$body['keywords']            = ! empty( $body['keywords'] ) ? sanitize_text_field( $body['keywords'] ) : null;
		$body['og_title']            = ! empty( $body['og_title'] ) ? sanitize_text_field( $body['og_title'] ) : null;
		$body['og_description']      = ! empty( $body['og_description'] ) ? sanitize_text_field( $body['og_description'] ) : null;
		$body['og_article_section']  = ! empty( $body['og_article_section'] ) ? sanitize_text_field( $body['og_article_section'] ) : null;
		$body['og_article_tags']     = ! empty( $body['og_article_tags'] ) ? sanitize_text_field( $body['og_article_tags'] ) : null;
		$body['twitter_title']       = ! empty( $body['twitter_title'] ) ? sanitize_text_field( $body['twitter_title'] ) : null;
		$body['twitter_description'] = ! empty( $body['twitter_description'] ) ? sanitize_text_field( $body['twitter_description'] ) : null;

		$saveStatus = Models\Post::savePost( $postId, $body );

		if ( ! empty( $saveStatus ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed update query: ' . $saveStatus
			], 401 );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'posts'   => $postId
		], 200 );
	}

	/**
	 * Update post settings from Post screen.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function updatePostFromScreen( $request ) {
		$body    = $request->get_json_params();
		$postId  = ! empty( $body['postId'] ) ? intval( $body['postId'] ) : null;
		$isMedia = isset( $body['isMedia'] ) ? true : false;
		$post    = aioseo()->helpers->getPost( $postId );

		if ( ! $postId ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Post ID is missing.'
			], 400 );
		}

		$thePost = aioseo()->db
			->start( 'aioseo_posts' )
			->where( 'post_id', $postId )
			->run()
			->model( 'AIOSEO\\Plugin\\Common\\Models\\Post' );

		if ( $thePost->exists() ) {
			$metaTitle = aioseo()->meta->title->getPostTypeTitle( $post->post_type );
			if ( empty( $thePost->title ) && ! empty( $body['title'] ) && trim( $body['title'] ) === trim( $metaTitle ) ) {
				$body['title'] = null;
			}
			$thePost->title = ! empty( $body['title'] ) ? sanitize_text_field( $body['title'] ) : null;

			$metaDescription = aioseo()->meta->description->getPostTypeDescription( $post->post_type );
			if ( empty( $thePost->description ) && ! empty( $body['description'] ) && trim( $body['description'] ) === trim( $metaDescription ) ) {
				$body['description'] = null;
			}
			$thePost->description = ! empty( $body['description'] ) ? sanitize_text_field( $body['description'] ) : '';
			$thePost->updated     = gmdate( 'Y-m-d H:i:s' );
			if ( $isMedia ) {
				wp_update_post(
					[
						'ID'         => $postId,
						'post_title' => sanitize_text_field( $body['imageTitle'] ),
					]
				);
				update_post_meta( $postId, '_wp_attachment_image_alt', sanitize_text_field( $body['imageAltTag'] ) );
			}
		} else {
			$thePost->post_id     = $postId;
			$thePost->title       = ! empty( $body['title'] ) ? sanitize_text_field( $body['title'] ) : '';
			$thePost->description = ! empty( $body['description'] ) ? sanitize_text_field( $body['description'] ) : null;
			$thePost->created     = gmdate( 'Y-m-d H:i:s' );
			$thePost->updated     = gmdate( 'Y-m-d H:i:s' );
			if ( $isMedia ) {
				wp_update_post(
					[
						'ID'         => $postId,
						'post_title' => sanitize_text_field( $body['imageTitle'] ),
					]
				);
				update_post_meta( $postId, '_wp_attachment_image_alt', sanitize_text_field( $body['imageAltTag'] ) );
			}
		}
		$thePost->save();

		$lastError = aioseo()->db->lastError();
		if ( ! empty( $lastError ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed update query: ' . $lastError
			], 401 );
		}

		return new \WP_REST_Response( [
			'success'     => true,
			'posts'       => $postId,
			'title'       => aioseo()->meta->title->getPostTitle( $postId ),
			'description' => aioseo()->meta->description->getPostDescription( $postId )
		], 200 );
	}

	/**
	 * Update post keyphrases.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function updatePostKeyphrases( $request ) {
		$body   = $request->get_json_params();
		$postId = ! empty( $body['postId'] ) ? intval( $body['postId'] ) : null;

		if ( ! $postId ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Post ID is missing.'
			], 400 );
		}

		$thePost = aioseo()->db
			->start( 'aioseo_posts' )
			->where( 'post_id', $postId )
			->run()
			->model( 'AIOSEO\\Plugin\\Common\\Models\\Post' );

		$thePost->post_id = $postId;
		if ( ! empty( $body['keyphrases'] ) ) {
			$thePost->keyphrases = wp_json_encode( $body['keyphrases'] );
		}
		if ( ! empty( $body['page_analysis'] ) ) {
			$thePost->page_analysis = wp_json_encode( $body['page_analysis'] );
		}
		if ( ! empty( $body['seo_score'] ) ) {
			$thePost->seo_score = sanitize_text_field( $body['seo_score'] );
		}
		$thePost->updated = gmdate( 'Y-m-d H:i:s' );
		$thePost->save();

		$lastError = aioseo()->db->lastError();
		if ( ! empty( $lastError ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed update query: ' . $lastError
			], 401 );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'post'    => $postId
		], 200 );
	}
}