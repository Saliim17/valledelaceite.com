<?php
namespace AIOSEO\Plugin\Common\Utils;

use AIOSEO\Plugin\Common\Models;
use AIOSEO\Plugin\Common\Traits;

/**
 * Class that holds all options for AIOSEO.
 *
 * @since 4.0.0
 */
class Options {
	use Traits\Options;

	/**
	 * All the default options.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $defaults = [
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		'internal'         => [
			'searchAppearanceDynamicBackup' => [
				'postTypes'  => [ 'type' => 'array', 'default' => [] ],
				'taxonomies' => [ 'type' => 'array', 'default' => [] ]
			],
			'socialFacebookDynamicBackup'   => [
				'postTypes'  => [ 'type' => 'array', 'default' => [] ],
				'taxonomies' => [ 'type' => 'array', 'default' => [] ]
			],
		],
		'webmasterTools'   => [
			'google'                    => [ 'type' => 'string' ],
			'bing'                      => [ 'type' => 'string' ],
			'yandex'                    => [ 'type' => 'string' ],
			'baidu'                     => [ 'type' => 'string' ],
			'pinterest'                 => [ 'type' => 'string' ],
			'alexa'                     => [ 'type' => 'string' ],
			'norton'                    => [ 'type' => 'string' ],
			'miscellaneousVerification' => [ 'type' => 'html' ]
		],
		'breadcrumbs'      => [
			'enable'             => [ 'type' => 'boolean' ],
			'separator'          => [ 'type' => 'string', 'default' => '&raquo;' ],
			'homepageLink'       => [ 'type' => 'boolean', 'default' => true ],
			'homepageLabel'      => [ 'type' => 'string', 'default' => 'Home' ],
			'breadcrumbPrefix'   => [ 'type' => 'string' ],
			'archiveFormat'      => [ 'type' => 'string', 'default' => 'Archives for %' ],
			'searchResultFormat' => [ 'type' => 'string', 'default' => 'Search for \'%\'' ],
			'errorFormat404'     => [ 'type' => 'string', 'default' => '404 Error: page not found' ],
			'showCurrentItem'    => [ 'type' => 'boolean', 'default' => true ],
			'linkCurrentItem'    => [ 'type' => 'boolean', 'default' => false ]
		],
		'rssContent'       => [
			'before' => [ 'type' => 'html' ],
			'after'  => [
				'type'    => 'html',
				'default' => <<<TEMPLATE
&lt;p&gt;The post #post_link first appeared on #site_link.&lt;/p&gt;
TEMPLATE
			]
		],
		'advanced'         => [
			'truSeo'          => [ 'type' => 'boolean', 'default' => true ],
			'seoAnalysis'     => [ 'type' => 'boolean', 'default' => true ],
			'dashboardWidget' => [ 'type' => 'boolean', 'default' => true ],
			'announcements'   => [ 'type' => 'boolean', 'default' => true ],
			'postTypes'       => [
				'all'      => [ 'type' => 'boolean', 'default' => true ],
				'included' => [ 'type' => 'array', 'default' => [ 'post', 'page', 'product' ] ],
			],
			'taxonomies'      => [
				'all'      => [ 'type' => 'boolean', 'default' => true ],
				'included' => [ 'type' => 'array', 'default' => [ 'category', 'post_tag', 'product_cat', 'product_tag' ] ],
			],
			'uninstall'       => [ 'type' => 'boolean', 'default' => false ]
		],
		'sitemap'          => [
			'general' => [
				'enable'           => [ 'type' => 'boolean', 'default' => true ],
				'filename'         => [ 'type' => 'string', 'default' => 'sitemap' ],
				'indexes'          => [ 'type' => 'boolean', 'default' => true ],
				'linksPerIndex'    => [ 'type' => 'number', 'default' => 1000 ],
				// @TODO: [V4+] Convert this to the dynamic options like in search appearance so we can have backups when plugins are deactivated.
				'postTypes'        => [
					'all'      => [ 'type' => 'boolean', 'default' => true ],
					'included' => [ 'type' => 'array', 'default' => [ 'post', 'page', 'attachment', 'product' ] ],
				],
				// @TODO: [V4+] Convert this to the dynamic options like in search appearance so we can have backups when plugins are deactivated.
				'taxonomies'       => [
					'all'      => [ 'type' => 'boolean', 'default' => true ],
					'included' => [ 'type' => 'array', 'default' => [ 'category', 'post_tag', 'product_cat', 'product_tag' ] ],
				],
				'author'           => [ 'type' => 'boolean', 'default' => false ],
				'date'             => [ 'type' => 'boolean', 'default' => false ],
				'additionalPages'  => [
					'enable' => [ 'type' => 'boolean', 'default' => false ],
					'pages'  => [ 'type' => 'array', 'default' => [] ]
				],
				'advancedSettings' => [
					'enable'        => [ 'type' => 'boolean', 'default' => false ],
					'excludeImages' => [ 'type' => 'boolean', 'default' => false ],
					'excludePosts'  => [ 'type' => 'array', 'default' => [] ],
					'excludeTerms'  => [ 'type' => 'array', 'default' => [] ],
					'priority'      => [
						'homePage'   => [
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						],
						'postTypes'  => [
							'grouped'   => [ 'type' => 'boolean', 'default' => true ],
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						],
						'taxonomies' => [
							'grouped'   => [ 'type' => 'boolean', 'default' => true ],
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						],
						'archive'    => [
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						],
						'author'     => [
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						]
					]
				]
			],
			'rss'     => [
				'enable'        => [ 'type' => 'boolean', 'default' => true ],
				'linksPerIndex' => [ 'type' => 'number', 'default' => 50 ],
				// @TODO: [V4+] Convert this to the dynamic options like in search appearance so we can have backups when plugins are deactivated.
				'postTypes'     => [
					'all'      => [ 'type' => 'boolean', 'default' => true ],
					'included' => [ 'type' => 'array', 'default' => [ 'post', 'page', 'product' ] ],
				]
			],
			'dynamic' => [
				'priority' => [
					'postTypes'  => [],
					'taxonomies' => []
				]
			]
		],
		'social'           => [
			'profiles'           => [
				'sameUsername' => [
					'enable'   => [ 'type' => 'boolean', 'default' => false ],
					'username' => [ 'type' => 'string' ],
					'included' => [ 'type' => 'array', 'default' => [ 'facebookPageUrl', 'twitterUrl', 'pinterestUrl', 'instagramUrl', 'youtubeUrl', 'linkedinUrl' ] ]
				],
				'urls'         => [
					'facebookPageUrl' => [ 'type' => 'string' ],
					'twitterUrl'      => [ 'type' => 'string' ],
					'instagramUrl'    => [ 'type' => 'string' ],
					'pinterestUrl'    => [ 'type' => 'string' ],
					'youtubeUrl'      => [ 'type' => 'string' ],
					'linkedinUrl'     => [ 'type' => 'string' ],
					'tumblrUrl'       => [ 'type' => 'string' ],
					'yelpPageUrl'     => [ 'type' => 'string' ],
					'soundCloudUrl'   => [ 'type' => 'string' ],
					'wikipediaUrl'    => [ 'type' => 'string' ],
					'myspaceUrl'      => [ 'type' => 'string' ],
					'googlePlacesUrl' => [ 'type' => 'string' ]
				]
			],
			'siteSocialProfiles' => [ 'type' => 'array' ],
			'facebook'           => [
				'general'  => [
					'enable'                  => [ 'type' => 'boolean', 'default' => true ],
					'defaultImageSourcePosts' => [ 'type' => 'string', 'default' => 'default' ],
					'customFieldImagePosts'   => [ 'type' => 'string' ],
					'defaultImagePosts'       => [ 'type' => 'string', 'default' => '' ],
					'defaultImagePostsWidth'  => [ 'type' => 'number', 'default' => '' ],
					'defaultImagePostsHeight' => [ 'type' => 'number', 'default' => '' ],
					'showAuthor'              => [ 'type' => 'boolean', 'default' => true ],
					'siteName'                => [ 'type' => 'string', 'localized' => true, 'default' => '#site_title #separator_sa #tagline' ],
					'dynamic'                 => [
						'postTypes'  => [],
						'taxonomies' => [],
						'archives'   => []
					]
				],
				'homePage' => [
					'image'       => [ 'type' => 'string', 'default' => '' ],
					'title'       => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'description' => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'imageWidth'  => [ 'type' => 'number', 'default' => '' ],
					'imageHeight' => [ 'type' => 'number', 'default' => '' ],
					'objectType'  => [ 'type' => 'string', 'default' => 'article' ]
				],
				'advanced' => [
					'enable'              => [ 'type' => 'boolean', 'default' => false ],
					'adminId'             => [ 'type' => 'string', 'default' => '' ],
					'appId'               => [ 'type' => 'string', 'default' => '' ],
					'authorUrl'           => [ 'type' => 'string', 'default' => '' ],
					'generateArticleTags' => [ 'type' => 'boolean', 'default' => false ],
					'useKeywordsInTags'   => [ 'type' => 'boolean', 'default' => true ],
					'useCategoriesInTags' => [ 'type' => 'boolean', 'default' => true ],
					'usePostTagsInTags'   => [ 'type' => 'boolean', 'default' => true ]
				]
			],
			'twitter'            => [
				'general'  => [
					'enable'                  => [ 'type' => 'boolean', 'default' => true ],
					'defaultCardType'         => [ 'type' => 'string', 'default' => 'summary' ],
					'defaultImageSourcePosts' => [ 'type' => 'string', 'default' => 'default' ],
					'customFieldImagePosts'   => [ 'type' => 'string' ],
					'defaultImagePosts'       => [ 'type' => 'string', 'default' => '' ],
					'showAuthor'              => [ 'type' => 'boolean', 'default' => true ],
					'additionalData'          => [ 'type' => 'boolean', 'default' => false ]
				],
				'homePage' => [
					'image'       => [ 'type' => 'string', 'default' => '' ],
					'title'       => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'description' => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'cardType'    => [ 'type' => 'string', 'default' => 'summary' ]
				],
			]
		],
		'searchAppearance' => [
			'global'   => [
				'separator'       => [ 'type' => 'string', 'default' => '&#45;' ],
				'siteTitle'       => [ 'type' => 'string', 'localized' => true, 'default' => '#site_title #separator_sa #tagline' ],
				'metaDescription' => [ 'type' => 'string', 'localized' => true, 'default' => '#tagline' ],
				'keywords'        => [ 'type' => 'string', 'localized' => true ],
				'schema'          => [
					'siteRepresents'    => [ 'type' => 'string', 'default' => 'organization' ],
					'person'            => [ 'type' => 'string' ],
					'organizationName'  => [ 'type' => 'string' ],
					'organizationLogo'  => [ 'type' => 'string' ],
					'personName'        => [ 'type' => 'string' ],
					'personLogo'        => [ 'type' => 'string' ],
					'phone'             => [ 'type' => 'string' ],
					'contactType'       => [ 'type' => 'string' ],
					'contactTypeManual' => [ 'type' => 'string' ]
				]
			],
			'advanced' => [
				'globalRobotsMeta'             => [
					'default'           => [ 'type' => 'boolean', 'default' => true ],
					'noindex'           => [ 'type' => 'boolean', 'default' => false ],
					'nofollow'          => [ 'type' => 'boolean', 'default' => false ],
					'noindexPaginated'  => [ 'type' => 'boolean', 'default' => false ],
					'nofollowPaginated' => [ 'type' => 'boolean', 'default' => false ],
					'noarchive'         => [ 'type' => 'boolean', 'default' => false ],
					'noimageindex'      => [ 'type' => 'boolean', 'default' => false ],
					'notranslate'       => [ 'type' => 'boolean', 'default' => false ],
					'nosnippet'         => [ 'type' => 'boolean', 'default' => false ],
					'noodp'             => [ 'type' => 'boolean', 'default' => false ],
					'maxSnippet'        => [ 'type' => 'number', 'default' => -1 ],
					'maxVideoPreview'   => [ 'type' => 'number', 'default' => -1 ],
					'maxImagePreview'   => [ 'type' => 'string', 'default' => 'large' ]
				],
				'sitelinks'                    => [ 'type' => 'boolean', 'default' => true ],
				'noIndexEmptyCat'              => [ 'type' => 'boolean', 'default' => true ],
				'removeStopWords'              => [ 'type' => 'boolean', 'default' => false ],
				'noPaginationForCanonical'     => [ 'type' => 'boolean', 'default' => true ],
				'useKeywords'                  => [ 'type' => 'boolean', 'default' => false ],
				'keywordsLooking'              => [ 'type' => 'boolean', 'default' => true ],
				'useCategoriesForMetaKeywords' => [ 'type' => 'boolean', 'default' => false ],
				'useTagsForMetaKeywords'       => [ 'type' => 'boolean', 'default' => false ],
				'dynamicallyGenerateKeywords'  => [ 'type' => 'boolean', 'default' => false ],
				'pagedFormat'                  => [ 'type' => 'string', 'default' => ' - Page #page_number' ]
			],
			'archives' => [
				'author' => [
					'show'            => [ 'type' => 'boolean', 'default' => true ],
					'title'           => [ 'type' => 'string', 'localized' => true, 'default' => '#author_name #separator_sa #site_title' ],
					'metaDescription' => [ 'type' => 'string', 'localized' => true, 'default' => '#author_bio' ],
					'advanced'        => [
						'robotsMeta'                => [
							'default'         => [ 'type' => 'boolean', 'default' => true ],
							'noindex'         => [ 'type' => 'boolean', 'default' => false ],
							'nofollow'        => [ 'type' => 'boolean', 'default' => false ],
							'noarchive'       => [ 'type' => 'boolean', 'default' => false ],
							'noimageindex'    => [ 'type' => 'boolean', 'default' => false ],
							'notranslate'     => [ 'type' => 'boolean', 'default' => false ],
							'nosnippet'       => [ 'type' => 'boolean', 'default' => false ],
							'noodp'           => [ 'type' => 'boolean', 'default' => false ],
							'maxSnippet'      => [ 'type' => 'number', 'default' => -1 ],
							'maxVideoPreview' => [ 'type' => 'number', 'default' => -1 ],
							'maxImagePreview' => [ 'type' => 'string', 'default' => 'large' ]
						],
						'showDateInGooglePreview'   => [ 'type' => 'boolean', 'default' => true ],
						'showPostThumbnailInSearch' => [ 'type' => 'boolean', 'default' => true ],
						'showMetaBox'               => [ 'type' => 'boolean', 'default' => true ],
						'keywords'                  => [ 'type' => 'string', 'localized' => true ]
					]
				],
				'date'   => [
					'show'            => [ 'type' => 'boolean', 'default' => true ],
					'title'           => [ 'type' => 'string', 'localized' => true, 'default' => '#archive_date #separator_sa #site_title' ],
					'metaDescription' => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'advanced'        => [
						'robotsMeta'                => [
							'default'         => [ 'type' => 'boolean', 'default' => true ],
							'noindex'         => [ 'type' => 'boolean', 'default' => false ],
							'nofollow'        => [ 'type' => 'boolean', 'default' => false ],
							'noarchive'       => [ 'type' => 'boolean', 'default' => false ],
							'noimageindex'    => [ 'type' => 'boolean', 'default' => false ],
							'notranslate'     => [ 'type' => 'boolean', 'default' => false ],
							'nosnippet'       => [ 'type' => 'boolean', 'default' => false ],
							'noodp'           => [ 'type' => 'boolean', 'default' => false ],
							'maxSnippet'      => [ 'type' => 'number', 'default' => -1 ],
							'maxVideoPreview' => [ 'type' => 'number', 'default' => -1 ],
							'maxImagePreview' => [ 'type' => 'string', 'default' => 'large' ]
						],
						'showDateInGooglePreview'   => [ 'type' => 'boolean', 'default' => true ],
						'showPostThumbnailInSearch' => [ 'type' => 'boolean', 'default' => true ],
						'showMetaBox'               => [ 'type' => 'boolean', 'default' => true ],
						'keywords'                  => [ 'type' => 'string', 'localized' => true ]
					]
				],
				'search' => [
					'show'            => [ 'type' => 'boolean', 'default' => false ],
					'title'           => [ 'type' => 'string', 'localized' => true, 'default' => '#search_term #separator_sa #site_title' ],
					'metaDescription' => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'advanced'        => [
						'robotsMeta'                => [
							'default'         => [ 'type' => 'boolean', 'default' => false ],
							'noindex'         => [ 'type' => 'boolean', 'default' => true ],
							'nofollow'        => [ 'type' => 'boolean', 'default' => false ],
							'noarchive'       => [ 'type' => 'boolean', 'default' => false ],
							'noimageindex'    => [ 'type' => 'boolean', 'default' => false ],
							'notranslate'     => [ 'type' => 'boolean', 'default' => false ],
							'nosnippet'       => [ 'type' => 'boolean', 'default' => false ],
							'noodp'           => [ 'type' => 'boolean', 'default' => false ],
							'maxSnippet'      => [ 'type' => 'number', 'default' => -1 ],
							'maxVideoPreview' => [ 'type' => 'number', 'default' => -1 ],
							'maxImagePreview' => [ 'type' => 'string', 'default' => 'large' ]
						],
						'showDateInGooglePreview'   => [ 'type' => 'boolean', 'default' => true ],
						'showPostThumbnailInSearch' => [ 'type' => 'boolean', 'default' => true ],
						'showMetaBox'               => [ 'type' => 'boolean', 'default' => true ],
						'keywords'                  => [ 'type' => 'string', 'localized' => true ]
					]
				]
			],
			'dynamic'  => [
				'postTypes'  => [],
				'taxonomies' => [],
				'archives'   => []
			]
		],
		'tools'            => [
			'robots'       => [
				'enable' => [ 'type' => 'boolean', 'default' => false ],
				'rules'  => [ 'type' => 'array', 'default' => [] ]
			],
			'importExport' => [
				'backup' => [
					'lastTime' => [ 'type' => 'string' ],
					'data'     => [ 'type' => 'string' ],
				]
			]
		],
		'deprecated'       => [
			'webmasterTools'   => [
				'googleAnalytics' => [
					'id'                        => [ 'type' => 'string' ],
					'advanced'                  => [ 'type' => 'boolean', 'default' => false ],
					'trackingDomain'            => [ 'type' => 'string' ],
					'multipleDomains'           => [ 'type' => 'boolean', 'default' => false ],
					'additionalDomains'         => [ 'type' => 'html' ],
					'anonymizeIp'               => [ 'type' => 'boolean', 'default' => false ],
					'displayAdvertiserTracking' => [ 'type' => 'boolean', 'default' => false ],
					'excludeUsers'              => [ 'type' => 'array', 'default' => [] ],
					'trackOutboundLinks'        => [ 'type' => 'boolean', 'default' => false ],
					'enhancedLinkAttribution'   => [ 'type' => 'boolean', 'default' => false ],
					'enhancedEcommerce'         => [ 'type' => 'boolean', 'default' => false ]
				]
			],
			'searchAppearance' => [
				'global'   => [
					'descriptionFormat' => [ 'type' => 'string' ],
					'schema'            => [
						'enableSchemaMarkup' => [ 'type' => 'boolean', 'default' => true ]
					]
				],
				'advanced' => [
					'autogenerateDescriptions'               => [ 'type' => 'boolean', 'default' => true ],
					'runShortcodesInDescription'             => [ 'type' => 'boolean', 'default' => true ],
					'useContentForAutogeneratedDescriptions' => [ 'type' => 'boolean', 'default' => false ],
					'excludePosts'                           => [ 'type' => 'array', 'default' => [] ],
					'excludeTerms'                           => [ 'type' => 'array', 'default' => [] ],
				]
			],
			'sitemap'          => [
				'general' => [
					'advancedSettings' => [
						'dynamic' => [ 'type' => 'boolean', 'default' => true ]
					]
				]
			],
			'tools'            => [
				'blocker' => [
					'blockBots'    => [ 'type' => 'boolean' ],
					'blockReferer' => [ 'type' => 'boolean' ],
					'track'        => [ 'type' => 'boolean' ],
					'custom'       => [
						'enable'  => [ 'type' => 'boolean' ],
						'bots'    => [ 'type' => 'html', 'default' => '' ],
						'referer' => [ 'type' => 'html', 'default' => '' ]
					]
				]
			]
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];

	/**
	 * The Construct method.
	 *
	 * @since 4.0.0
	 *
	 * @param string $optionsName An array of options.
	 */
	public function __construct( $optionsName = 'aioseo_options' ) {
		$this->optionsName = is_network_admin() ? $optionsName . '_network' : $optionsName;

		$this->init();
	}

	/**
	 * Initializes the options.
	 *
	 * @since 4.0.0
	 *
	 * @param  boolean $resetKeys Whether or not to reset keys after init.
	 * @return void
	 */
	protected function init( $resetKeys = false ) {
		if ( $resetKeys ) {
			$originalGroupKey  = $this->groupKey;
			$originalSubGroups = $this->subGroups;
		}

		$this->addDynamicDefaults();
		$this->translateDefaults();

		$options = $this->getDbOptions();

		$this->options = apply_filters( 'aioseo_get_options', $options );

		// Get the localized options.
		$dbOptionsLocalized = get_option( $this->optionsName . '_localized' );
		if ( empty( $dbOptionsLocalized ) ) {
			$dbOptionsLocalized = [];
		}
		$this->localized = $dbOptionsLocalized;

		if ( $resetKeys ) {
			$this->groupKey  = $originalGroupKey;
			$this->subGroups = $originalSubGroups;
		}
	}

	/**
	 * Get the DB options.
	 *
	 * @since 4.0.12
	 *
	 * @return array An array of options.
	 */
	public function getDbOptions() {
		// Options from the DB.
		$dbOptions = json_decode( get_option( $this->optionsName ), true );
		if ( empty( $dbOptions ) ) {
			$dbOptions = [];
		}

		// Refactor options.
		$this->defaultsMerged = array_replace_recursive( $this->defaults, $this->defaultsMerged );

		return array_replace_recursive(
			$this->defaultsMerged,
			$this->addValueToValuesArray( $this->defaultsMerged, $dbOptions )
		);
	}

	/**
	 * Adds some defaults that are dynamically generated.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addDynamicDefaults() {
		$needsUpdate = false;

		// Bad Bots/Referers.
		$this->defaults['deprecated']['tools']['blocker']['custom']['bots']['default']    = implode( "\n", aioseo()->badBotBlocker->getBotList() );
		$this->defaults['deprecated']['tools']['blocker']['custom']['referer']['default'] = implode( "\n", aioseo()->badBotBlocker->getRefererList() );

		// Post Types.
		$postTypes = aioseo()->helpers->getPublicPostTypes();
		foreach ( $postTypes as $postType ) {
			if ( 'type' === $postType['name'] ) {
				$postType['name'] = '_aioseo_type';
			}

			// Search appearance
			$defaultTitle       = '#post_title #separator_sa #site_title';
			$defaultDescription = '#post_content';
			$defaultSchemaType  = 'WebPage';
			$defaultWebPageType = 'WebPage';
			$defaultArticleType = 'BlogPosting';

			if ( 'post' === $postType['name'] ) {
				$defaultSchemaType = 'Article';
			}

			if ( 'attachment' === $postType['name'] ) {
				$defaultDescription = '#attachment_caption';
				$defaultSchemaType  = 'ItemPage';
				$defaultWebPageType = 'ItemPage';
			}

			if ( 'product' === $postType['name'] ) {
				$defaultSchemaType  = 'WebPage'; // @TODO: [V4+] Replace this with Product after it's released.
				$defaultWebPageType = 'ItemPage';
			}

			if ( 'news' === $postType['name'] ) {
				$defaultArticleType = 'NewsArticle';
			}

			// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			$options = [
				'show'            => [ 'type' => 'boolean', 'default' => true ],
				'title'           => [ 'type' => 'string', 'localized' => true, 'default' => $defaultTitle ],
				'metaDescription' => [ 'type' => 'string', 'localized' => true, 'default' => $defaultDescription ],
				'schemaType'      => [ 'type' => 'string', 'default' => $defaultSchemaType ],
				'webPageType'     => [ 'type' => 'string', 'default' => $defaultWebPageType ],
				'articleType'     => [ 'type' => 'string', 'default' => $defaultArticleType ],
				'customFields'    => [ 'type' => 'html' ],
				'advanced'        => [
					'robotsMeta'                => [
						'default'         => [ 'type' => 'boolean', 'default' => true ],
						'noindex'         => [ 'type' => 'boolean', 'default' => false ],
						'nofollow'        => [ 'type' => 'boolean', 'default' => false ],
						'noarchive'       => [ 'type' => 'boolean', 'default' => false ],
						'noimageindex'    => [ 'type' => 'boolean', 'default' => false ],
						'notranslate'     => [ 'type' => 'boolean', 'default' => false ],
						'nosnippet'       => [ 'type' => 'boolean', 'default' => false ],
						'noodp'           => [ 'type' => 'boolean', 'default' => false ],
						'maxSnippet'      => [ 'type' => 'number', 'default' => -1 ],
						'maxVideoPreview' => [ 'type' => 'number', 'default' => -1 ],
						'maxImagePreview' => [ 'type' => 'string', 'default' => 'large' ]
					],
					'bulkEditing'               => [ 'type' => 'string', 'default' => 'enabled' ],
					'showDateInGooglePreview'   => [ 'type' => 'boolean', 'default' => true ],
					'showPostThumbnailInSearch' => [ 'type' => 'boolean', 'default' => true ],
					'showMetaBox'               => [ 'type' => 'boolean', 'default' => true ]
				]
			];

			if ( 'attachment' === $postType['name'] ) {
				$options['redirectAttachmentUrls'] = [ 'type' => 'string', 'default' => 'attachment' ];
			}
			// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

			// Reset the backup.
			if ( isset( $this->options['internal']['searchAppearanceDynamicBackup']['postTypes']['value'][ $postType['name'] ] ) ) {
				$needsUpdate = true;
				$options = array_replace_recursive( $options, json_decode( $this->options['internal']['searchAppearanceDynamicBackup']['postTypes']['value'][ $postType['name'] ], true ) );
				unset( $this->options['internal']['searchAppearanceDynamicBackup']['postTypes']['value'][ $postType['name'] ] );

				// Set it back on the options.
				$this->options['searchAppearance']['dynamic']['postTypes'][ $postType['name'] ] = $options;
			}

			$this->defaults['searchAppearance']['dynamic']['postTypes'][ $postType['name'] ] = $options;

			// Facebook (open graph).
			$options = [
				'objectType' => [
					'type'    => 'string',
					'default' => 'article'
				],
			];

			// Reset the backup.
			if ( isset( $this->options['internal']['socialFacebookDynamicBackup']['postTypes']['value'][ $postType['name'] ] ) ) {
				$needsUpdate = true;
				$options = array_replace_recursive( $options, json_decode( $this->options['internal']['socialFacebookDynamicBackup']['postTypes']['value'][ $postType['name'] ], true ) );
				unset( $this->options['internal']['socialFacebookDynamicBackup']['postTypes']['value'][ $postType['name'] ] );

				// Set it back on the options.
				$this->options['social']['facebook']['general']['dynamic']['postTypes'][ $postType['name'] ] = $options;
			}

			$this->defaults['social']['facebook']['general']['dynamic']['postTypes'][ $postType['name'] ] = $options;

			// Sitemap Options.
			// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			$this->defaults['sitemap']['dynamic']['priority']['postTypes'][ $postType['name'] ] = [
				'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
				'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
			];
			// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		}

		// Taxonomies.
		$taxonomies = aioseo()->helpers->getPublicTaxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			if ( 'type' === $taxonomy['name'] ) {
				$taxonomy['name'] = '_aioseo_type';
			}

			// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			$options = [
				'show'            => [ 'type' => 'boolean', 'default' => true ],
				'title'           => [ 'type' => 'string', 'localized' => true, 'default' => '#taxonomy_title #separator_sa #site_title' ],
				'metaDescription' => [ 'type' => 'string', 'localized' => true, 'default' => '#taxonomy_description' ],
				'advanced'        => [
					'robotsMeta'                => [
						'default'         => [ 'type' => 'boolean', 'default' => true ],
						'noindex'         => [ 'type' => 'boolean', 'default' => false ],
						'nofollow'        => [ 'type' => 'boolean', 'default' => false ],
						'noarchive'       => [ 'type' => 'boolean', 'default' => false ],
						'noimageindex'    => [ 'type' => 'boolean', 'default' => false ],
						'notranslate'     => [ 'type' => 'boolean', 'default' => false ],
						'nosnippet'       => [ 'type' => 'boolean', 'default' => false ],
						'noodp'           => [ 'type' => 'boolean', 'default' => false ],
						'maxSnippet'      => [ 'type' => 'number', 'default' => -1 ],
						'maxVideoPreview' => [ 'type' => 'number', 'default' => -1 ],
						'maxImagePreview' => [ 'type' => 'string', 'default' => 'large' ]
					],
					'showDateInGooglePreview'   => [ 'type' => 'boolean', 'default' => true ],
					'showPostThumbnailInSearch' => [ 'type' => 'boolean', 'default' => true ],
					'showMetaBox'               => [ 'type' => 'boolean', 'default' => true ]
				]
			];
			// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

			// Reset the backup.
			if ( isset( $this->options['internal']['searchAppearanceDynamicBackup']['taxonomies']['value'][ $taxonomy['name'] ] ) ) {
				$needsUpdate = true;
				$options = array_replace_recursive( $options, json_decode( $this->options['internal']['searchAppearanceDynamicBackup']['taxonomies']['value'][ $taxonomy['name'] ], true ) );
				unset( $this->options['internal']['searchAppearanceDynamicBackup']['taxonomies']['value'][ $taxonomy['name'] ] );

				// Set it back on the options.
				$this->options['searchAppearance']['dynamic']['taxonomies'][ $taxonomy['name'] ] = $options;
			}

			$this->defaults['searchAppearance']['dynamic']['taxonomies'][ $taxonomy['name'] ] = $options;

			// Facebook (open graph).
			$options = [
				'objectType' => [
					'type'    => 'string',
					'default' => 'article'
				],
			];

			// Reset the backup.
			if ( isset( $this->options['internal']['socialFacebookDynamicBackup']['taxonomies']['value'][ $taxonomy['name'] ] ) ) {
				$needsUpdate = true;
				$options = array_replace_recursive( $options, json_decode( $this->options['internal']['socialFacebookDynamicBackup']['taxonomies']['value'][ $taxonomy['name'] ], true ) );
				unset( $this->options['internal']['socialFacebookDynamicBackup']['taxonomies']['value'][ $taxonomy['name'] ] );

				// Set it back on the options.
				$this->options['social']['facebook']['general']['dynamic']['taxonomies'][ $taxonomy['name'] ] = $options;
			}

			$this->defaults['social']['facebook']['general']['dynamic']['taxonomies'][ $taxonomy['name'] ] = $options;

			// Sitemap Options.
			// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			$this->defaults['sitemap']['dynamic']['priority']['taxonomies'][ $taxonomy['name'] ] = [
				'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
				'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
			];
			// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		}

		// CPT Archives.
		$postTypes = aioseo()->helpers->getPublicPostTypes( false, true );
		foreach ( $postTypes as $postType ) {
			if ( 'type' === $postType['name'] ) {
				$postType['name'] = '_aioseo_type';
			}

			// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			$options = [
				'show'            => [ 'type' => 'boolean', 'default' => true ],
				'title'           => [ 'type' => 'string', 'localized' => true, 'default' => '#archive_title #separator_sa #site_title' ],
				'metaDescription' => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
				'customFields'    => [ 'type' => 'html' ],
				'advanced'        => [
					'robotsMeta'                => [
						'default'         => [ 'type' => 'boolean', 'default' => true ],
						'noindex'         => [ 'type' => 'boolean', 'default' => false ],
						'nofollow'        => [ 'type' => 'boolean', 'default' => false ],
						'noarchive'       => [ 'type' => 'boolean', 'default' => false ],
						'noimageindex'    => [ 'type' => 'boolean', 'default' => false ],
						'notranslate'     => [ 'type' => 'boolean', 'default' => false ],
						'nosnippet'       => [ 'type' => 'boolean', 'default' => false ],
						'noodp'           => [ 'type' => 'boolean', 'default' => false ],
						'maxSnippet'      => [ 'type' => 'number', 'default' => -1 ],
						'maxVideoPreview' => [ 'type' => 'number', 'default' => -1 ],
						'maxImagePreview' => [ 'type' => 'string', 'default' => 'large' ]
					],
					'showDateInGooglePreview'   => [ 'type' => 'boolean', 'default' => true ],
					'showPostThumbnailInSearch' => [ 'type' => 'boolean', 'default' => true ],
					'showMetaBox'               => [ 'type' => 'boolean', 'default' => true ],
					'keywords'                  => [ 'type' => 'string', 'localized' => true ]
				]
			];
			// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

			// Reset the backup.
			if ( isset( $this->options['internal']['searchAppearanceDynamicBackup']['archives']['value'][ $postType['name'] ] ) ) {
				$needsUpdate = true;
				$options = array_replace_recursive( $options, json_decode( $this->options['internal']['searchAppearanceDynamicBackup']['archives']['value'][ $postType['name'] ], true ) );
				unset( $this->options['internal']['searchAppearanceDynamicBackup']['archives']['value'][ $postType['name'] ] );

				// Set it back on the options.
				$this->options['searchAppearance']['dynamic']['archives'][ $postType['name'] ] = $options;
			}

			$this->defaults['searchAppearance']['dynamic']['archives'][ $postType['name'] ] = $options;

			// Facebook (open graph).
			$options = [
				'objectType' => [
					'type'    => 'string',
					'default' => 'article'
				],
			];

			// Reset the backup.
			if ( isset( aioseo()->internalOptions->internal->socialFacebookDynamicBackup->archives->{ $postType['name'] } ) ) {
				$needsUpdate = true;
				$options = array_replace_recursive( $options, json_decode( aioseo()->internalOptions->internal->socialFacebookDynamicBackup->archives->{ $postType['name'] }, true ) );
				unset( aioseo()->internalOptions->internal->socialFacebookDynamicBackup->archives->{ $postType['name'] } );

				// Set it back on the options.
				$this->options['social']['facebook']['general']['dynamic']['archives'][ $postType['name'] ] = $options;
			}

			$this->defaults['social']['facebook']['general']['dynamic']['archives'][ $postType['name'] ] = $options;
		}

		$this->defaults['searchAppearance']['global']['schema']['organizationName']['default'] = aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) );

		if ( $needsUpdate ) {
			$this->update( $this->getDbOptions() );
		}
	}

	/**
	 * For our defaults array, some options need to be translated, so we do that here.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function translateDefaults() {
		$default = sprintf( '{"label":"%1$s","value":"default"}', __( 'default', 'all-in-one-seo-pack' ) );
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['homePage']['priority']['default']    = $default;
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['homePage']['frequency']['default']   = $default;
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['postTypes']['priority']['default']   = $default;
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['postTypes']['frequency']['default']  = $default;
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['taxonomies']['priority']['default']  = $default;
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['taxonomies']['frequency']['default'] = $default;

		$this->defaults['breadcrumbs']['homepageLabel']['default']                             = __( 'Home', 'all-in-one-seo-pack' );
		$this->defaults['breadcrumbs']['archiveFormat']['default']                             = sprintf( '%1$s %%', __( 'Archives for', 'all-in-one-seo-pack' ) );
		$this->defaults['breadcrumbs']['searchResultFormat']['default']                        = sprintf( '%1$s \'%%\'', __( 'Search for', 'all-in-one-seo-pack' ) );
		$this->defaults['breadcrumbs']['errorFormat404']['default']                            = __( '404 Error: page not found', 'all-in-one-seo-pack' );
		$this->defaults['searchAppearance']['global']['schema']['organizationName']['default'] = aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) );
		$this->defaults['searchAppearance']['global']['schema']['organizationLogo']['default'] = aioseo()->helpers->getSiteLogoUrl() ? aioseo()->helpers->getSiteLogoUrl() : '';
	}

	/**
	 * Sanitizes, then saves the options to the database.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $options An array of options to sanitize, then save.
	 * @return void
	 */
	public function sanitizeAndSave( $options ) {
		$sitemapOptions           = ! empty( $options['sitemap'] ) && ! empty( $options['sitemap']['general'] ) ? $options['sitemap']['general'] : null;
		$oldSitemapOptions        = aioseo()->options->sitemap->general->all();
		$deprecatedSitemapOptions = ! empty( $options['deprecated'] ) &&
			! empty( $options['deprecated']['sitemap'] ) &&
			! empty( $options['deprecated']['sitemap']['general'] )
				? $options['deprecated']['sitemap']['general']
				: null;
		$oldDeprecatedSitemapOptions = aioseo()->options->deprecated->sitemap->general->all();
		$oldPhoneOption              = aioseo()->options->searchAppearance->global->schema->phone;
		$phoneNumberOptions          = ! empty( $options['searchAppearance'] ) &&
			! empty( $options['searchAppearance']['global'] ) &&
			! empty( $options['searchAppearance']['global']['schema'] ) &&
			isset( $options['searchAppearance']['global']['schema']['phone'] )
				? $options['searchAppearance']['global']['schema']['phone']
				: null;

		$options = $this->maybeRemoveUnfilteredHtmlFields( $options );

		$this->init();

		if ( ! is_array( $options ) ) {
			return;
		}

		// Get the settings from the options being passed in.
		$dynamicPostTypeSettings     = $this->options['searchAppearance']['dynamic']['postTypes'];
		$dynamicPostTypeSettingsOG   = $this->options['social']['facebook']['general']['dynamic']['postTypes'];
		$dynamicTaxonomiesSettings   = $this->options['searchAppearance']['dynamic']['taxonomies'];
		$dynamicTaxonomiesSettingsOG = $this->options['social']['facebook']['general']['dynamic']['taxonomies'];
		$dynamicArchiveSettings      = $this->options['searchAppearance']['dynamic']['archives'];

		// Refactor options.
		$this->options = array_replace_recursive(
			$this->options,
			$this->addValueToValuesArray( $this->options, $options, [], true )
		);

		// Post Types.
		$postTypes = aioseo()->helpers->getPublicPostTypes();
		foreach ( $dynamicPostTypeSettings as $postTypeName => $postTypeData ) {
			foreach ( $postTypes as $postType ) {
				if ( 'type' === $postType['name'] ) {
					$postType['name'] = '_aioseo_type';
				}

				// If the option has disappeared, we want to save the settings manually.
				if ( $postTypeName === $postType['name'] ) {
					continue 2;
				}
			}

			$this->options['internal']['searchAppearanceDynamicBackup']['postTypes']['value'][ $postTypeName ] = wp_json_encode( $postTypeData );
		}
		foreach ( $dynamicPostTypeSettingsOG as $postTypeName => $postTypeData ) {
			foreach ( $postTypes as $postType ) {
				if ( 'type' === $postType['name'] ) {
					$postType['name'] = '_aioseo_type';
				}

				// If the option has disappeared, we want to save the settings manually.
				if ( $postTypeName === $postType['name'] ) {
					continue 2;
				}
			}

			$this->options['internal']['socialFacebookDynamicBackup']['postTypes']['value'][ $postTypeName ] = wp_json_encode( $postTypeData );
		}

		// Taxonomies.
		$taxonomies = aioseo()->helpers->getPublicTaxonomies();
		foreach ( $dynamicTaxonomiesSettings as $taxonomyName => $taxonomyData ) {
			foreach ( $taxonomies as $taxonomy ) {
				if ( 'type' === $taxonomy['name'] ) {
					$taxonomy['name'] = '_aioseo_type';
				}

				// If the option has disappeared, we want to save the settings manually.
				if ( $taxonomyName === $taxonomy['name'] ) {
					continue 2;
				}
			}

			$this->options['internal']['searchAppearanceDynamicBackup']['taxonomies']['value'][ $taxonomyName ] = wp_json_encode( $taxonomyData );
		}
		foreach ( $dynamicTaxonomiesSettingsOG as $taxonomyName => $taxonomyData ) {
			foreach ( $taxonomies as $taxonomy ) {
				// If the option has disappeared, we want to save the settings manually.
				if ( $taxonomyName === $taxonomy['name'] ) {
					continue 2;
				}
			}

			$this->options['internal']['socialFacebookDynamicBackup']['taxonomies']['value'][ $taxonomyName ] = wp_json_encode( $taxonomyData );
		}

		// Archives.
		$postTypes = aioseo()->helpers->getPublicPostTypes( false, true );
		foreach ( $dynamicArchiveSettings as $archiveName => $archiveData ) {
			foreach ( $postTypes as $postType ) {
				if ( 'type' === $postType['name'] ) {
					$postType['name'] = '_aioseo_type';
				}

				// If the option has disappeared, we want to save the settings manually.
				if ( $archiveName === $postType['name'] ) {
					continue 2;
				}
			}

			$this->options['internal']['searchAppearanceDynamicBackup']['archives']['value'][ $postTypeName ] = wp_json_encode( $postTypeData );
		}

		// The above works for most options, but there are a few that need to be forcibly updated.
		if ( $sitemapOptions ) {
			$this->options['sitemap']['general']['postTypes']['included']['value']            = $this->sanitizeField( $options['sitemap']['general']['postTypes']['included'], 'array' );
			$this->options['sitemap']['general']['taxonomies']['included']['value']           = $this->sanitizeField( $options['sitemap']['general']['taxonomies']['included'], 'array' );
			$this->options['sitemap']['general']['additionalPages']['pages']['value']         = $this->sanitizeField( $options['sitemap']['general']['additionalPages']['pages'], 'array' );
			$this->options['sitemap']['general']['advancedSettings']['excludePosts']['value'] = $this->sanitizeField( $options['sitemap']['general']['advancedSettings']['excludePosts'], 'array' );
			$this->options['sitemap']['general']['advancedSettings']['excludeTerms']['value'] = $this->sanitizeField( $options['sitemap']['general']['advancedSettings']['excludeTerms'], 'array' );
		}

		if ( ! empty( $options['advanced'] ) ) {
			if (
				! empty( $options['advanced']['postTypes'] ) &&
				isset( $options['advanced']['postTypes']['included'] )
			) {
				$this->options['advanced']['postTypes']['included']['value'] = $this->sanitizeField( $options['advanced']['postTypes']['included'], 'array' );
			}

			if (
				! empty( $options['advanced']['taxonomies'] ) &&
				isset( $options['advanced']['taxonomies']['included'] )
			) {
				$this->options['advanced']['taxonomies']['included']['value'] = $this->sanitizeField( $options['advanced']['taxonomies']['included'], 'array' );
			}
		}

		if ( ! empty( $options['tools'] ) ) {
			if (
				! empty( $options['tools']['robots'] ) &&
				isset( $options['tools']['robots']['rules'] )
			) {
				$this->options['tools']['robots']['rules']['value'] = $this->sanitizeField( $options['tools']['robots']['rules'], 'array' );
			}
		}

		if ( ! empty( $options['deprecated'] ) ) {

			if (
				! empty( $options['deprecated']['webmasterTools'] ) &&
				! empty( $options['deprecated']['webmasterTools']['googleAnalytics'] ) &&
				isset( $options['deprecated']['webmasterTools']['googleAnalytics']['excludeUsers'] )
			) {
				$this->options['deprecated']['webmasterTools']['googleAnalytics']['excludeUsers']['value'] = $this->sanitizeField( $options['deprecated']['webmasterTools']['googleAnalytics']['excludeUsers'], 'array' ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			}
			if (
				! empty( $options['deprecated']['searchAppearance'] ) &&
				! empty( $options['deprecated']['searchAppearance']['advanced'] ) &&
				isset( $options['deprecated']['searchAppearance']['advanced']['excludePosts'] )
			) {
				$this->options['deprecated']['searchAppearance']['advanced']['excludePosts']['value'] = $this->sanitizeField( $options['deprecated']['searchAppearance']['advanced']['excludePosts'], 'array' ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			}

			if (
				! empty( $options['deprecated']['searchAppearance'] ) &&
				! empty( $options['deprecated']['searchAppearance']['advanced'] ) &&
				isset( $options['deprecated']['searchAppearance']['advanced']['excludeTerms'] )
			) {
				$this->options['deprecated']['searchAppearance']['advanced']['excludeTerms']['value'] = $this->sanitizeField( $options['deprecated']['searchAppearance']['advanced']['excludeTerms'], 'array' ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			}
		}

		// Update localized options.
		update_option( $this->optionsName . '_localized', $this->localized );

		// Update values.
		$this->update();

		// If phone settings have changed, let's see if we need to dump the phone number notice.
		if (
			$phoneNumberOptions &&
			$phoneNumberOptions !== $oldPhoneOption
		) {
			$notification = Models\Notification::getNotificationByName( 'v3-migration-schema-number' );
			if ( $notification->exists() ) {
				Models\Notification::deleteNotificationByName( 'v3-migration-schema-number' );
			}
		}

		// If sitemap settings were changed, static files need to be regenerated.
		if (
			! empty( $deprecatedSitemapOptions ) &&
			! empty( $sitemapOptions )
		) {
			if (
				(
					aioseo()->helpers->arraysDifferent( $oldSitemapOptions, $sitemapOptions ) ||
					aioseo()->helpers->arraysDifferent( $oldDeprecatedSitemapOptions, $deprecatedSitemapOptions )
				) &&
				$sitemapOptions['advancedSettings']['enable'] &&
				! $deprecatedSitemapOptions['advancedSettings']['dynamic']
			) {
				aioseo()->sitemap->scheduleRegeneration();
			}
		}
	}

	/**
	 * If the user does not have access to unfiltered HTML, we need to remove them from saving.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $options An array of options.
	 * @return array          An array of options.
	 */
	private function maybeRemoveUnfilteredHtmlFields( $options ) {
		if ( ! current_user_can( 'unfiltered_html' ) ) {
			if (
				! empty( $options['webmasterTools'] ) &&
				isset( $options['webmasterTools']['miscellaneousVerification'] )
			) {
				unset( $options['webmasterTools']['miscellaneousVerification'] );
			}

			if (
				! empty( $options['rssContent'] ) &&
				isset( $options['rssContent']['before'] )
			) {
				unset( $options['rssContent']['before'] );
			}

			if (
				! empty( $options['rssContent'] ) &&
				isset( $options['rssContent']['after'] )
			) {
				unset( $options['rssContent']['after'] );
			}
		}

		return $options;
	}
}