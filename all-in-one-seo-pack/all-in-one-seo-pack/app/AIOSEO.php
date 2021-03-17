<?php
namespace AIOSEO\Plugin {

	/**
	 * Main AIOSEO class.
	 *
	 * @since 4.0.0
	 */
	final class AIOSEO {
		/**
		 * Holds the instance of the plugin currently in use.
		 *
		 * @since 4.0.0
		 *
		 * @var AIOSEO\Plugin\AIOSEO
		 */
		private static $instance;

		/**
		 * Plugin version for enqueueing, etc.
		 * The value is retrieved from the AIOSEO_VERSION constant.
		 *
		 * @since 4.0.0
		 *
		 * @var string
		 */
		public $version = '';

		/**
		 * Paid returns true, free (Lite) returns false.
		 *
		 * @since 4.0.0
		 *
		 * @var boolean
		 */
		public $pro = false;

		/**
		 * Returns 'Pro' or 'Lite'.
		 *
		 * @since 4.0.0
		 *
		 * @var boolean
		 */
		public $versionPath = 'Lite';

		/**
		 * The AIOSEO options.
		 *
		 * @since 4.0.0
		 *
		 * @var array
		 */
		public $options = [];

		/**
		 * The WordPress filters to run.
		 *
		 * @since 4.0.0
		 *
		 * @var Filters
		 */
		private $filters = null;

		/**
		 * For usage tracking when enabled.
		 *
		 * @since 4.0.0
		 *
		 * @var Filters
		 */
		private $usage = null;

		/**
		 * For WP site health.
		 *
		 * @since 4.0.0
		 *
		 * @var Filters
		 */
		private $siteHealth = null;

		/**
		 * For auto updates.
		 *
		 * @since 4.0.0
		 *
		 * @var Filters
		 */
		private $autoUpdates = null;

		/**
		 * Main AIOSEO Instance.
		 *
		 * Insures that only one instance of AIOSEO exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 4.0.0
		 *
		 * @return AIOSEO The aioseo instance.
		 */
		public static function instance() {
			if ( null === self::$instance || ! self::$instance instanceof self ) {
				self::$instance = new self();

				// Plugin Slug - Determine plugin type and set slug accordingly.
				if (
					( ! defined( 'AIOSEO_DEV_VERSION' ) || 'pro' === AIOSEO_DEV_VERSION ) &&
					is_dir( plugin_dir_path( AIOSEO_FILE ) . 'app/Pro' )
				) {
					self::$instance->pro         = true;
					self::$instance->versionPath = 'Pro';
				}

				self::$instance->init();

				// Load our addons on the action right after plugins_loaded.
				add_action( 'sanitize_comment_cookies', [ self::$instance, 'loadAddons' ] );
			}

			return self::$instance;
		}

		/**
		 * Initialize All in One SEO!
		 *
		 * @return void
		 */
		private function init() {
			$this->constants();
			$this->includes();
			$this->preLoad();
			$this->load();
		}

		/**
		 * Setup plugin constants.
		 * All the path/URL related constants are defined in main plugin file.
		 *
		 * @since 4.0.0
		 *
		 * @return void
		 */
		private function constants() {
			$defaultHeaders = [
				'name'    => 'Plugin Name',
				'version' => 'Version',
			];

			$pluginData = get_file_data( AIOSEO_FILE, $defaultHeaders );

			$constants = [
				'AIOSEO_PLUGIN_BASENAME'   => plugin_basename( AIOSEO_FILE ),
				'AIOSEO_PLUGIN_NAME'       => $pluginData['name'],
				'AIOSEO_PLUGIN_SHORT_NAME' => 'AIOSEO',
				'AIOSEO_PLUGIN_URL'        => plugin_dir_url( AIOSEO_FILE ),
				'AIOSEO_VERSION'           => $pluginData['version'],
				'AIOSEO_MARKETING_URL'     => 'https://aioseo.com/',
				'AIOSEO_MARKETING_DOMAIN'  => 'aioseo.com'
			];

			foreach ( $constants as $constant => $value ) {
				if ( ! defined( $constant ) ) {
					define( $constant, $value );
				}
			}

			$this->version = AIOSEO_VERSION;
		}

		/**
		 * Including the new files with PHP 5.3 style.
		 *
		 * @since 4.0.0
		 *
		 * @return void
		 */
		private function includes() {
			$dependencies = [
				'/vendor/autoload.php',
				'/vendor/woocommerce/action-scheduler/action-scheduler.php',
			];

			foreach ( $dependencies as $path ) {
				if ( ! file_exists( AIOSEO_DIR . $path ) ) {
					// Something is not right.
					status_header( 500 );
					wp_die( esc_html__( 'Plugin is missing required dependencies. Please contact support for more information.', 'all-in-one-seo-pack' ) );
				}
				require AIOSEO_DIR . $path;
			}

			add_action( 'plugins_loaded', [ $this, 'actionScheduler' ], 10 );
		}

		/**
		 * Ensure our action scheduler tables are always set.
		 *
		 * @since 4.0.0
		 *
		 * @return void
		 */
		public function actionScheduler() {
			if ( class_exists( 'ActionScheduler' ) && class_exists( 'ActionScheduler_ListTable' ) ) {
				new Common\Utils\ActionScheduler(
					\ActionScheduler::store(),
					\ActionScheduler::logger(),
					\ActionScheduler::runner()
				);
			}
		}

		/**
		 * Runs before we load the plugin.
		 *
		 * @since 4.0.0
		 *
		 * @return void
		 */
		private function preLoad() {
			$this->db = new Common\Utils\Database();
		}

		/**
		 * Load our classes.
		 *
		 * @since 4.0.0
		 *
		 * @return void
		 */
		public function load() {
			// Load external translations if this is a Pro install.
			if ( $this->pro ) {
				$translations = new Pro\Main\Translations(
					'plugin',
					'all-in-one-seo-pack',
					'https://packages.translationspress.com/aioseo/all-in-one-seo-pack/packages.json'
				);
				$translations->init();

				$translations = new Pro\Main\Translations(
					'plugin',
					'aioseo-pro',
					'https://packages.translationspress.com/aioseo/aioseo-pro/packages.json'
				);
				$translations->init();
			}

			$this->helpers            = $this->pro ? new Pro\Utils\Helpers() : new Common\Utils\Helpers();
			$this->addons             = new Common\Utils\Addons();
			$this->tags               = $this->pro ? new Pro\Utils\Tags() : new Common\Utils\Tags();
			$this->badBotBlocker      = new Common\Tools\BadBotBlocker();
			$this->internalOptions    = $this->pro ? new Pro\Utils\InternalOptions() : new Lite\Utils\InternalOptions();
			$this->options            = $this->pro ? new Pro\Utils\Options() : new Lite\Utils\Options();
			$this->backup             = new Common\Utils\Backup();
			$this->access             = $this->pro ? new Pro\Utils\Access() : new Common\Utils\Access();
			$this->usage              = $this->pro ? new Pro\Admin\Usage() : new Lite\Admin\Usage();
			$this->siteHealth         = $this->pro ? new Pro\Admin\SiteHealth() : new Common\Admin\SiteHealth();
			$this->license            = $this->pro ? new Pro\Admin\License() : null;
			$this->autoUpdates        = $this->pro ? new Pro\Admin\AutoUpdates() : null;
			$this->updates            = $this->pro ? new Pro\Main\Updates() : new Common\Main\Updates();
			$this->meta               = $this->pro ? new Pro\Meta\Meta() : new Common\Meta\Meta();
			$this->social             = $this->pro ? new Pro\Social\Social() : new Common\Social\Social();
			$this->robotsTxt          = new Common\Tools\RobotsTxt();
			$this->htaccess           = new Common\Tools\Htaccess();
			$this->term               = $this->pro ? new Pro\Admin\Term() : null;
			$this->notices            = $this->pro ? new Pro\Admin\Notices\Notices() : new Lite\Admin\Notices\Notices();
			$this->admin              = $this->pro ? new Pro\Admin\Admin() : new Lite\Admin\Admin();
			$this->conflictingPlugins = $this->pro ? new Pro\Admin\ConflictingPlugins() : new Common\Admin\ConflictingPlugins();
			$this->migration          = $this->pro ? new Pro\Migration\Migration() : new Common\Migration\Migration();
			$this->importExport       = $this->pro ? new Pro\ImportExport\ImportExport() : new Common\ImportExport\ImportExport();
			$this->sitemap            = $this->pro ? new Pro\Sitemap\Sitemap() : new Common\Sitemap\Sitemap();

			if ( ! wp_doing_ajax() && ! wp_doing_cron() ) {
				$this->rss              = new Common\Rss();
				$this->main             = $this->pro ? new Pro\Main\Main() : new Common\Main\Main();
				$this->schema           = new Common\Schema\Schema();
				$this->head             = $this->pro ? new Pro\Main\Head() : new Common\Main\Head();
				$this->activate         = $this->pro ? new Pro\Main\Activate() : new Lite\Main\Activate();
				$this->filters          = $this->pro ? new Pro\Main\Filters() : new Lite\Main\Filters();
				$this->dashboard        = $this->pro ? new Pro\Admin\Dashboard() : new Common\Admin\Dashboard();
				$this->api              = $this->pro ? new Pro\Api\Api() : new Lite\Api\Api();
				$this->postSettings     = $this->pro ? new Pro\Admin\PostSettings() : new Lite\Admin\PostSettings();
				$this->filter           = new Common\Utils\Filter();
				$this->localBusinessSeo = new Common\Admin\LocalBusinessSeo();
				$this->help             = new Common\Help\Help();
			}

			if ( wp_doing_ajax() || wp_doing_cron() ) {
				return;
			}

			add_action( 'init', [ $this, 'loadInit' ], 999 );
		}

		/**
		 * Things that need to load after init.
		 *
		 * @since 4.0.0
		 *
		 * @return void
		 */
		public function loadInit() {
			$this->settings = new Common\Utils\VueSettings( '_aioseo_settings' );
			$this->sitemap->init();
			$this->sitemap->ping->init();

			$this->badBotBlocker->init();

			// We call this again to reset any post types/taxonomies that have not yet been set up.
			$this->options->refresh();
		}

		/**
		 * Loads our addons.
		 *
		 * Runs right after the plugins_loaded hook.
		 *
		 * @since 4.0.0
		 *
		 * @return void
		 */
		public function loadAddons() {
			do_action( 'aioseo_loaded' );
		}
	}
}

namespace {

	/**
	 * The function which returns the one AIOSEO instance.
	 *
	 * @since 4.0.0
	 *
	 * @return AIOSEO\Plugin\AIOSEO The instance.
	 */
	function aioseo() {
		return AIOSEO\Plugin\AIOSEO::instance();
	}
}