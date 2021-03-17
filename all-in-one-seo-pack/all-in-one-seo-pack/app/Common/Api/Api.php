<?php
namespace AIOSEO\Plugin\Common\Api;

/**
 * Api class for the admin.
 *
 * @since 4.0.0
 */
class Api {
	/**
	 * The REST API Namespace
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public $namespace = 'aioseo/v1';

	/**
	 * The routes we use in the rest API.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $routes = [
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		'GET'    => [
			'options' => [ 'callback' => [ 'Settings', 'getOptions' ] ],
			'ping'    => [ 'callback' => [ 'Ping', 'ping' ] ],
			'post'    => [ 'callback' => [ 'PostsTerms', 'getPostData' ] ],
			'tags'    => [ 'callback' => [ 'Tags', 'getTags' ] ]
		],
		'POST'   => [
			'htaccess'                                            => [ 'callback' => [ 'Tools', 'saveHtaccess' ], 'access' => 'aioseo_tools_settings' ],
			'post'                                                => [
				'callback' => [ 'PostsTerms', 'updatePosts' ],
				'access'   => [
					'aioseo_page_analysis',
					'aioseo_page_general_settings',
					'aioseo_page_advanced_settings',
					'aioseo_page_schema_settings',
					'aioseo_page_social_settings'
				]
			],
			'postscreen'                                          => [ 'callback' => [ 'PostsTerms', 'updatePostFromScreen' ], 'access' => 'aioseo_page_general_settings' ],
			'termscreen'                                          => [ 'callback' => [ 'PostsTerms', 'updateTermFromScreen' ], 'access' => 'aioseo_page_general_settings' ],
			'keyphrases'                                          => [ 'callback' => [ 'PostsTerms', 'updatePostKeyphrases' ], 'access' => 'aioseo_page_analysis' ],
			'analyze'                                             => [ 'callback' => [ 'Analyze', 'analyzeSite' ] ],
			'analyze/delete-site'                                 => [ 'callback' => [ 'Analyze', 'deleteSite' ], 'access' => 'aioseo_seo_analysis_settings' ],
			'clear-log'                                           => [ 'callback' => [ 'Tools', 'clearLog' ], 'access' => 'aioseo_tools_settings' ],
			'connect'                                             => [ 'callback' => [ 'Connect', 'saveConnectToken' ], 'access' => [ 'aioseo_general_settings', 'aioseo_setup_wizard' ] ],
			'connect-pro'                                         => [ 'callback' => [ 'Connect', 'processConnect' ], 'access' => [ 'aioseo_general_settings', 'aioseo_setup_wizard' ] ],
			'connect-url'                                         => [ 'callback' => [ 'Connect', 'getConnectUrl' ], 'access' => [ 'aioseo_general_settings', 'aioseo_setup_wizard' ] ],
			'backup'                                              => [ 'callback' => [ 'Tools', 'createBackup' ], 'access' => 'aioseo_tools_settings' ],
			'backup/restore'                                      => [ 'callback' => [ 'Tools', 'restoreBackup' ], 'access' => 'aioseo_tools_settings' ],
			'email-debug-info'                                    => [ 'callback' => [ 'Tools', 'emailDebugInfo' ], 'access' => 'aioseo_tools_settings' ],
			'migration/fix-blank-formats'                         => [ 'callback' => [ 'Migration', 'fixBlankFormats' ] ],

			'notification/blog-visibility-reminder'               => [ 'callback' => [ 'Notifications', 'blogVisibilityReminder' ] ],
			'notification/description-format-reminder'            => [ 'callback' => [ 'Notifications', 'descriptionFormatReminder' ] ],
			'notification/conflicting-plugins-reminder'           => [ 'callback' => [ 'Notifications', 'conflictingPluginsReminder' ] ],
			'notification/deprecated-filters-reminder'            => [ 'callback' => [ 'Notifications', 'deprecatedFiltersReminder' ] ],
			'notification/install-addons-reminder'                => [ 'callback' => [ 'Notifications', 'installAddonsReminder' ] ],
			'notification/install-aioseo-image-seo-reminder'      => [ 'callback' => [ 'Notifications', 'installImageSeoReminder' ] ],
			'notification/install-aioseo-local-business-reminder' => [ 'callback' => [ 'Notifications', 'installLocalBusinessReminder' ] ],
			'notification/install-aioseo-news-sitemap-reminder'   => [ 'callback' => [ 'Notifications', 'installNewsSitemapReminder' ] ],
			'notification/install-aioseo-video-sitemap-reminder'  => [ 'callback' => [ 'Notifications', 'installVideoSitemapReminder' ] ],
			'notification/install-mi-reminder'                    => [ 'callback' => [ 'Notifications', 'installMiReminder' ] ],
			'notification/v3-migration-custom-field-reminder'     => [ 'callback' => [ 'Notifications', 'migrationCustomFieldReminder' ] ],
			'notification/v3-migration-schema-number-reminder'    => [ 'callback' => [ 'Notifications', 'migrationSchemaNumberReminder' ] ],
			'notifications/dismiss'                               => [ 'callback' => [ 'Notifications', 'dismissNotifications' ] ],
			'objects'                                             => [ 'callback' => [ 'PostsTerms', 'searchForObjects' ], 'access' => [ 'aioseo_search_appearance_settings', 'aioseo_sitemap_settings' ] ], // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'options'                                             => [
				'callback' => [ 'Settings', 'saveChanges' ],
				'access'   =>
					[
						'aioseo_general_settings',
						'aioseo_search_appearance_settings',
						'aioseo_social_networks_settings',
						'aioseo_sitemap_settings',
						'aioseo_internal_links_settings',
						'aioseo_redirects_settings',
						'aioseo_seo_analysis_settings',
						'aioseo_tools_settings',
						'aioseo_feature_manager_settings',
						'aioseo_local_seo_settings'
					]
			],
			'plugins/deactivate'                                  => [ 'callback' => [ 'Plugins', 'deactivatePlugins' ], 'access' => 'aioseo_feature_manager_settings' ],
			'plugins/install'                                     => [ 'callback' => [ 'Plugins', 'installPlugins' ], 'access' => [ 'install_plugins', 'aioseo_feature_manager_settings' ] ],
			'reset-settings'                                      => [ 'callback' => [ 'Settings', 'resetSettings' ], 'access' => 'aioseo_tools_settings' ],
			'settings/export'                                     => [ 'callback' => [ 'Settings', 'exportSettings' ], 'access' => 'aioseo_tools_settings' ],
			'settings/hide-setup-wizard'                          => [ 'callback' => [ 'Settings', 'hideSetupWizard' ] ],
			'settings/hide-upgrade-bar'                           => [ 'callback' => [ 'Settings', 'hideUpgradeBar' ] ],
			'settings/import'                                     => [ 'callback' => [ 'Settings', 'importSettings' ], 'access' => 'aioseo_tools_settings' ],
			'settings/import-plugins'                             => [ 'callback' => [ 'Settings', 'importPlugins' ], 'access' => 'aioseo_tools_settings' ],
			'settings/toggle-card'                                => [ 'callback' => [ 'Settings', 'toggleCard' ] ],
			'settings/toggle-radio'                               => [ 'callback' => [ 'Settings', 'toggleRadio' ] ],
			'sitemap/deactivate-conflicting-plugins'              => [ 'callback' => [ 'Sitemaps', 'deactivateConflictingPlugins' ] ],
			'sitemap/delete-static-files'                         => [ 'callback' => [ 'Sitemaps', 'deleteStaticFiles' ] ],
			'tools/delete-robots-txt'                             => [ 'callback' => [ 'Tools', 'deleteRobotsTxt' ], 'access' => 'aioseo_tools_settings' ],
			'tools/import-robots-txt'                             => [ 'callback' => [ 'Tools', 'importRobotsTxt' ], 'access' => 'aioseo_tools_settings' ],
			'wizard'                                              => [ 'callback' => [ 'Wizard', 'saveWizard' ], 'access' => 'aioseo_setup_wizard' ]
		],
		'DELETE' => [
			'backup' => [ 'callback' => [ 'Tools', 'deleteBackup' ], 'access' => 'aioseo_tools_settings' ]
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];

	/**
	 * Class contructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_filter( 'rest_pre_serve_request', [ $this, 'allowHeaders' ] );
		add_action( 'rest_api_init', [ $this, 'registerRoutes' ] );
	}

	/**
	 * Get all the routes to register.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of routes.
	 */
	protected function getRoutes() {
		return $this->routes;
	}

	/**
	 * Registers the API routes.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function registerRoutes() {
		$class = new \ReflectionClass( get_called_class() );
		foreach ( $this->getRoutes() as $method => $data ) {
			foreach ( $data as $route => $options ) {
				register_rest_route(
					$this->namespace,
					$route,
					[
						'methods'             => $method,
						'permission_callback' => empty( $options['permissions'] ) ? [ $this, 'validRequest' ] : [ $this, $options['permissions'] ],
						'callback'            => is_array( $options['callback'] )
							? [
								(
									class_exists( $class->getNamespaceName() . '\\' . $options['callback'][0] )
										? $class->getNamespaceName() . '\\' . $options['callback'][0]
										: __NAMESPACE__ . '\\' . $options['callback'][0]
								),
								$options['callback'][1]
							]
							: [ $this, $options['callback'] ]
					]
				);
			}
		}
	}

	/**
	 * Sets headers that are allowed for our API routes.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function allowHeaders() {
		header( 'Access-Control-Allow-Headers: X-WP-Nonce' );
	}

	/**
	 * Determine if logged in or has the proper permissions.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request $request The REST Request.
	 * @return bool                      True if validated, false if not.
	 */
	public function validRequest( $request ) {
		return is_user_logged_in() && $this->validateAccess( $request );
	}

	/**
	 * Validates access from the routes array.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request $request The REST Request.
	 * @return bool                      True if validated, false if not.
	 */
	private function validateAccess( $request ) {
		$route     = str_replace( '/' . $this->namespace . '/', '', $request->get_route() );
		$routeData = $this->getRoutes()[ $request->get_method() ][ $route ];

		if ( empty( $routeData['access'] ) ) {
			return true;
		}

		// We validate with any of the access options.
		if ( ! is_array( $routeData['access'] ) ) {
			$routeData['access'] = [ $routeData['access'] ];
		}
		foreach ( $routeData['access'] as $access ) {
			if ( current_user_can( $access ) ) {
				return true;
			}
		}

		return false;
	}
}