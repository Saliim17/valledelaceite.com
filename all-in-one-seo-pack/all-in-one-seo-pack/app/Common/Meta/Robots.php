<?php
namespace AIOSEO\Plugin\Common\Meta;

/**
 * Handles the robots meta tag.
 *
 * @since 4.0.0
 */
class Robots {

	/**
	 * The robots meta tag attributes.
	 *
	 * We'll already set the keys on construction so that we always output the attributes in the same order.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $attributes = [
		'noindex'           => '',
		'nofollow'          => '',
		'noarchive'         => '',
		'nosnippet'         => '',
		'noimageindex'      => '',
		'noodp'             => '',
		'notranslate'       => '',
		'max-snippet'       => '',
		'max-image-preview' => '',
		'max-video-preview' => ''
	];

	/**
	 * Returns the robots meta tag value.
	 *
	 * @since 4.0.0
	 *
	 * @return mixed The robots meta tag value or false.
	 */
	public function meta() {
		if ( is_category() || is_tag() || is_tax() ) {
			return $this->term();
		}

		if ( ! get_option( 'blog_public' ) || $this->isNoindexedWooCommercePage() ) {
			return false;
		}

		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$this->globalValues();
			return $this->metaHelper();
		}

		$post = aioseo()->helpers->getPost();
		if ( $post ) {
			$this->post();
			return $this->metaHelper();
		}

		if ( is_author() ) {
			$this->globalValues( [ 'archives', 'author' ] );
			return $this->metaHelper();
		}

		if ( is_date() ) {
			$this->globalValues( [ 'archives', 'date' ] );
			return $this->metaHelper();
		}

		if ( is_search() ) {
			$this->globalValues( [ 'archives', 'search' ] );
			return $this->metaHelper();
		}

		if ( is_404() ) {
			return apply_filters( 'aioseo_404_robots', 'noindex' );
		}

		if ( is_archive() ) {
			$this->archives();
			return $this->metaHelper();
		}
	}

	/**
	 * Stringifies and filters the robots meta tag value.
	 *
	 * Acts as a helper for meta().
	 *
	 * @since 4.0.0
	 *
	 * @return string The robots meta tag value.
	 */
	protected function metaHelper() {
		$pageNumber = aioseo()->helpers->getPageNumber();
		if ( 1 < $pageNumber ) {
			if ( aioseo()->options->searchAppearance->advanced->globalRobotsMeta->noindexPaginated ) {
				$this->attributes['noindex'] = 'noindex';
			}

			if ( aioseo()->options->searchAppearance->advanced->globalRobotsMeta->nofollowPaginated ) {
				$this->attributes['nofollow'] = 'nofollow';
			}
		}

		// Never allow users to noindex the homepage.
		if ( is_front_page() ) {
			$this->attributes['noindex'] = '';
		}

		$this->attributes = apply_filters( 'aioseo_robots_meta', $this->attributes );
		return implode( ', ', array_filter( $this->attributes ) );
	}

	/**
	 * Sets the attributes for the current post.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function post() {
		$options  = aioseo()->options->noConflict();
		$post     = aioseo()->helpers->getPost();
		$metaData = aioseo()->meta->metaData->getMetaData( $post );

		if ( ! empty( $metaData ) && ! $metaData->robots_default ) {
			$this->metaValues( $metaData );
			return;
		}

		if ( $options->searchAppearance->dynamic->postTypes->has( $post->post_type ) ) {
			$this->globalValues( [ 'dynamic', 'postTypes', $post->post_type ] );
		}
	}

	/**
	 * Returns the robots meta tag value for the current term.
	 *
	 * @since 4.0.6
	 *
	 * @return string The robots meta tag value.
	 */
	private function term() {
		$options  = aioseo()->options->noConflict();
		$term     = get_queried_object();

		if ( $options->searchAppearance->dynamic->taxonomies->has( $term->taxonomy ) ) {
			$this->globalValues( [ 'dynamic', 'taxonomies', $term->taxonomy ] );
			return$this->metaHelper();
		}

		$this->globalValues();
		return $this->metaHelper();
	}

	/**
	 * Sets the attributes for the current archive.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function archives() {
		$options  = aioseo()->options->noConflict();
		$postType = get_queried_object();

		if ( $options->searchAppearance->dynamic->archives->has( $postType->name ) ) {
			$this->globalValues( [ 'dynamic', 'archives', $postType->name ] );
		}
	}

	/**
	 * Sets the attributes based on the global values.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $optionOrder The order in which the options need to be called to get the relevant robots meta settings.
	 * @return void
	 */
	protected function globalValues( $optionOrder = [] ) {
		$robotsMeta = [];
		if ( count( $optionOrder ) ) {
			$options = aioseo()->options->noConflict()->searchAppearance;
			foreach ( $optionOrder as $option ) {
				if ( ! $options->has( $option, false ) ) {
					return;
				};
				$options = $options->$option;
			}

			$clonedOptions = clone $options;
			if ( ! $clonedOptions->show ) {
				$this->attributes['noindex'] = 'noindex';
			}

			$robotsMeta = $options->advanced->robotsMeta->all();
			if ( $robotsMeta['default'] ) {
				$robotsMeta = aioseo()->options->searchAppearance->advanced->globalRobotsMeta->all();
			}
		} else {
			$robotsMeta = aioseo()->options->searchAppearance->advanced->globalRobotsMeta->all();
		}

		if ( $robotsMeta['default'] ) {
			return;
		}

		if ( $robotsMeta['noindex'] ) {
			$this->attributes['noindex'] = 'noindex';
		}
		if ( $robotsMeta['nofollow'] ) {
			$this->attributes['nofollow'] = 'nofollow';
		}
		if ( $robotsMeta['noarchive'] ) {
			$this->attributes['noarchive'] = 'noarchive';
		}
		$noSnippet = $robotsMeta['nosnippet'];
		if ( $noSnippet ) {
			$this->attributes['nosnippet'] = 'nosnippet';
		}
		$noImageIndex = $robotsMeta['noimageindex'];
		if ( $noImageIndex ) {
			$this->attributes['noimageindex'] = 'noimageindex';
		}
		if ( $robotsMeta['noodp'] ) {
			$this->attributes['noodp'] = 'noodp';
		}
		if ( $robotsMeta['notranslate'] ) {
			$this->attributes['notranslate'] = 'notranslate';
		}
		$maxSnippet = $robotsMeta['maxSnippet'];
		if ( ! $noSnippet && $maxSnippet && intval( $maxSnippet ) ) {
			$this->attributes['max-snippet'] = "max-snippet:$maxSnippet";
		}
		$maxImagePreview = $robotsMeta['maxImagePreview'];
		if ( ! $noImageIndex && $maxImagePreview && in_array( $maxImagePreview, [ 'none', 'standard', 'large' ], true ) ) {
			$this->attributes['max-image-preview'] = "max-image-preview:$maxImagePreview";
		}
		$maxVideoPreview = $robotsMeta['maxVideoPreview'];
		if ( $maxVideoPreview && intval( $maxVideoPreview ) ) {
			$this->attributes['max-video-preview'] = "max-video-preview:$maxVideoPreview";
		}
	}

	/**
	 * Sets the attributes from the meta data.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $metaData The post/term meta data.
	 * @return void
	 */
	protected function metaValues( $metaData ) {
		if ( $metaData->robots_noindex || $this->isPasswordProtected() ) {
			$this->attributes['noindex'] = 'noindex';
		}
		if ( $metaData->robots_nofollow ) {
			$this->attributes['nofollow'] = 'nofollow';
		}
		if ( $metaData->robots_noarchive ) {
			$this->attributes['noarchive'] = 'noarchive';
		}
		if ( $metaData->robots_nosnippet ) {
			$this->attributes['nosnippet'] = 'nosnippet';
		}
		if ( $metaData->robots_noimageindex ) {
			$this->attributes['noimageindex'] = 'noimageindex';
		}
		if ( $metaData->robots_noodp ) {
			$this->attributes['noodp'] = 'noodp';
		}
		if ( $metaData->robots_notranslate ) {
			$this->attributes['notranslate'] = 'notranslate';
		}
		if ( ! $metaData->robots_nosnippet && $metaData->robots_max_snippet && intval( $metaData->robots_max_snippet ) ) {
			$this->attributes['max-snippet'] = "max-snippet:$metaData->robots_max_snippet";
		}
		if ( ! $metaData->robots_noimageindex && $metaData->robots_max_imagepreview && in_array( $metaData->robots_max_imagepreview, [ 'none', 'standard', 'large' ], true ) ) {
			$this->attributes['max-image-preview'] = "max-image-preview:$metaData->robots_max_imagepreview";
		}
		if ( $metaData->robots_max_videopreview && intval( $metaData->robots_max_videopreview ) ) {
			$this->attributes['max-video-preview'] = "max-video-preview:$metaData->robots_max_videopreview";
		}
	}

	/**
	 * Checks whether the current post is password protected.
	 *
	 * @since 4.0.0
	 *
	 * @return bool Whether the post is password protected.
	 */
	private function isPasswordProtected() {
		$post = aioseo()->helpers->getPost();
		return is_object( $post ) && $post->post_password;
	}

	/**
	 * Checks whether the current page is a noindexed WooCommerce page.
	 *
	 * WooCommerce noindexes the Cart, Checkout and My Account pages by default. In this case, we don't need to output another robots meta tag.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether the current page is an noindexed WooCommerce page.
	 */
	private function isNoindexedWooCommercePage() {
		$post = aioseo()->helpers->getPost();
		if (
			! aioseo()->helpers->isWooCommerceActive() ||
			! is_object( $post ) ||
			'page' !== $post->post_type ||
			! has_action( 'wp_head', 'wc_page_noindex' )
		) {
			return false;
		}

		return in_array( get_permalink(), aioseo()->helpers->getNoindexedWooCommercePages(), true );
	}
}