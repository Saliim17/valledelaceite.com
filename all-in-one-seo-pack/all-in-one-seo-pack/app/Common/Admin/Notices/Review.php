<?php
namespace AIOSEO\Plugin\Common\Admin\Notices;

/**
 * Review Plugin Notice.
 *
 * @since 4.0.0
 */
class Review {
	/**
	 * Class Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_aioseo-dismiss-review-plugin-cta', [ $this, 'dismissNotice' ] );
	}

	/**
	 * Go through all the checks to see if we should show the notice.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function maybeShowNotice() {
		$dismissed = get_user_meta( get_current_user_id(), '_aioseo_plugin_review_dismissed', true );
		if ( '1' === $dismissed ) {
			return;
		}

		if ( ! empty( $dismissed ) && $dismissed > time() ) {
			return;
		}

		// Only show if plugin has been active for over two weeks.
		if ( ! aioseo()->internalOptions->internal->firstActivated ) {
			aioseo()->internalOptions->internal->firstActivated = time();
		}

		$activated = aioseo()->internalOptions->internal->firstActivated( time() );
		if ( $activated > strtotime( '-2 weeks' ) ) {
			return;
		}

		$this->showNotice();
	}

	/**
	 * Actually show the review plugin.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function showNotice() {
		$feedbackUrl = add_query_arg(
			[
				'wpf7528_24'   => untrailingslashit( home_url() ),
				'wpf7528_26'   => aioseo()->options->has( 'general' ) && aioseo()->options->general->has( 'licenseKey' )
					? aioseo()->options->general->licenseKey
					: '',
				'wpf7528_27'   => aioseo()->pro ? 'pro' : 'lite',
				'wpf7528_28'   => AIOSEO_VERSION,
				'utm_source'   => aioseo()->pro ? 'proplugin' : 'liteplugin',
				'utm_medium'   => 'review-notice',
				'utm_campaign' => 'feedback',
				'utm_content'  => AIOSEO_VERSION,
			],
			'https://aioseo.com/plugin-feedback/'
		);

		// Translators: 1 - The plugin name ("All in One SEO").
		$string1  = sprintf( __( 'Are you enjoying %1$s?', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_NAME );
		$string2  = __( 'Yes I love it', 'all-in-one-seo-pack' );
		$string3  = __( 'Not Really...', 'all-in-one-seo-pack' );
		// Translators: The plugin name ("All in One SEO").
		$string4  = sprintf( __( 'We\'re sorry to hear you aren\'t enjoying %1$s. We would love a chance to improve. Could you take a minute and let us know what we can do better?', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_NAME ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
		$string5  = __( 'Give feedback', 'all-in-one-seo-pack' );
		$string6  = __( 'No thanks', 'all-in-one-seo-pack' );
		$string7  = __( 'That\'s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'all-in-one-seo-pack' );
		// Translators: The plugin name ("All in One SEO").
		$string8  = sprintf( __( 'CEO of %1$s', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_NAME );
		$string9  = __( 'Ok, you deserve it', 'all-in-one-seo-pack' );
		$string10 = __( 'Nope, maybe later', 'all-in-one-seo-pack' );
		$string11 = __( 'I already did', 'all-in-one-seo-pack' );

		$nonce = wp_create_nonce( 'aioseo-dismiss-review' );
		?>
		<div class="notice notice-info aioseo-review-plugin-cta is-dismissible">
			<div class="step-1">
				<p><?php echo esc_html( $string1 ); ?></p>
				<p>
					<a href="#" class="aioseo-review-switch-step-3" data-step="3"><?php echo esc_html( $string2 ); ?></a> 🙂 |
					<a href="#" class="aioseo-review-switch-step-2" data-step="2"><?php echo esc_html( $string3 ); ?></a>
				</p>
			</div>
			<div class="step-2" style="display:none;">
				<p><?php echo esc_html( $string4 ); ?></p>
				<p>
					<a href="<?php echo esc_url( $feedbackUrl ); ?>" class="aioseo-dismiss-review-notice" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $string5 ); ?></a>&nbsp;&nbsp;
					<a href="#" class="aioseo-dismiss-review-notice" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $string6 ); ?></a>
				</p>
			</div>
			<div class="step-3" style="display:none;">
				<p><?php echo esc_html( $string7 ); ?></p>
				<p><strong>~ Syed Balkhi<br><?php echo esc_html( $string8 ); ?></strong></p>
				<p>
					<a href="https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews/?filter=5#new-post" class="aioseo-dismiss-review-notice" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $string9 ); ?>
					</a>&nbsp;&nbsp;
					<a href="#" class="aioseo-dismiss-review-notice-delay" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $string10 ); ?>
					</a>&nbsp;&nbsp;
					<a href="#" class="aioseo-dismiss-review-notice" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $string11 ); ?>
					</a>
				</p>
			</div>
		</div>
		<style>
		.aioseop-notice-review_plugin_cta .aioseo-action-buttons {
			display: none;
		}
		</style>
		<script type="text/javascript">
		// @TODO: [V4+] Move this into vue or ES6 app.
		jQuery(document).ready(function() {
			var aioseoSetupButton = function (button) {
				var delay = false;
				var aioseoReviewPluginDismiss = function () {
					jQuery.post(window.ajaxurl, {
						delay  : delay,
						nonce  : '<?php echo esc_attr( $nonce ); ?>',
						action : 'aioseo-dismiss-review-plugin-cta'
					});
				};
				button.addEventListener('click', function (event) {
					// Dismiss notice here.
					event.preventDefault();
					aioseoReviewPluginDismiss();
				});

				jQuery(document).on('click', '.aioseo-review-plugin-cta .aioseo-review-switch-step-3', function(event) {
					event.preventDefault();
					jQuery('.aioseo-review-plugin-cta .step-1, .aioseo-review-plugin-cta .step-2').hide();
					jQuery('.aioseo-review-plugin-cta .step-3').show();
				});
				jQuery(document).on('click', '.aioseo-review-plugin-cta .aioseo-review-switch-step-2', function(event) {
					event.preventDefault();
					jQuery('.aioseo-review-plugin-cta .step-1, .aioseo-review-plugin-cta .step-3').hide();
					jQuery('.aioseo-review-plugin-cta .step-2').show();
				});
				jQuery(document).on('click', '.aioseo-review-plugin-cta .aioseo-dismiss-review-notice-delay', function(event) {
					event.preventDefault();
					delay = true;
					button.click();
				});
				jQuery(document).on('click', '.aioseo-review-plugin-cta .aioseo-dismiss-review-notice', function(event) {
					if ('#' === jQuery(this).attr('href')) {
						event.preventDefault();
					}
					button.click();
				});
			}

			var notice = document.querySelector('.notice.aioseo-review-plugin-cta');
			var button = notice.querySelector('button.notice-dismiss');
			if (!button) {
				var interval = window.setInterval(function() {
					button = notice.querySelector('button.notice-dismiss');
					if (button) {
						aioseoSetupButton(button);
						window.clearInterval(interval)
					}
				}, 50);
			}
		});
		</script>
		<?php
	}

	/**
	 * Dismiss the review plugin CTA.
	 *
	 * @since 4.0.0
	 *
	 * @return WP_Response The successful response.
	 */
	public function dismissNotice() {
		check_ajax_referer( 'aioseo-dismiss-review', 'nonce' );
		$delay = isset( $_POST['delay'] ) ? 'true' === wp_unslash( $_POST['delay'] ) : false; // phpcs:ignore HM.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( ! $delay ) {
			update_user_meta( get_current_user_id(), '_aioseo_plugin_review_dismissed', true );
			return wp_send_json_success();
		}

		update_user_meta( get_current_user_id(), '_aioseo_plugin_review_dismissed', strtotime( '+1 week' ) );

		return wp_send_json_success();
	}
}