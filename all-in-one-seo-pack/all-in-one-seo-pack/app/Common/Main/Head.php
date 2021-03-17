<?php
namespace AIOSEO\Plugin\Common\Main;

use AIOSEO\Plugin\Common\Meta;

/**
 * Outputs anything we need to the head of the site.
 *
 * @since 4.0.0
 */
class Head {

	/**
	 * The page title.
	 *
	 * @since 4.0.5
	 *
	 * @var string
	 */
	private static $pageTitle = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'addAnalytics' ] );
		add_action( 'after_setup_theme', [ $this, 'registerTitleHooks' ], 1000 );
		add_action( 'wp_head', [ $this, 'init' ], 1 );

		$this->analytics    = new GoogleAnalytics();
		$this->links        = new Meta\Links();
		$this->robots       = new Meta\Robots();
		$this->keywords     = new Meta\Keywords();
		$this->verification = new Meta\SiteVerification();
		$this->views        = [
			'meta'   => AIOSEO_DIR . '/app/Common/Views/main/meta.php',
			'social' => AIOSEO_DIR . '/app/Common/Views/main/social.php',
			'schema' => AIOSEO_DIR . '/app/Common/Views/main/schema.php'
		];
	}

	/**
	 * Adds analytics to the views if needed.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function addAnalytics() {
		if ( $this->analytics->canShowScript() ) {
			$this->views['analytics'] = AIOSEO_DIR . '/app/Common/Views/main/analytics.php';
		}
	}

	/**
	 * Registers our title hooks.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function registerTitleHooks() {
		if ( apply_filters( 'aioseo_disable', false ) || apply_filters( 'aioseo_disable_title_rewrites', false ) ) {
			return;
		}

		add_filter( 'pre_get_document_title', [ $this, 'getTitle' ], 99999 );
		add_filter( 'wp_title', [ $this, 'getTitle' ], 99999 );
		if ( ! current_theme_supports( 'title-tag' ) ) {
			add_action( 'template_redirect', [ $this, 'startOutputBuffering' ] );
			add_action( 'wp_footer', [ $this, 'rewriteTitle' ], -2 );
		}
	}

	/**
	 * Initializes the class.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function init() {
		$included = new Meta\Included();
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ! $included->isIncluded() ) {
			return;
		}

		$this->output();
	}

	/**
	 * Returns the page title.
	 *
	 * @since 4.0.5
	 *
	 * @param  string $wpTitle   The original page title from WordPress.
	 * @return string $pageTitle The page title.
	 */
	public function getTitle( $wpTitle = '' ) {
		if ( null !== self::$pageTitle ) {
			return self::$pageTitle;
		}
		self::$pageTitle = aioseo()->meta->title->filterPageTitle( $wpTitle );
		return self::$pageTitle;
	}

	/**
	 * Starts our output buffering.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function startOutputBuffering() {
		ob_start();
	}

	/**
	 * Rewrites the page title using output buffering.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function rewriteTitle() {
		$content   = ob_get_clean();
		$split     = preg_split( '#</head>#', $content );
		$head      = $split[0] . '</head>';
		$body      = $split[1];

		// Remove all existing title tags.
		$head = preg_replace( '#<title.*?\/title>#', '', $head );

		// Add the new title tag to our own comment block.
		$pageTitle = aioseo()->helpers->escapeRegexReplacement( $this->getTitle() );
		$head      = preg_replace( '#(<!--\sAll\sin\sOne\sSEO[a-zA-Z\s0-9.]+\s-->)#', "$1\r\n\t\t<title>$pageTitle</title>", $head, 1 );

		$content = $head . $body;
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * The output function itself.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function output() {
		remove_action( 'wp_head', 'rel_canonical' );

		echo "\n\t\t<!-- " . sprintf(
			'%1$s %2$s',
			esc_html( AIOSEO_PLUGIN_NAME ),
			aioseo()->version // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		) . " -->\n";

		foreach ( $this->views as $view ) {
			require_once( $view );
		}

		echo "\t\t<!-- " . esc_html( AIOSEO_PLUGIN_NAME ) . " -->\n\n";
	}
}