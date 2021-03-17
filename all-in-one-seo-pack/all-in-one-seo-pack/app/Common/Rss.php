<?php
namespace AIOSEO\Plugin\Common;

/**
 * Adds content before or after posts in the RSS feed.
 *
 * @since 4.0.0
 */
class Rss {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_filter( 'the_content_feed', [ $this, 'addRssContent' ] );
		add_filter( 'the_excerpt_rss', [ $this, 'addRssContentExcerpt' ] );
	}

	/**
	 * Adds content before or after the RSS excerpt.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $content The
	 * @return void
	 */
	public function addRssContentExcerpt( $content ) {
		return $this->addRssContent( $content, 'excerpt' );
	}

	/**
	 * Adds content before or after the RSS post.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $content The
	 * @return void
	 */
	public function addRssContent( $content, $type = 'complete' ) {
		$content = trim( $content );
		if ( empty( $content ) ) {
			return '';
		}

		if ( is_feed() ) {
			$before = aioseo()->tags->replaceTags( aioseo()->options->rssContent->before, get_the_ID() );
			$after  = aioseo()->tags->replaceTags( aioseo()->options->rssContent->after, get_the_ID() );

			if ( $before || $after ) {
				if ( 'excerpt' === $type ) {
					$content = wpautop( $content );
				}
				$content = aioseo()->helpers->decodeHtmlEntities( $before ) . $content . aioseo()->helpers->decodeHtmlEntities( $after );
			}
		}

		return $content;
	}
}