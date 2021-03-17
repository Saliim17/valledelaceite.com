<?php
namespace AIOSEO\Plugin\Common\Schema;

/**
 * Builds our schema.
 *
 * @since 4.0.0
 */
class Schema {

	/**
	 * The included graphs.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	public $graphs = [];

	/**
	 * The context data.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	public $context = [];


	/**
	 * All existing WebPage graphs.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $webPageGraphs = [
		'WebPage',
		'AboutPage',
		'CheckoutPage',
		'CollectionPage',
		'ContactPage',
		'FAQPage',
		'ItemPage',
		'MedicalWebPage',
		'ProfilePage',
		'QAPage',
		'RealEstateListing',
		'SearchResultsPage'
	];

	/**
	 * Returns the JSON schema for the requested page.
	 *
	 * @since 4.0.0
	 *
	 * @return string The JSON schema.
	 */
	public function get() {
		// First, let's check if it's disabled.
		if ( apply_filters( 'aioseo_schema_disable', false ) ) {
			return '';
		}

		if ( in_array( 'enableSchemaMarkup', aioseo()->internalOptions->deprecatedOptions, true ) ) {
			if ( ! aioseo()->options->deprecated->searchAppearance->global->schema->enableSchemaMarkup ) {
				return '';
			}
		}

		$this->init();

		if ( ! $this->graphs ) {
			return '';
		}

		$schema = [
			'@context' => 'https://schema.org',
			'@graph'   => []
		];

		// @TODO: [V4+] Use method to filter out empty or null values using recursion.
		foreach ( array_unique( array_filter( $this->graphs ) ) as $graph ) {
			$namespace = "\AIOSEO\Plugin\Common\Schema\Graphs\\$graph";
			$schema['@graph'][] = array_filter( ( new $namespace )->get() );
		}

		// We need to filter again to check for completely empty graphs.
		$schema['@graph'] = array_values( array_filter( $schema['@graph'] ) );

		if ( isset( $_GET['aioseo-dev'] ) ) {
			return wp_json_encode( $schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		}
		return wp_json_encode( $schema );
	}

	/**
	 * Determines the context and graphs for the requested page.
	 *
	 * This can't run in the constructor since the queried object needs to be available first.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function init() {
		$context      = new Context();
		$this->graphs = [
			'WebSite',
			ucfirst( aioseo()->options->searchAppearance->global->schema->siteRepresents ),
			'BreadcrumbList'
		];

		if ( is_front_page() ) {
			$this->graphs[] = 'posts' === get_option( 'show_on_front' ) ? 'CollectionPage' : 'WebPage';
			$this->context  = $context->home();
			return;
		}

		if ( is_home() || aioseo()->helpers->isWooCommerceShopPage() ) {
			$this->graphs[] = 'CollectionPage';
			$this->context  = $context->post();
			return;
		}

		if ( is_singular() ) {
			$this->context = $context->post();

			// Check if we're on a BuddyPress member page.
			if ( function_exists( 'bp_is_user' ) && bp_is_user() ) {
				array_push( $this->graphs, 'ProfilePage', 'PersonAuthor' );
				return;
			}

			$post = aioseo()->helpers->getPost();
			if ( $post && 'page' !== $post->post_type ) {
				$this->graphs[] = 'PersonAuthor';
			}

			$postGraphs = $this->getPostGraphs( $post );
			if ( is_array( $postGraphs ) ) {
				$this->graphs = array_merge( $this->graphs, $postGraphs );
				return;
			}
			$this->graphs[] = $postGraphs;
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$this->graphs[] = 'CollectionPage';
			$this->context  = $context->term();
			return;
		}

		if ( is_author() ) {
			array_push( $this->graphs, 'CollectionPage', 'PersonAuthor' );
			$this->context = $context->author();
			return;
		}

		if ( is_post_type_archive() ) {
			$this->graphs[] = 'CollectionPage';
			$this->context  = $context->postArchive();
			return;
		}

		if ( is_date() ) {
			$this->graphs[] = 'CollectionPage';
			$this->context  = $context->date();
			return;
		}

		if ( is_search() ) {
			$this->graphs[] = 'SearchResultsPage';
			$this->context  = $context->search();
			return;
		}

		if ( is_404() ) {
			$this->context = $context->notFound();
		}
	}

	/**
	 * Returns the graph names that are set for the post.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post      The post object.
	 * @return string|array The graph name(s).
	 */
	public function getPostGraphs( $post = null ) {
		$post    = is_object( $post ) ? $post : aioseo()->helpers->getPost();
		$options = aioseo()->options->noConflict();

		$schemaType        = 'default';
		$schemaTypeOptions = '';

		// Get individual settings.
		$metaData = aioseo()->meta->metaData->getMetaData( $post );
		if ( $metaData && ! empty( $metaData->schema_type ) ) {
			$schemaType        = $metaData->schema_type;
			$schemaTypeOptions = json_decode( $metaData->schema_type_options );
		}

		// Get global settings if set to default.
		if ( 'default' === $schemaType && $options->searchAppearance->dynamic->postTypes->has( $post->post_type ) ) {
			$schemaType = $options->searchAppearance->dynamic->postTypes->{$post->post_type}->schemaType;
		}

		switch ( $schemaType ) {
			case 'WebPage':
				$webPageGraph = ! empty( $metaData->schema_type ) && 'default' !== $metaData->schema_type ? $schemaTypeOptions->webPage->webPageType :
					$options->searchAppearance->dynamic->postTypes->{$post->post_type}->webPageType;
				return ucfirst( $webPageGraph );
			case 'Article':
				$articleGraph = ! empty( $metaData->schema_type ) && 'default' !== $metaData->schema_type ? $schemaTypeOptions->article->articleType :
					$options->searchAppearance->dynamic->postTypes->{$post->post_type}->articleType;
				return [ 'WebPage', ucfirst( $articleGraph ) ];
			case 'none':
				return '';
			default:
				// This fixes a bug from WPForms Form Pages.
				if ( 'default' === $schemaType ) {
					return 'WebPage';
				}
				// Check if the schema type isn't already WebPage or one of its child graphs.
				if ( in_array( $schemaType, $this->webPageGraphs, true ) ) {
					return ucfirst( $schemaType );
				}
				return [ 'WebPage', ucfirst( $schemaType ) ];
		}
	}
}