<?php
namespace AIOSEO\Plugin\Common\Models;

/**
 * The Post DB Model.
 *
 * @since 4.0.0
 */
class Post extends Model {
	/**
	 * The name of the table in the database, without the prefix.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $table = 'aioseo_posts';

	/**
	 * Fields that should be json encoded on save and decoded on get.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $jsonFields = [ 'images', 'videos' ];

	/**
	 * Fields that should be hidden when serialized.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $hidden = [ 'id' ];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 *
	 * @param mixed $var This can be the primary key of the resource, or it could be an array of data to manufacture a resource without a database query.
	 */
	public function __construct( $var = null ) {
		parent::__construct( $var );

		// Duplicate Post integration.
		add_action( 'dp_duplicate_post', [ $this, 'duplicatePostIntegration' ], 10, 3 );
	}

	/**
	 * Duplicates the model when duplicate post is triggered.
	 *
	 * @since 4.0.0
	 *
	 * @param  integer $newPostId    The new Post ID.
	 * @param  WP_Post $originalPost The original post object.
	 * @param  string  $status       The status of the post.
	 * @return void
	 */
	public function duplicatePostIntegration( $newPostId, $originalPost, $status ) {
		$originalAioseoPost = self::getPost( $originalPost->ID );
		if ( ! $originalAioseoPost->exists() ) {
			return;
		}

		$newPost = self::getPost( $newPostId );
		if ( $newPost->exists() ) {
			return;
		}

		$columns = $originalAioseoPost->getColumns();
		foreach ( $columns as $column => $value ) {
			// Skip the ID columns.
			if ( 'id' === $column || 'post_id' === $column ) {
				continue;
			}

			if ( 'post_id' === $column ) {
				$newPost->$column = $newPostId;
				continue;
			}

			$newPost->$column = $originalAioseoPost->$column;
		}

		$newPost->save();
	}

	/**
	 * Returns a Post with a given ID.
	 *
	 * @since 4.0.0
	 *
	 * @param  int  $postId The Post ID.
	 * @return Post         The Post object.
	 */
	public static function getPost( $postId ) {
		return aioseo()->db
			->start( 'aioseo_posts' )
			->where( 'post_id', $postId )
			->run()
			->model( 'AIOSEO\\Plugin\\Common\\Models\\Post' );
	}

	/**
	 * Saves Post AIOSEO settings.
	 *
	 * @since 4.0.3
	 *
	 * @param  int   $postId         The Post ID.
	 * @param  array $data           The post data to save.
	 * @return bool|WP_REST_Response True if post saved or Response Error.
	 */
	public static function savePost( $postId, $data ) {
		$thePost = aioseo()->db
			->start( 'aioseo_posts' )
			->where( 'post_id', $postId )
			->run()
			->model( 'AIOSEO\\Plugin\\Common\\Models\\Post' );

		$post = aioseo()->helpers->getPost( $postId );

		// Reset title/descriptions if they are the same as the defaults.
		if ( $thePost->exists() ) {
			$metaTitle       = aioseo()->meta->title->getPostTypeTitle( $post->post_type );
			$metaDescription = aioseo()->meta->description->getPostTypeDescription( $post->post_type );
			if ( empty( $thePost->title ) && ! empty( $data['title'] ) && trim( $data['title'] ) === trim( $metaTitle ) ) {
				$data['title'] = null;
			}

			if ( empty( $thePost->description ) && ! empty( $data['description'] ) && trim( $data['description'] ) === trim( $metaDescription ) ) {
				$data['description'] = null;
			}
		}

		$thePost->post_id                     = $postId;
		$thePost->priority                    = ! empty( $data['priority'] ) ? sanitize_text_field( $data['priority'] ) : null;
		$thePost->frequency                   = ! empty( $data['frequency'] ) ? sanitize_text_field( $data['frequency'] ) : null;
		$thePost->title                       = ! empty( $data['title'] ) ? sanitize_text_field( $data['title'] ) : null;
		$thePost->description                 = ! empty( $data['description'] ) ? sanitize_text_field( $data['description'] ) : null;
		$thePost->keywords                    = ! empty( $data['keywords'] ) ? sanitize_text_field( $data['keywords'] ) : null;
		$thePost->keyphrases                  = ! empty( $data['keyphrases'] ) ? wp_json_encode( $data['keyphrases'] ) : null;
		$thePost->page_analysis               = ! empty( $data['page_analysis'] ) ? wp_json_encode( $data['page_analysis'] ) : null;
		$thePost->seo_score                   = ! empty( $data['seo_score'] ) ? sanitize_text_field( $data['seo_score'] ) : 0;
		$thePost->canonical_url               = ! empty( $data['canonicalUrl'] ) ? esc_url_raw( $data['canonicalUrl'] ) : null;
		$thePost->pillar_content              = isset( $data['pillar_content'] ) ? rest_sanitize_boolean( $data['pillar_content'] ) : 0;
		$thePost->robots_default              = isset( $data['default'] ) ? rest_sanitize_boolean( $data['default'] ) : 1; // robots_enabled
		$thePost->robots_noindex              = isset( $data['noindex'] ) ? rest_sanitize_boolean( $data['noindex'] ) : 0;
		$thePost->robots_nofollow             = isset( $data['nofollow'] ) ? rest_sanitize_boolean( $data['nofollow'] ) : 0;
		$thePost->robots_noarchive            = isset( $data['noarchive'] ) ? rest_sanitize_boolean( $data['noarchive'] ) : 0;
		$thePost->robots_notranslate          = isset( $data['notranslate'] ) ? rest_sanitize_boolean( $data['notranslate'] ) : 0;
		$thePost->robots_noimageindex         = isset( $data['noimageindex'] ) ? rest_sanitize_boolean( $data['noimageindex'] ) : 0;
		$thePost->robots_nosnippet            = isset( $data['nosnippet'] ) ? rest_sanitize_boolean( $data['nosnippet'] ) : 0;
		$thePost->robots_noodp                = isset( $data['noodp'] ) ? rest_sanitize_boolean( $data['noodp'] ) : 0;
		$thePost->robots_max_snippet          = ! empty( $data['maxSnippet'] ) ? sanitize_text_field( $data['maxSnippet'] ) : 0;
		$thePost->robots_max_videopreview     = ! empty( $data['maxVideoPreview'] ) ? (int) sanitize_text_field( $data['maxVideoPreview'] ) : 0;
		$thePost->robots_max_imagepreview     = ! empty( $data['maxImagePreview'] ) ? sanitize_text_field( $data['maxImagePreview'] ) : 'none';
		$thePost->og_object_type              = ! empty( $data['og_object_type'] ) ? sanitize_text_field( $data['og_object_type'] ) : 'default';
		$thePost->og_title                    = ! empty( $data['og_title'] ) ? sanitize_text_field( $data['og_title'] ) : null;
		$thePost->og_description              = ! empty( $data['og_description'] ) ? sanitize_text_field( $data['og_description'] ) : null;
		$thePost->og_image_custom_url         = ! empty( $data['og_image_custom_url'] ) ? esc_url_raw( $data['og_image_custom_url'] ) : null;
		$thePost->og_image_custom_fields      = ! empty( $data['og_image_custom_fields'] ) ? sanitize_text_field( $data['og_image_custom_fields'] ) : null;
		$thePost->og_image_type               = ! empty( $data['og_image_type'] ) ? sanitize_text_field( $data['og_image_type'] ) : 'default';
		$thePost->og_video                    = ! empty( $data['og_video'] ) ? sanitize_text_field( $data['og_video'] ) : '';
		$thePost->og_article_section          = ! empty( $data['og_article_section'] ) ? sanitize_text_field( $data['og_article_section'] ) : null;
		$thePost->og_article_tags             = ! empty( $data['og_article_tags'] ) ? sanitize_text_field( $data['og_article_tags'] ) : null;
		$thePost->twitter_use_og              = isset( $data['twitter_use_og'] ) ? rest_sanitize_boolean( $data['twitter_use_og'] ) : 0;
		$thePost->twitter_card                = ! empty( $data['twitter_card'] ) ? sanitize_text_field( $data['twitter_card'] ) : 'default';
		$thePost->twitter_image_custom_url    = ! empty( $data['twitter_image_custom_url'] ) ? esc_url_raw( $data['twitter_image_custom_url'] ) : null;
		$thePost->twitter_image_custom_fields = ! empty( $data['twitter_image_custom_fields'] ) ? sanitize_text_field( $data['twitter_image_custom_fields'] ) : null;
		$thePost->twitter_image_type          = ! empty( $data['twitter_image_type'] ) ? sanitize_text_field( $data['twitter_image_type'] ) : 'default';
		$thePost->twitter_title               = ! empty( $data['twitter_title'] ) ? sanitize_text_field( $data['twitter_title'] ) : null;
		$thePost->twitter_description         = ! empty( $data['twitter_description'] ) ? sanitize_text_field( $data['twitter_description'] ) : null;
		$thePost->schema_type                 = ! empty( $data['schema_type'] ) ? sanitize_text_field( $data['schema_type'] ) : 'none';
		$thePost->schema_type_options         = ! empty( $data['schema_type_options'] ) ? wp_json_encode( $data['schema_type_options'] ) : parent::getDefaultSchemaOptions();
		$thePost->tabs                        = ! empty( $data['tabs'] ) ? wp_json_encode( $data['tabs'] ) : parent::getDefaultTabsOptions();
		$thePost->local_seo                   = ! empty( $data['local_seo'] ) ? wp_json_encode( $data['local_seo'] ) : null;
		$thePost->updated                     = gmdate( 'Y-m-d H:i:s' );

		if ( ! $thePost->exists() ) {
			$thePost->created = gmdate( 'Y-m-d H:i:s' );
		}
		$thePost->save();
		$thePost->reset();

		// Update the post meta as well for localization.
		$keywords = ! empty( $data['keywords'] ) ? json_decode( $data['keywords'] ) : [];
		foreach ( $keywords as $k => $keyword ) {
			$keywords[ $k ] = $keyword->value;
		}
		$keywords = implode( ',', $keywords );

		$ogArticleTags = ! empty( $data['og_article_tags'] ) ? json_decode( $data['og_article_tags'] ) : [];
		foreach ( $ogArticleTags as $k => $tag ) {
			$ogArticleTags[ $k ] = $tag->value;
		}
		$ogArticleTags = implode( ',', $ogArticleTags );

		if ( ! empty( $data ) ) {
			update_post_meta( $postId, '_aioseo_title', $data['title'] );
			update_post_meta( $postId, '_aioseo_description', $data['description'] );
			update_post_meta( $postId, '_aioseo_keywords', $keywords );
			update_post_meta( $postId, '_aioseo_og_title', $data['og_title'] );
			update_post_meta( $postId, '_aioseo_og_description', $data['og_description'] );
			update_post_meta( $postId, '_aioseo_og_article_section', $data['og_article_section'] );
			update_post_meta( $postId, '_aioseo_og_article_tags', $ogArticleTags );
			update_post_meta( $postId, '_aioseo_twitter_title', $data['twitter_title'] );
			update_post_meta( $postId, '_aioseo_twitter_description', $data['twitter_description'] );
		}

		$lastError = aioseo()->db->lastError();
		if ( ! empty( $lastError ) ) {
			return $lastError;
		}

		return null;
	}

	/**
	 * Get default values for TruSEO page analysis
	 *
	 * @since 4.0.0
	 *
	 * @return object The default TruSEO page analysis values.
	 */
	public static function getPageAnalysisDefaults() {
		$analysisDefaults = [
			'analysis' => [
				'basic'       => [
					'metadescriptionLength' => [
						'error'       => 1,
						'maxScore'    => 9,
						'score'       => 6,
						'title'       => __( 'Meta description length', 'all-in-one-seo-pack' ),
						'description' => __( 'The meta description is too short.', 'all-in-one-seo-pack' )
					],
					'isInternalLink'        => [
						'error'       => 1,
						'maxScore'    => 9,
						'score'       => 3,
						'title'       => __( 'Internal links', 'all-in-one-seo-pack' ),
						'description' => __( 'We couldn\'t find any internal links in your content. Add internal links in your content.', 'all-in-one-seo-pack' )
					],
					'isExternalLink'        => [
						'error'       => 1,
						'maxScore'    => 9,
						'score'       => 3,
						'title'       => __( 'External links', 'all-in-one-seo-pack' ),
						'description' => __( 'No outbound links were found. Link out to external resources.', 'all-in-one-seo-pack' )
					],
					'errors'                => 3
				],
				'title'       => [
					'titleLength' => [
						'error'       => 1,
						'maxScore'    => 9,
						'score'       => 1,
						'title'       => __( 'SEO Title length', 'all-in-one-seo-pack' ),
						'description' => __( 'No title has been specified. Make sure to write one!', 'all-in-one-seo-pack' )
					],
					// 'titleHasNumber'     => [
					//  'error'       => 1,
					//  'maxScore'    => 9,
					//  'score'       => 5,
					//  'title'       => __( 'Title has number', 'all-in-one-seo-pack' ),
					//  'description' => __( 'Your SEO title doesn\'t contain a number. Add a number to your title to improve CTR.', 'all-in-one-seo-pack' )
					// ],
					// 'titleHasPowerWords' => [
					//  'error'       => 1,
					//  'maxScore'    => 9,
					//  'score'       => 5,
					//  'title'       => __( 'Title has Power Words', 'all-in-one-seo-pack' ),
					//  'description' => __(
					//      'Your SEO title doesn\'t contain a power word. Add at least one. Power Words are tried-and-true words that copywriters use to attract more clicks.',
					//      'all-in-one-seo-pack'
					//  ) // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					// ],
					// 'titleSentiment'     => [
					//  'error'       => 1,
					//  'maxScore'    => 9,
					//  'score'       => 5,
					//  'title'       => __( 'Title Sentiment Words', 'all-in-one-seo-pack' ),
					//  'description' => __(
					//      'Your SEO title doesn\'t contain a sentiment word. Headlines with a strong emotional sentiment (positive or negative) tend to receive more clicks.',
					//      'all-in-one-seo-pack'
					//  ) // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					// ],
					'errors'      => 1
				],
				'readability' => [
					'contentHasAssets'        => [
						'error'       => 1,
						'maxScore'    => 5,
						'score'       => 0,
						'title'       => __( 'Images/Videos in content', 'all-in-one-seo-pack' ),
						'description' => __( 'You are not using rich media like images or videos.', 'all-in-one-seo-pack' )
					],
					'passiveVoice'            => [
						'error'       => 1,
						'maxScore'    => 9,
						'score'       => 3,
						'title'       => __( 'Passive Voice', 'all-in-one-seo-pack' ),
						'description' => __( 'Use active voice.', 'all-in-one-seo-pack' )
					],
					'subheadingsDistribution' => [
						'error'       => 1,
						'maxScore'    => 9,
						'score'       => 2,
						'title'       => __( 'Subheading distribution', 'all-in-one-seo-pack' ),
						'description' => __( 'You are not using any subheadings, although your text is rather long. Try and add some subheadings.', 'all-in-one-seo-pack' )
					],
					'errors'                  => 3
				]
			]
		];
		return json_decode( wp_json_encode( $analysisDefaults ) );
	}
}