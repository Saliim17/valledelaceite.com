<?php
namespace AIOSEO\Plugin\Common\Help;

class Help {
	/**
	 * Source of notifications content.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $url = 'https://aioseo.com/wp-content/docs.json';

	/**
	 * Settings.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $settings = [
		'docsUrl'          => 'https://aioseo.com/docs/',
		'supportTicketUrl' => 'https://aioseo.com/account/support/',
		'upgradeUrl'       => 'https://aioseo.com/pricing/',
	];

	/**
	 * Gets the URL for the notifications api.
	 *
	 * @since 4.0.0
	 *
	 * @return string The URL to use for the api requests.
	 */
	private function getUrl() {
		if ( defined( 'AIOSEO_DOCS_FEED_URL' ) ) {
			return AIOSEO_DOCS_FEED_URL;
		}

		return $this->url;
	}

	/**
	 * Get docs from the cache.
	 *
	 * @since 4.0.0
	 *
	 * @return array Docs data.
	 */
	public function getDocs() {
		$aioseoAdminHelpDocs          = get_transient( 'aioseo_admin_help_docs' );
		$aioseoAdminHelpDocsCacheTime = WEEK_IN_SECONDS;
		if ( false === $aioseoAdminHelpDocs ) {
			$request = wp_remote_get( $this->getUrl() );

			if ( is_wp_error( $request ) ) {
				return false;
			}

			$response = $request['response'];

			if ( ( $response['code'] <= 200 ) && ( $response['code'] > 299 ) ) {
				$aioseoAdminHelpDocsCacheTime = 10 * MINUTE_IN_SECONDS;
			}
			$docs = wp_remote_retrieve_body( $request );
			set_transient( 'aioseo_admin_help_docs', $docs, $aioseoAdminHelpDocsCacheTime );
		}
		return $aioseoAdminHelpDocs;
	}
}