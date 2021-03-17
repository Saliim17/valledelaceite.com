<?php
namespace AIOSEO\Plugin\Common\Admin;

use AIOSEO\Plugin\Common\Models;
use AIOSEO\Plugin\Common\Migration;

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class Admin {
	/**
	 * The page slug for the sidebar.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $pageSlug = 'aioseo';

	/**
	 * Sidebar menu name.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public $menuName = 'All in One SEO';

	/**
	 * An array of pages for the admin.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $pages = [];

	/**
	 * An array of items to add to the admin bar.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $adminBarMenuItems = [];

	/**
	 * Construct method.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		$this->pages = [
			$this->pageSlug            => [
				'menu_title' => esc_html__( 'Dashboard', 'all-in-one-seo-pack' ),
				'capability' => apply_filters( 'aioseo_manage_seo', 'aioseo_manage_seo' ),
				'parent'     => $this->pageSlug
			],
			'aioseo-settings'          => [
				'menu_title' => esc_html__( 'General Settings', 'all-in-one-seo-pack' ),
				'capability' => 'aioseo_general_settings',
				'parent'     => $this->pageSlug
			],
			'aioseo-search-appearance' => [
				'menu_title' => esc_html__( 'Search Appearance', 'all-in-one-seo-pack' ),
				'capability' => 'aioseo_search_appearance_settings',
				'parent'     => $this->pageSlug
			],
			'aioseo-social-networks'   => [
				'menu_title' => esc_html__( 'Social Networks', 'all-in-one-seo-pack' ),
				'capability' => 'aioseo_social_networks_settings',
				'parent'     => $this->pageSlug
			],
			'aioseo-sitemaps'          => [
				'menu_title' => esc_html__( 'Sitemaps', 'all-in-one-seo-pack' ),
				'capability' => 'aioseo_sitemap_settings',
				'parent'     => $this->pageSlug
			],
			// 'aioseo-internal-links'    => [
			//  'menu_title' => esc_html__( 'Internal Links', 'all-in-one-seo-pack' ),
			//  'capability' => 'aioseo_internal_links_settings',
			//  'parent'     => $this->pageSlug
			// ],
			// 'aioseo-redirects'         => [
			//  'menu_title' => esc_html__( 'Redirects', 'all-in-one-seo-pack' ),
			//  'capability' => 'aioseo_redirects_settings',
			//  'parent'     => $this->pageSlug
			// ],
			'aioseo-local-seo'         => [
				'menu_title' => esc_html__( 'Local SEO', 'all-in-one-seo-pack' ),
				'capability' => 'aioseo_local_seo_settings',
				'parent'     => $this->pageSlug
			],
			'aioseo-seo-analysis'      => [
				'menu_title' => esc_html__( 'SEO Analysis', 'all-in-one-seo-pack' ),
				'capability' => 'aioseo_seo_analysis_settings',
				'parent'     => $this->pageSlug
			],
			'aioseo-tools'             => [
				'menu_title' => esc_html__( 'Tools', 'all-in-one-seo-pack' ),
				'capability' => 'aioseo_tools_settings',
				'parent'     => $this->pageSlug
			],
			'aioseo-feature-manager'   => [
				'menu_title' => esc_html__( 'Feature Manager', 'all-in-one-seo-pack' ),
				'capability' => 'aioseo_feature_manager_settings',
				'parent'     => $this->pageSlug
			],
			'aioseo-monsterinsights'   => [
				'menu_title' => esc_html__( 'Analytics', 'all-in-one-seo-pack' ),
				'capability' => apply_filters( 'aioseo_manage_seo', 'aioseo_manage_seo' ),
				'parent'     => 'aioseo-monsterinsights'
			],
			'aioseo-about'             => [
				'menu_title' => esc_html__( 'About Us', 'all-in-one-seo-pack' ),
				'capability' => apply_filters( 'aioseo_manage_seo', 'aioseo_manage_seo' ),
				'parent'     => $this->pageSlug
			]
		];

		add_action( 'sanitize_comment_cookies', [ $this, 'init' ], 20 );

		$this->setupWizard = new SetupWizard();
	}

	/**
	 * Initialize the admin.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function init() {
		// Add the admin bar menu.
		if ( is_user_logged_in() && current_user_can( 'aioseo_manage_seo' ) && ( ! is_multisite() || ! is_network_admin() ) ) {
			add_action( 'admin_bar_menu', [ $this, 'adminBarMenu' ], 1000 );
		}

		if ( is_admin() ) {
			// Add the menu to the sidebar.
			add_action( 'admin_menu', [ $this, 'addMenu' ] );
			if ( is_multisite() ) {
				add_action( 'network_admin_menu', [ $this, 'addRobotsMenu' ] );
			}

			// Add the columns to page/posts.
			add_action( 'current_screen', [ $this, 'addPostColumns' ], 1 );

			// Add Score to Publish metabox.
			add_action( 'post_submitbox_misc_actions', [ $this, 'addPublishScore' ] );

			add_action( 'admin_init', [ $this, 'addPluginScripts' ] );

			add_action( 'wp_enqueue_editor', [ $this, 'addClassicLinkFormatScript' ], 999999 );

			global $wp_version;
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			if ( version_compare( $wp_version, '5.3', '>=' ) || is_plugin_active( 'gutenberg/gutenberg.php' ) ) {
				add_action( 'admin_init', [ $this, 'addGutenbergLinkFormatScript' ] );
				add_action( 'enqueue_block_editor_assets', function() {
					wp_enqueue_script( 'aioseo-link-format' );
				} );
			}
		}
	}

	/**
	 * Enqueues the plugins script.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addPluginScripts() {
		global $pagenow;

		if ( 'plugins.php' !== $pagenow ) {
			return;
		}

		aioseo()->helpers->enqueueScript(
			'aioseo-plugins',
			'js/plugins.js',
			false
		);

		wp_localize_script(
			'aioseo-plugins',
			'aioseoPlugins',
			[
				'basename' => AIOSEO_PLUGIN_BASENAME
			]
		);
	}

	/**
	 * Enqueues our link format for the Classic Editor.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addClassicLinkFormatScript() {
		wp_deregister_script( 'wplink' );

		wp_enqueue_script(
			'wplink',
			aioseo()->helpers->getScriptUrl( 'js/link-format-classic.js', false ),
			[ 'jquery', 'wp-a11y' ],
			aioseo()->version,
			true
		);

		wp_localize_script(
			'wplink',
			'aioseoL10n',
			[
				'update'         => esc_html__( 'Update', 'all-in-one-seo-pack' ),
				'save'           => esc_html__( 'Add Link', 'all-in-one-seo-pack' ),
				'noTitle'        => esc_html__( '(no title)', 'all-in-one-seo-pack' ),
				'labelTitle'     => esc_html__( 'Title', 'all-in-one-seo-pack' ),
				'noMatchesFound' => esc_html__( 'No results found.', 'all-in-one-seo-pack' ),
				'linkInserted'   => esc_html__( 'Link has been inserted.', 'all-in-one-seo-pack' ),
				// Translators: 1 - HTML whitespace character, 2 - Opening HTML code tag, 3 - Closing HTML code tag.
				'noFollow'       => sprintf( esc_html__( '%1$sAdd %2$srel="nofollow"%3$s to link', 'all-in-one-seo-pack' ), '&nbsp;', '<code>', '</code>' ),
				// Translators: 1 - HTML whitespace character, 2 - Opening HTML code tag, 3 - Closing HTML code tag.
				'sponsored'      => sprintf( esc_html__( '%1$sAdd %2$srel="sponsored"%3$s to link', 'all-in-one-seo-pack' ), '&nbsp;', '<code>', '</code>' ),
				// Translators: 1 - HTML whitespace character, 2 - Opening HTML code tag, 3 - Closing HTML code tag.
				'ugc'            => sprintf( esc_html__( '%1$sAdd %2$srel="UGC"%3$s to link', 'all-in-one-seo-pack' ), '&nbsp;', '<code>', '</code>' ),
			]
		);
	}

	/**
	 * Registers our link format for the Block Editor.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addGutenbergLinkFormatScript() {
		$linkFormat = 'block';
		if ( is_plugin_active( 'gutenberg/gutenberg.php' ) ) {
			$data = get_plugin_data( ABSPATH . 'wp-content/plugins/gutenberg/gutenberg.php', false, false );
			if ( version_compare( $data['Version'], '7.4.0', '<' ) ) {
				$linkFormat = 'block-old';
			}
		} else {
			if ( version_compare( get_bloginfo( 'version' ), '5.4', '<' ) ) {
				$linkFormat = 'block-old';
			}
		}

		wp_register_script(
			'aioseo-link-format',
			aioseo()->helpers->getScriptUrl( "js/link-format-$linkFormat.js", false ),
			[
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-plugins',
				'wp-components',
				'wp-edit-post',
				'wp-api',
				'wp-editor',
				'wp-hooks',
				'lodash'
			],
			aioseo()->version,
			true
		);
	}

	/**
	 * Adds All in One SEO to the Admin Bar.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function adminBarMenu() {
		if ( false === apply_filters( 'aioseo_show_in_admin_bar', true ) ) {
			// API filter hook to disable showing SEO in admin bar.
			return;
		}

		$count = count( Models\Notification::getAllActiveNotifications() );
		$count = 10 > $count ? $count : '!';
		$count = $count ? '<div class="aioseo-menu-notification-counter">' . $count . '</div>' : '';

		$this->adminBarMenuItems[] = [
			'id'    => 'aioseo-main',
			'title' => '<div class="ab-item aioseo-logo svg"></div><span class="text">' . esc_html__( 'SEO', 'all-in-one-seo-pack' ) . '</span>' . wp_kses_post( $count ),
			'href'  => esc_url( admin_url( 'admin.php?page=aioseo' ) )
		];

		if ( $count ) {
			$this->adminBarMenuItems[] = [
				'parent' => 'aioseo-main',
				'id'     => 'aioseo-notifications',
				'title'  => esc_html__( 'Notifications', 'all-in-one-seo-pack' ) . ' <div class="aioseo-menu-notification-indicator"></div>',
				'href'   => admin_url( 'admin.php?page=aioseo&notifications=true' ),
			];
		}

		if ( ! is_admin() ) {
			$this->addPageAnalyzerMenuItems();
		}

		$this->addSettingsMenuItems();
		$this->addPostMenuItems();

		// Add the menu bar items.
		$this->addAdminBarMenuItems();
	}

	/**
	 * Actually adds the menu items to the admin bar.
	 *
	 * @return void
	 */
	protected function addAdminBarMenuItems() {
		global $wp_admin_bar;
		foreach ( $this->adminBarMenuItems as $key => $item ) {
			$wp_admin_bar->add_menu( $item );
		}
	}

	/**
	 * Adds the Analyze this Page menu item to the admin bar.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addPageAnalyzerMenuItems() {
		global $wp;
		$url = home_url( $wp->request );

		if ( ! $url ) {
			return;
		}

		$this->adminBarMenuItems[] = [
			'id'     => 'aioseo-analyze-page',
			'parent' => 'aioseo-main',
			'title'  => esc_html__( 'Analyze this page', 'all-in-one-seo-pack' ),
		];

		$url = urlencode( $url );

		$submenuItems = [
			[
				'id'    => 'aioseo-analyze-page-inlinks',
				'title' => esc_html__( 'Check links to this URL', 'all-in-one-seo-pack' ),
				'href'  => 'https://search.google.com/search-console/links/drilldown?resource_id=' . urlencode( get_option( 'siteurl' ) ) . '&type=EXTERNAL&target=' . $url . '&domain=',
			],
			[
				'id'    => 'aioseo-analyze-page-cache',
				'title' => esc_html__( 'Check Google Cache', 'all-in-one-seo-pack' ),
				'href'  => '//webcache.googleusercontent.com/search?strip=1&q=cache:' . $url,
			],
			[
				'id'    => 'aioseo-analyze-page-structureddata',
				'title' => esc_html__( 'Google Structured Data Test', 'all-in-one-seo-pack' ),
				'href'  => 'https://search.google.com/test/rich-results?url=' . $url,
			],
			[
				'id'    => 'aioseo-analyze-page-facebookdebug',
				'title' => esc_html__( 'Facebook Debugger', 'all-in-one-seo-pack' ),
				'href'  => 'https://developers.facebook.com/tools/debug/?q=' . $url,
			],
			[
				'id'    => 'aioseo-analyze-page-pinterestvalidator',
				'title' => esc_html__( 'Pinterest Rich Pins Validator', 'all-in-one-seo-pack' ),
				'href'  => 'https://developers.pinterest.com/tools/url-debugger/?link=' . $url,
			],
			[
				'id'    => 'aioseo-analyze-page-htmlvalidation',
				'title' => esc_html__( 'HTML Validator', 'all-in-one-seo-pack' ),
				'href'  => '//validator.w3.org/check?uri=' . $url,
			],
			[
				'id'    => 'aioseo-analyze-page-cssvalidation',
				'title' => esc_html__( 'CSS Validator', 'all-in-one-seo-pack' ),
				'href'  => '//jigsaw.w3.org/css-validator/validator?uri=' . $url,
			],
			[
				'id'    => 'aioseo-analyze-page-pagespeed',
				'title' => esc_html__( 'Google Page Speed Test', 'all-in-one-seo-pack' ),
				'href'  => '//developers.google.com/speed/pagespeed/insights/?url=' . $url,
			],
			[
				'id'    => 'aioseo-analyze-page-google-mobile-friendly',
				'title' => esc_html__( 'Mobile-Friendly Test', 'all-in-one-seo-pack' ),
				'href'  => 'https://www.google.com/webmasters/tools/mobile-friendly/?url=' . $url,
			],
			[
				'id'    => 'aioseo-external-tools-linkedin-post-inspector',
				'title' => esc_html__( 'LinkedIn Post Inspector', 'all-in-one-seo-pack' ),
				'href'  => "https://www.linkedin.com/post-inspector/inspect/$url"
			]
		];

		foreach ( $submenuItems as $item ) {
			$this->adminBarMenuItems[] = [
				'parent' => 'aioseo-analyze-page',
				'id'     => $item['id'],
				'title'  => $item['title'],
				'href'   => $item['href'],
				'meta'   => [ 'target' => '_blank' ],
			];
		}
	}

	/**
	 * Adds the current post menu items to the admin bar.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function addPostMenuItems() {
		$blogPage = aioseo()->helpers->getBlogPage();
		if ( ! $blogPage ) {
			return;
		}

		$addMenu = $this->getAdminBarMenuData( $blogPage );
		if ( ! empty( $addMenu ) ) {
			$this->adminBarMenuItems[] = [
				'id'     => 'aioseo-edit-' . $addMenu['id'],
				'parent' => 'aioseo-main',
				'title'  => esc_html__( 'Edit SEO', 'all-in-one-seo-pack' ),
				'href'   => $addMenu['link'],
			];
		}
	}

	/**
	 * Add the settings items to the menu bar.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function addSettingsMenuItems() {
		if ( ! is_admin() ) {
			$this->adminBarMenuItems[] = [
				'id'     => 'aioseo-settings-main',
				'parent' => 'aioseo-main',
				// Translators: This is an action link users can click to open the General Settings menu.
				'title'  => esc_html__( 'SEO Settings', 'all-in-one-seo-pack' )
			];
		}

		$parent = is_admin() ? 'aioseo-main' : 'aioseo-settings-main';
		foreach ( $this->pages as $id => $page ) {
			// Remove the analytics menu.
			if ( 'aioseo-monsterinsights' === $id ) {
				continue;
			}

			$this->adminBarMenuItems[] = [
				'id'     => $id,
				'parent' => $parent,
				'title'  => $page['menu_title'],
				'href'   => esc_url( admin_url( 'admin.php?page=' . $id ) )
			];
		}
	}

	/**
	 * Retreive data to build the admin bar.
	 * @since 4.0.0
	 *
	 * @param  WP_Post $post The post object.
	 * @return array         An array of data to build a menu link.
	 */
	protected function getAdminBarMenuData( $post ) {
		// Don't show if we're on the home page and the home page is the latest posts.
		if ( ! is_home() || ( ! is_front_page() && ! is_home() ) ) {
			global $wp_the_query;
			$currentObject = $wp_the_query->get_queried_object();

			if ( is_singular() ) {
				if ( ! empty( $currentObject ) && ! empty( $currentObject->post_type ) ) {
					// Try the main query.
					$editPostLink = get_edit_post_link( $currentObject->ID );
					return [
						'id'   => $currentObject->ID,
						'link' => $editPostLink . '#aioseo'
					];
				} else {
					// Try the post object.
					return [
						'id'   => $post->ID,
						'link' => get_edit_post_link( $post->ID ) . '#aioseo'
					];
				}
			}
		}
	}

	/**
	 * Add the menu inside of WordPress.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addMenu() {
		$this->addMainMenu();

		foreach ( $this->pages as $slug => $page ) {
			$hook = add_submenu_page(
				$page['parent'],
				! empty( $page['page_title'] ) ? $page['page_title'] : $page['menu_title'],
				$page['menu_title'],
				$page['capability'],
				$slug,
				[ $this, 'page' ]
			);
			add_action( "load-{$hook}", [ $this, 'hooks' ] );

			if ( ! current_user_can( 'aioseo_admin' ) ) {
				remove_submenu_page( 'aioseo', 'aioseo' );
			}
		}
	}

	/**
	 * Add the robots only menu inside of the WordPress network admin.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addRobotsMenu() {
		$this->addMainMenu( 'aioseo-tools' );

		$page = $this->pages['aioseo-tools'];
		$hook = add_submenu_page(
			$page['parent'],
			! empty( $page['page_title'] ) ? $page['page_title'] : $page['menu_title'],
			$page['menu_title'],
			$page['capability'],
			'aioseo-tools',
			[ $this, 'page' ]
		);
		add_action( "load-{$hook}", [ $this, 'hooks' ] );
	}

	/**
	 * Add the main menu.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $slug which slug to use.
	 * @return void
	 */
	private function addMainMenu( $slug = 'aioseo' ) {
		add_menu_page(
			$this->menuName,
			$this->menuName,
			apply_filters( 'aioseo_manage_seo', 'aioseo_manage_seo' ),
			$slug,
			'__return_true',
			'data:image/svg+xml;base64,' . base64_encode( aioseo()->helpers->logo( 16, 16, '#A0A5AA' ) ),
			'80.01234567890'
		);
	}

	/**
	 * Output the HTML for the page.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function page() {
		echo '<div id="aioseo-app"></div>';
	}

	/**
	 * Hooks for loading our pages.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function hooks() {
		$currentScreen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		global $admin_page_hooks;

		if ( ! is_object( $currentScreen ) || empty( $currentScreen->id ) || empty( $admin_page_hooks ) ) {
			return;
		}

		$pages = [
			'dashboard',
			'settings',
			'search-appearance',
			'social-networks',
			'sitemaps',
			'internal-links',
			'redirects',
			'local-seo',
			'seo-analysis',
			'tools',
			'feature-manager',
			'monsterinsights',
			'about'
		];

		foreach ( $pages as $page ) {
			$addScripts = false;

			if ( 'toplevel_page_aioseo' === $currentScreen->id ) {
				$addScripts = true;
			}

			if ( ! empty( $admin_page_hooks['aioseo'] ) && $currentScreen->id === $admin_page_hooks['aioseo'] ) {
				$addScripts = true;
			}

			if ( strpos( $currentScreen->id, 'aioseo-' . $page ) !== false ) {
				$addScripts = true;
			}

			if ( ! $addScripts ) {
				continue;
			}

			if ( 'tools' === $page ) {
				$this->checkAdminQueryArgs();
			}

			// Redirect our Analytics page to the appropriate plugin page.
			if ( 'monsterinsights' === $page ) {

				$pluginData = aioseo()->helpers->getPluginData();

				if (
					(
						$pluginData['miLite']['activated'] ||
						$pluginData['miPro']['activated']
					) &&
					function_exists( 'MonsterInsights' ) &&
					function_exists( 'monsterinsights_get_ua' )
				) {
					if ( (bool) monsterinsights_get_ua() ) {
						wp_safe_redirect( $pluginData['miLite']['adminUrl'] );
						exit;
					}
				}

				if (
					(
						$pluginData['emLite']['activated'] ||
						$pluginData['emPro']['activated']
					) &&
					function_exists( 'ExactMetrics' ) &&
					function_exists( 'exactmetrics_get_ua' )
				) {
					if ( (bool) exactmetrics_get_ua() ) {
						wp_safe_redirect( $pluginData['emLite']['adminUrl'] );
						exit;
					}
				}
			}

			// We don't want any plugin adding notices to our screens. Let's clear them out here.
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );

			// Scripts.
			aioseo()->helpers->enqueueScript(
				'aioseo-vendors',
				'js/chunk-vendors.js'
			);
			aioseo()->helpers->enqueueScript(
				'aioseo-common',
				'js/chunk-common.js'
			);
			aioseo()->helpers->enqueueScript(
				'aioseo-' . $page . '-script',
				'js/' . $page . '.js'
			);

			// Styles.
			$rtl = is_rtl() ? '.rtl' : '';
			aioseo()->helpers->enqueueStyle(
				'aioseo-vendors',
				"css/chunk-vendors$rtl.css"
			);
			aioseo()->helpers->enqueueStyle(
				'aioseo-common',
				"css/chunk-common$rtl.css"
			);
			// aioseo()->helpers->enqueueStyle(
			//  'aioseo-' . $page . '-style',
			//  'css/' . $page . $rtl . '.css'
			// );
			// aioseo()->helpers->enqueueStyle(
			//  'aioseo-' . $page . '-vendors-style',
			//  'css/chunk-' . $page . $rtl . '-vendors.css'
			// );

			wp_localize_script(
				'aioseo-' . $page . '-script',
				'aioseo',
				aioseo()->helpers->getVueData( $page )
			);

			add_action( 'admin_footer_text', [ $this, 'addFooterText' ] );

			// Only enqueue the media library if we need it in our module
			if ( in_array( $page, [
				'social-networks',
				'search-appearance',
				'local-seo'
			], true ) ) {
				wp_enqueue_media();
			}

			break;
		}
	}

	/**
	 * Add footer text to the WordPress admin screens.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addFooterText() {
		$linkText = esc_html__( 'Give us a 5-star rating!', 'all-in-one-seo-pack' );
		$href     = 'https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews/?filter=5#new-post';

		$link1 = sprintf(
			'<a href="%1$s" target="_blank" title="%2$s">&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
			$href,
			$linkText
		);

		$link2 = sprintf(
			'<a href="%1$s" target="_blank" title="%2$s">WordPress.org</a>',
			$href,
			$linkText
		);

		printf(
			// Translators: 1 - The plugin name ("All in One SEO"), - 2 - This placeholder will be replaced with star icons, - 3 - "WordPress.org" - 4 - The plugin name ("All in One SEO").
			esc_html__( 'Please rate %1$s %2$s on %3$s to help us spread the word. Thank you!', 'all-in-one-seo-pack' ),
			sprintf( '<strong>%1$s</strong>', esc_html( AIOSEO_PLUGIN_NAME ) ),
			wp_kses_post( $link1 ),
			wp_kses_post( $link2 )
		);
	}

	/**
	 * Check to get if the screen should be shown.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public function isAllowedScreen( $screen, $postType ) {
		if ( 'type' === $postType ) {
			$postType = '_aioseo_type';
		}
		if ( 'edit' === $screen || 'upload' === $screen ) {
			if ( aioseo()->options->advanced->postTypes->all ) {
				return true;
			}

			$postTypes = aioseo()->options->advanced->postTypes->included;
			if ( in_array( $postType, $postTypes, true ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Adds the columns to the page/post types.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addPostColumns() {
		$screen = get_current_screen();
		if ( $this->isAllowedScreen( $screen->base, $screen->post_type ) ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueuePostsScripts' ] );

			if ( 'product' === $screen->post_type ) {
				add_filter( 'manage_edit-product_columns', [ $this, 'postColumns' ] );
				add_action( 'manage_posts_custom_column', [ $this, 'renderColumn' ], 10, 2 );
			} elseif ( 'attachment' === $screen->post_type ) {
				$enabled = apply_filters( 'aioseo_image_seo_media_columns', true );

				if ( ! $enabled ) {
					return;
				}

				add_filter( 'manage_media_columns', [ $this, 'postColumns' ] );
				add_action( 'manage_media_custom_column', [ $this, 'renderColumn' ], 10, 2 );
			} else {
				add_filter( "manage_edit-{$screen->post_type}_columns", [ $this, 'postColumns' ] );
				add_action( "manage_{$screen->post_type}_posts_custom_column", [ $this, 'renderColumn' ], 10, 2 );
			}
		}
	}

	/**
	 * Enqueues the JS/CSS for the page/posts table page.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function enqueuePostsScripts() {
		// Scripts.
		aioseo()->helpers->enqueueScript(
			'aioseo-posts-table',
			'js/posts-table.js'
		);
		aioseo()->helpers->enqueueScript(
			'aioseo-vendors',
			'js/chunk-vendors.js'
		);
		aioseo()->helpers->enqueueScript(
			'aioseo-common',
			'js/chunk-common.js'
		);

		$data          = aioseo()->helpers->getVueData();
		$data['posts'] = [];
		$data['terms'] = [];
		wp_localize_script(
			'aioseo-posts-table',
			'aioseo',
			$data
		);

		// Styles.
		$rtl = is_rtl() ? '.rtl' : '';
		aioseo()->helpers->enqueueStyle(
			'aioseo-vendors',
			"css/chunk-vendors$rtl.css"
		);
		aioseo()->helpers->enqueueStyle(
			'aioseo-common',
			"css/chunk-common$rtl.css"
		);
		aioseo()->helpers->enqueueStyle(
			'aioseo-posts-table-style',
			"css/posts-table$rtl.css"
		);
	}

	/**
	 * Adds columns to the page/post tables in the admin.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $columns The columns we are adding ours onto.
	 * @return array          The modified columns.
	 */
	public function postColumns( $columns ) {
		$pageAnalysisCapability    = aioseo()->access->hasCapability( 'aioseo_page_analysis' );
		$generalSettingsCapability = aioseo()->access->hasCapability( 'aioseo_page_general_settings' );
		if (
			! current_user_can( 'aioseo_manage_seo' ) ||
			( empty( $pageAnalysisCapability ) && empty( $generalSettingsCapability ) )
		) {
			return $columns;
		}

		// Translators: 1 - The short plugin name ("AIOSEO").
		$columns['aioseo-details'] = sprintf( esc_html__( '%1$s Details', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_SHORT_NAME );

		return $columns;
	}

	/**
	 * Renders the column in the page/post table.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $columnName The column name.
	 * @param  int    $postId     The current rows, post id.
	 * @return void
	 */
	public function renderColumn( $columnName, $postId ) {
		if ( ! current_user_can( 'edit_post', $postId ) && ! current_user_can( 'aioseo_manage_seo' ) ) {
			return;
		}
		if ( 'aioseo-details' === $columnName ) {
			// Add this column/post to the localized array.
			global $wp_scripts;

			$data = $wp_scripts->get_data( 'aioseo-posts-table', 'data' );

			if ( ! is_array( $data ) ) {
				$data = json_decode( str_replace( 'var aioseo = ', '', substr( $data, 0, -1 ) ), true );
			}

			$nonce   = wp_create_nonce( "aioseo_meta_{$columnName}_{$postId}" );
			$posts   = $data['posts'];
			$thePost = Models\Post::getPost( $postId );
			$posts[] = [
				'id'                 => $postId,
				'columnName'         => $columnName,
				'nonce'              => $nonce,
				'title'              => $thePost->title,
				'titleParsed'        => aioseo()->meta->title->getPostTitle( $postId ),
				'defaultTitle'       => aioseo()->meta->title->getPostTypeTitle( get_post_type( $postId ) ),
				'description'        => $thePost->description,
				'descriptionParsed'  => aioseo()->meta->description->getPostDescription( $postId ),
				'defaultDescription' => aioseo()->meta->description->getPostTypeDescription( get_post_type( $postId ) ),
				'value'              => (int) $thePost->seo_score,
				'showMedia'          => false,
				'isSpecialPage'      => aioseo()->helpers->isSpecialPage( $postId )
			];

			$data['posts'] = $posts;

			$wp_scripts->add_data( 'aioseo-posts-table', 'data', '' );
			wp_localize_script( 'aioseo-posts-table', 'aioseo', $data );

			require( AIOSEO_DIR . '/app/Common/Views/admin/posts/columns.php' );
		}
	}

	/**
	 * Renders the column in the media/attachment table.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $columnName The column name.
	 * @param  int    $postId     The current rows, post id.
	 * @return void
	 */
	public function renderMediaColumn( $columnName, $postId ) {
		$screen = get_current_screen();
		if ( $this->isAllowedScreen( $screen->base, $screen->post_type ) ) {
			$this->renderColumn( $columnName, $postId );
		}
		return null;
	}

	/**
	 * Renders the Score button in the publish metabox.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post $postObj The post object.
	 * @return void
	 */
	public function addPublishScore( $postObj ) {
		$pageAnalysisCapability = aioseo()->access->hasCapability( 'aioseo_page_analysis' );
		if ( empty( $pageAnalysisCapability ) || ( aioseo()->helpers->isWooCommerceActive() && 'product' === $postObj->post_type ) ) {
			return;
		}
		$postTypes     = aioseo()->helpers->getPublicPostTypes();
		$showTruSeo    = aioseo()->options->advanced->truSeo;
		$isSpecialPage = aioseo()->helpers->isSpecialPage( $postObj->ID );

		$postTypesMB = [];
		foreach ( $postTypes as $pt ) {
			if ( class_exists( 'bbPress' ) ) {
				if (
					'attachment' !== $pt['name'] &&
					'forum' !== $pt['name'] &&
					'topic' !== $pt['name'] &&
					'reply' !== $pt['name']
				) {
					$postTypesMB[] = $pt['name'];
				}
			} else {
				if ( 'attachment' !== $pt['name'] ) {
					$postTypesMB[] = $pt['name'];
				}
			}
		}

		if ( in_array( $postObj->post_type, $postTypesMB, true ) && $showTruSeo && ! $isSpecialPage ) {
			$score = (int) Models\Post::getPost( $postObj->ID )->seo_score;
			$path  = 'M10 20C15.5228 20 20 15.5228 20 10C20 4.47715 15.5228 0 10 0C4.47716 0 0 4.47715 0 10C0 15.5228 4.47716 20 10 20ZM8.40767 3.65998C8.27222 3.45353 8.02129 3.357 7.79121 3.43828C7.52913 3.53087 7.27279 3.63976 7.02373 3.76429C6.80511 3.87361 6.69542 4.12332 6.74355 4.36686L6.91501 5.23457C6.95914 5.45792 6.86801 5.68459 6.69498 5.82859C6.42152 6.05617 6.16906 6.31347 5.94287 6.59826C5.80229 6.77526 5.58046 6.86908 5.36142 6.82484L4.51082 6.653C4.27186 6.60473 4.02744 6.71767 3.92115 6.94133C3.86111 7.06769 3.80444 7.19669 3.75129 7.32826C3.69815 7.45983 3.64929 7.59212 3.60464 7.72495C3.52562 7.96007 3.62107 8.21596 3.82396 8.35351L4.54621 8.84316C4.73219 8.96925 4.82481 9.19531 4.80234 9.42199C4.7662 9.78671 4.76767 10.1508 4.80457 10.5089C4.82791 10.7355 4.73605 10.9619 4.55052 11.0886L3.82966 11.5811C3.62734 11.7193 3.53274 11.9753 3.61239 12.2101C3.70314 12.4775 3.80985 12.7391 3.93188 12.9932C4.03901 13.2163 4.28373 13.3282 4.5224 13.2791L5.37279 13.1042C5.59165 13.0591 5.8138 13.1521 5.95491 13.3287C6.17794 13.6077 6.43009 13.8653 6.70918 14.0961C6.88264 14.2396 6.97459 14.4659 6.93122 14.6894L6.76282 15.5574C6.71551 15.8013 6.8262 16.0507 7.04538 16.1591C7.16921 16.2204 7.29563 16.2782 7.42457 16.3324C7.55352 16.3867 7.68316 16.4365 7.81334 16.4821C8.19418 16.6154 8.72721 16.1383 9.1213 15.7855C9.31563 15.6116 9.4355 15.3654 9.43677 15.1018C9.43677 15.1004 9.43678 15.099 9.43678 15.0976L9.43677 13.6462C9.43677 13.6308 9.43736 13.6155 9.43852 13.6004C8.27454 13.3165 7.40918 12.248 7.40918 10.9732V9.43198C7.40918 9.31483 7.50224 9.21986 7.61706 9.21986H8.338V7.70343C8.338 7.49405 8.50433 7.32432 8.70952 7.32432C8.9147 7.32432 9.08105 7.49405 9.08105 7.70343V9.21986H11.0316V7.70343C11.0316 7.49405 11.1979 7.32432 11.4031 7.32432C11.6083 7.32432 11.7746 7.49405 11.7746 7.70343V9.21986H12.4956C12.6104 9.21986 12.7034 9.31483 12.7034 9.43198V10.9732C12.7034 12.2883 11.7825 13.3838 10.5628 13.625C10.5631 13.632 10.5632 13.6391 10.5632 13.6462L10.5632 15.0914C10.5632 15.36 10.6867 15.6107 10.8868 15.7853C11.2879 16.1351 11.8302 16.6079 12.2088 16.4742C12.4708 16.3816 12.7272 16.2727 12.9762 16.1482C13.1949 16.0389 13.3046 15.7891 13.2564 15.5456L13.085 14.6779C13.0408 14.4545 13.132 14.2278 13.305 14.0838C13.5785 13.8563 13.8309 13.599 14.0571 13.3142C14.1977 13.1372 14.4195 13.0434 14.6385 13.0876L15.4892 13.2595C15.7281 13.3077 15.9725 13.1948 16.0788 12.9711C16.1389 12.8448 16.1955 12.7158 16.2487 12.5842C16.3018 12.4526 16.3507 12.3204 16.3953 12.1875C16.4744 11.9524 16.3789 11.6965 16.176 11.559L15.4537 11.0693C15.2678 10.9432 15.1752 10.7171 15.1976 10.4905C15.2338 10.1258 15.2323 9.76167 15.1954 9.40357C15.1721 9.17699 15.2639 8.95062 15.4495 8.82387L16.1703 8.33141C16.3726 8.1932 16.4672 7.93715 16.3876 7.70238C16.2968 7.43495 16.1901 7.17337 16.0681 6.91924C15.961 6.69615 15.7162 6.58422 15.4776 6.63333L14.6272 6.8083C14.4083 6.85333 14.1862 6.76033 14.0451 6.58377C13.822 6.30474 13.5699 6.04713 13.2908 5.81632C13.1173 5.67287 13.0254 5.44652 13.0688 5.22301L13.2372 4.35503C13.2845 4.11121 13.1738 3.86179 12.9546 3.75334C12.8308 3.69208 12.7043 3.63424 12.5754 3.58002C12.4465 3.52579 12.3168 3.47593 12.1866 3.43037C11.9562 3.34974 11.7055 3.44713 11.5707 3.65416L11.0908 4.39115C10.9672 4.58093 10.7457 4.67543 10.5235 4.65251C10.1661 4.61563 9.80932 4.61712 9.45837 4.65477C9.23633 4.6786 9.01448 4.58486 8.89027 4.39554L8.40767 3.65998Z'; // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			?>
			<div class="misc-pub-section aioseo-score-settings">
				<svg viewBox="0 0 20 20" width="20" height="20" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd" d="<?php echo esc_attr( $path ); ?>" fill="#82878C" />
				</svg>
				<span>
					<?php
						// Translators: 1 - The short plugin name ("AIOSEO").
						echo sprintf( esc_html__( '%1$s Score', 'all-in-one-seo-pack' ), esc_html( AIOSEO_PLUGIN_SHORT_NAME ) );
					?>
				</span>
				<div id="aioseo-post-settings-sidebar-button" class="aioseo-score-button classic-editor <?php echo esc_attr( aioseo()->helpers->getScoreClass( $score ) ); ?>">
					<span id="aioseo-post-score"><?php echo esc_attr( $score . '/100' ); ?></span>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Checks the admin query args to run appropriate tasks.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function checkAdminQueryArgs() {
		// Migrate the post/term meta manually.
		if ( isset( $_GET['aioseo-v3-migrate-now'] ) ) {
			aioseo()->migration->oldOptions = ( new Migration\OldOptions() )->oldOptions;
			aioseo()->migration->meta->migratePostMeta();
		}

		// Redo the migration from the beginning.
		if ( isset( $_GET['aioseo-v3-migration'] ) && 'i-want-to-migrate' === wp_unslash( $_GET['aioseo-v3-migration'] ) ) { // phpcs:ignore HM.Security.ValidatedSanitizedInput.InputNotSanitized
			Migration\Helpers::redoMigration();
		}

		// Remove all AIOSEO transients.
		if ( isset( $_GET['aioseo-clear-cache'] ) ) {
			$table = aioseo()->db->db->options;
			aioseo()->db->db->query( "DELETE FROM {$table} WHERE option_name LIKE '\_transient\_aioseo\_%'" );
			aioseo()->db->db->query( "DELETE FROM {$table} WHERE option_name LIKE '\_transient\_timeout\_aioseo\_%'" );
		}

		$this->updateDeprecatedOptions();
	}

	/**
	 * Updates deprecated options.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function updateDeprecatedOptions() {
		// Check if the user is forcefully wanting to add a deprecated option.
		$allDeprecatedOptions = aioseo()->internalOptions->getAllDeprecatedOptions();
		$deprecatedOptions    = aioseo()->internalOptions->internal->deprecatedOptions;
		if ( isset( $_GET['aioseo-enable-option'] ) ) {
			$changed = false;
			foreach ( $allDeprecatedOptions as $deprecatedOption ) {
				if ( $deprecatedOption === $_GET['aioseo-enable-option'] && ! in_array( $deprecatedOption, $deprecatedOptions, true ) ) {
					$changed = true;
					array_push( $deprecatedOptions, $deprecatedOption );
				}
			}

			if ( $changed ) {
				aioseo()->internalOptions->internal->deprecatedOptions = array_values( $deprecatedOptions );
			}
		}

		if ( isset( $_GET['aioseo-disable-option'] ) ) {
			$changed = false;
			foreach ( $allDeprecatedOptions as $deprecatedOption ) {
				if ( $deprecatedOption === $_GET['aioseo-disable-option'] && in_array( $deprecatedOption, $deprecatedOptions, true ) ) {
					$changed = true;
					$key     = array_search( $deprecatedOption, $deprecatedOptions, true );
					unset( $deprecatedOptions[ $key ] );
				}
			}

			if ( $changed ) {
				aioseo()->internalOptions->internal->deprecatedOptions = array_values( $deprecatedOptions );
			}
		}
	}
}