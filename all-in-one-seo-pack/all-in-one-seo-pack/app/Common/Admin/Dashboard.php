<?php
namespace AIOSEO\Plugin\Common\Admin;

/**
 * Class that holds our dashboard widget.
 *
 * @since 4.0.0
 */
class Dashboard {
	/**
	 * Class Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', [ $this, 'addDashboardWidget' ] );
	}

	/**
	 * Add Dashboard Widget
	 *
	 * @since 2.3.10
	 */
	public function addDashboardWidget() {
		if ( current_user_can( 'install_plugins' ) && $this->showWidget() ) {
			wp_add_dashboard_widget(
				'aioseo-rss-feed',
				esc_html__( 'SEO News', 'all-in-one-seo-pack' ),
				[
					$this,
					'displayRssDashboardWidget',
				]
			);
		}
	}

	/**
	 * Whether or not to show the widget.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean True if yes, false otherwise.
	 */
	protected function showWidget() {
		// API filter hook to disable showing SEO News dashboard widget.
		if ( false === apply_filters( 'aioseo_show_seo_news', true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Display RSS Dashboard Widget
	 *
	 * @since 4.0.0
	 */
	public function displayRssDashboardWidget() {
		// check if the user has chosen not to display this widget through screen options.
		$currentScreen = get_current_screen();
		$hiddenWidgets = get_user_meta( get_current_user_id(), 'metaboxhidden_' . $currentScreen->id );
		if ( $hiddenWidgets && count( $hiddenWidgets ) > 0 && is_array( $hiddenWidgets[0] ) && in_array( 'aioseo-rss-feed', $hiddenWidgets[0], true ) ) {
			return;
		}

		include_once( ABSPATH . WPINC . '/feed.php' );

		$rssItems = get_transient( 'aioseo_rss_feed' );
		if ( false === $rssItems ) {

			$rss = fetch_feed( 'https://aioseo.com/feed/' );
			if ( is_wp_error( $rss ) ) {
				esc_html_e( 'Temporarily unable to load feed.', 'all-in-one-seo-pack' );

				return;
			}
			$rssItems = $rss->get_items( 0, 4 ); // Show four items.
			$cached   = [];
			foreach ( $rssItems as $item ) {
				$cached[] = [
					'url'     => $item->get_permalink(),
					'title'   => $item->get_title(),
					'date'    => $item->get_date( 'M jS Y' ),
					'content' => substr( wp_strip_all_tags( $item->get_content() ), 0, 128 ) . '...',
				];
			}
			$rssItems = $cached;

			set_transient( 'aioseo_rss_feed', $cached, 12 * HOUR_IN_SECONDS );

		}

		?>

		<ul>
			<?php
			if ( false === $rssItems ) {
				echo '<li>No items</li>';

				return;
			}

			foreach ( $rssItems as $item ) {
				?>
				<li>
					<a target="_blank" href="<?php echo esc_url( $item['url'] ); ?>">
						<?php echo esc_html( $item['title'] ); ?>
					</a>
					<span class="aioseop-rss-date"><?php echo esc_html( $item['date'] ); ?></span>
					<div class="aioseop_news">
						<?php echo esc_html( wp_strip_all_tags( $item['content'] ) ) . '...'; ?>
					</div>
				</li>
				<?php
			}

			?>
		</ul>

		<?php

	}
}