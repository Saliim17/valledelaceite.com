<?php
namespace AIOSEO\Plugin\Common\Sitemap;

/**
 * Handles our sitemap search engine ping feature.
 *
 * @since 4.0.0
 */
class Ping {

	/**
	 * Registers our hooks.
	 *
	 * @since 4.0.0
	 */
	public function init() {
		if ( 0 === (int) get_option( 'blog_public' ) ) {
			return;
		}

		add_filter( 'init', [ $this, 'scheduleRecurring' ] );

		// Ping sitemap on each post update.
		add_action( 'save_post', [ $this, 'schedule' ], 1000, 2 );
		add_action( 'delete_post', [ $this, 'schedule' ], 1000, 2 );

		// Action Scheduler hooks.
		add_action( 'aioseo_sitemap_ping', [ $this, 'ping' ] );
		add_action( 'aioseo_sitemap_ping_recurring', [ $this, 'ping' ] );
	}

	/**
	 * Schedules a sitemap ping.
	 *
	 * @since 4.0.0
	 *
	 * @param  integer $postId The ID of the post.
	 * @param  WP_Post $post   The post object.
	 * @return void
	 */
	public function schedule( $postId, $post = null ) {
		if ( ! aioseo()->helpers->isValidPost( $post ) ) {
			return;
		}

		try {
			if ( as_next_scheduled_action( 'aioseo_sitemap_ping' ) ) {
				as_unschedule_action( 'aioseo_sitemap_ping', [], 'aioseo' );
			}

			as_schedule_single_action( time() + 30, 'aioseo_sitemap_ping', [], 'aioseo' );
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * Schedules the recurring sitemap ping.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function scheduleRecurring() {
		try {
			if ( ! as_next_scheduled_action( 'aioseo_sitemap_ping_recurring' ) ) {

				$interval = apply_filters( 'aioseo_sitemap_ping_recurring', DAY_IN_SECONDS );
				as_schedule_recurring_action( strtotime( 'tomorrow' ), $interval, 'aioseo_sitemap_ping_recurring', [], 'aioseo' );
			}
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * Pings search engines when the sitemap is updated.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $sitemapUrls Sitemap URLs that should be sent to the remote endpoints.
	 * @return void
	 */
	public function ping( $sitemapUrls = [] ) {
		$endpoints = apply_filters( 'aioseo_sitemap_ping_urls', [
			'https://www.google.com/ping?sitemap=',
			'https://www.bing.com/ping?sitemap='
		] );

		if ( aioseo()->options->sitemap->general->enable ) {
			// Check if user has a custom filename from the V3 migration.
			$sitemapUrls[] = trailingslashit( home_url() ) . aioseo()->sitemap->helpers->filename() . '.xml';
		}
		if ( aioseo()->options->sitemap->rss->enable ) {
			$sitemapUrls[] = trailingslashit( home_url() ) . 'sitemap.rss';
		}

		foreach ( aioseo()->sitemap->addons as $addon => $classes ) {
			if ( ! empty( $classes['ping'] ) ) {
				$sitemapUrls = $sitemapUrls + $classes['ping']->getPingUrls();
			}
		}

		foreach ( $endpoints as $endpoint ) {
			foreach ( $sitemapUrls as $url ) {
				wp_remote_get( urlencode( $endpoint . $url ) );
				// @TODO: [V4+] Log bad responses using dedicated logger class once available.
			}
		}
	}
}