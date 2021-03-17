<?php
namespace AIOSEO\Plugin\Common\Meta;

/**
 * Handles the title.
 *
 * @since 4.0.0
 */
class Title {
	/**
	 * Returns the filtered page title.
	 *
	 * Acts as a helper for getTitle() because we need to encode the title before sending it back to the filter.
	 *
	 * @since 4.0.0
	 *
	 * @return string The page title.
	 */
	public function filterPageTitle() {
		return aioseo()->helpers->encodeOutputHtml( $this->getTitle() );
	}

	/**
	 * Returns the homepage title.
	 *
	 * @since 4.0.0
	 *
	 * @return string The homepage title.
	 */
	public function getHomePageTitle() {
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$title = $this->getPostTitle( (int) get_option( 'page_on_front' ) );
			return $title ? $title : aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) );
		}

		$title = $this->prepareTitle( aioseo()->options->searchAppearance->global->siteTitle );
		return $title ? $title : aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) );
	}

	/**
	 * Returns the title for the current page.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post $post    The post object (optional).
	 * @param  boolean $default Whether we want the default value, not the post one.
	 * @return string           The page title.
	 */
	public function getTitle( $post = null, $default = false ) {
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			return $this->getHomePageTitle();
		}

		if ( $post || is_singular() || aioseo()->helpers->isStaticPage() ) {
			return $this->getPostTitle( $post, $default );
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$term = $post ? $post : get_queried_object();
			return $this->getTermTitle( $term, $default );
		}

		if ( is_author() ) {
			$title = $this->prepareTitle( aioseo()->options->searchAppearance->archives->author->title );
			if ( $title ) {
				return $title;
			}
			$author = get_queried_object();
			$name   = trim( sprintf( '%1$s %2$s', get_the_author_meta( 'first_name', $author->ID ), get_the_author_meta( 'last_name', $author->ID ) ) );
			return $this->prepareTitle( $name );
		}

		if ( is_date() ) {
			$title = $this->prepareTitle( aioseo()->options->searchAppearance->archives->date->title );
			if ( $title ) {
				return $title;
			}

			if ( is_year() ) {
				$title = get_the_date( 'Y' );
			} elseif ( is_month() ) {
				$title = get_the_date( 'F, Y' );
			} elseif ( is_day() ) {
				$title = get_the_date();
			}
			return $this->prepareTitle( "$title &#8211; #site_title" );
		}

		if ( is_search() ) {
			return $this->prepareTitle( aioseo()->options->searchAppearance->archives->search->title );
		}

		if ( is_archive() ) {
			$postType = get_queried_object();
			$options  = aioseo()->options->noConflict();
			if ( $options->searchAppearance->dynamic->archives->has( $postType->name ) ) {
				return $this->prepareTitle( aioseo()->options->searchAppearance->dynamic->archives->{ $postType->name }->title );
			}
		}
	}

	/**
	 * Returns the post title.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post|int $post    The post object or ID.
	 * @param  boolean     $default Whether we want the default value, not the post one.
	 * @return string               The post title.
	 */
	public function getPostTitle( $post, $default = false ) {
		$post     = $post && is_object( $post ) ? $post : aioseo()->helpers->getPost( $post );
		$metaData = aioseo()->meta->metaData->getMetaData( $post );

		$title = '';
		if ( ! empty( $metaData->title ) && ! $default ) {
			$title = $this->prepareTitle( $metaData->title, $post->ID );
		}

		// If this post is the static home page and we have no title, let's reset to the site name.
		if ( empty( $title ) && 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) === $post->ID ) {
			return aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) );
		}

		if ( ! $title ) {
			$title = $this->prepareTitle( $this->getPostTypeTitle( $post->post_type ), $post->ID, $default );
		}
		return $title ? $title : $this->prepareTitle( $post->post_title, $post->ID, $default );
	}

	/**
	 * Retrieve the default title for the post type.
	 *
	 * @since 4.0.6
	 *
	 * @param  string $postType The post type.
	 * @return string           The title.
	 */
	public function getPostTypeTitle( $postType ) {
		$options = aioseo()->options->noConflict();
		if ( $options->searchAppearance->dynamic->postTypes->has( $postType, false ) ) {
			return $options->{$postType}->title;
		}

		return '';
	}

	/**
	 * Returns the term title.
	 *
	 * @since 4.0.6
	 *
	 * @param  WP_Term $term    The term object.
	 * @param  boolean $default Whether we want the default value, not the post one.
	 * @return string           The term title.
	 */
	public function getTermTitle( $term, $default = false ) {
		$title   = '';
		$options = aioseo()->options->noConflict();
		if ( ! $title && $options->searchAppearance->dynamic->taxonomies->has( $term->taxonomy ) ) {
			$newTitle = aioseo()->options->searchAppearance->dynamic->taxonomies->{$term->taxonomy}->title;
			$newTitle = preg_replace( '/#taxonomy_title/', $term->name, $newTitle );
			$title    = $this->prepareTitle( $newTitle, false, $default );
		}
		return $title ? $title : $this->prepareTitle( single_term_title( '', false ), false, $default );
	}

	/**
	 * Prepares and sanitizes the title.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $title   The title.
	 * @param  int     $id      The page or post id.
	 * @param  boolean $default Whether we want the default value, not the post one.
	 * @return string           The sanitized title.
	 */
	public function prepareTitle( $title, $id = false, $default = false ) {
		if ( ! is_admin() && 1 < aioseo()->helpers->getPageNumber() ) {
			$title .= aioseo()->options->searchAppearance->advanced->pagedFormat;
		}

		$title = $default ? $title : aioseo()->tags->replaceTags( $title, $id );
		$title = apply_filters( 'aioseo_title', $title );

		$title = aioseo()->helpers->decodeHtmlEntities( $title );
		$title = wp_strip_all_tags( strip_shortcodes( $title ) );
		// Trim both internal and external whitespace.
		$title = preg_replace( '/[\s]+/u', ' ', trim( $title ) );
		return aioseo()->helpers->internationalize( $title );
	}
}