<?php
namespace AIOSEO\Plugin\Common\ImportExport\YoastSeo;

use AIOSEO\Plugin\Common\ImportExport;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Search Appearance settings.
 *
 * @since 4.0.0
 */
class SearchAppearance {

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->options = get_option( 'wpseo_titles' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateSeparator();
		$this->migrateTitleFormats();
		$this->migrateDescriptionFormats();
		$this->migrateNoindexSettings();
		$this->migratePostTypeSettings();
		$this->migrateRedirectAttachments();
		$this->migrateKnowledgeGraphSettings();
		$this->migrateRssContentSettings();
	}

	/**
	 * Migrates the title/description separator.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateSeparator() {
		$separators = [
			'sc-dash'   => '-',
			'sc-ndash'  => '&ndash;',
			'sc-mdash'  => '&mdash;',
			'sc-colon'  => ':',
			'sc-middot' => '&middot;',
			'sc-bull'   => '&bull;',
			'sc-star'   => '*',
			'sc-smstar' => '&#8902;',
			'sc-pipe'   => '|',
			'sc-tilde'  => '~',
			'sc-laquo'  => '&laquo;',
			'sc-raquo'  => '&raquo;',
			'sc-lt'     => '&lt;',
			'sc-gt'     => '&gt;',
		];

		if ( ! empty( $this->options['separator'] ) && in_array( $this->options['separator'], array_keys( $separators ), true ) ) {
			aioseo()->options->searchAppearance->global->separator = $separators[ $this->options['separator'] ];
		}
	}

	/**
	 * Migrates the title formats.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateTitleFormats() {
		$settings = [
			'title-home-wpseo'    => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'siteTitle' ] ],
			'title-author-wpseo'  => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'author', 'title' ] ],
			'title-archive-wpseo' => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'date', 'title' ] ],
			'title-search-wpseo'  => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'search', 'title' ] ],
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options, true );
	}

	/**
	 * Migrates the description formats.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateDescriptionFormats() {
		$settings = [
			'metadesc-home-wpseo'    => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'metaDescription' ] ],
			'metadesc-author-wpseo'  => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'author', 'metaDescription' ] ],
			'metadesc-archive-wpseo' => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'date', 'metaDescription' ] ],
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options, true );
	}

	/**
	 * Migrates the noindex settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateNoindexSettings() {
		if ( ! empty( $this->options['noindex-author-wpseo'] ) ) {
			aioseo()->options->searchAppearance->archives->author->show = false;
			aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->default = false;
			aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->noindex = true;
		} else {
			aioseo()->options->searchAppearance->archives->author->show = true;
		}

		if ( ! empty( $this->options['noindex-archive-wpseo'] ) ) {
			aioseo()->options->searchAppearance->archives->date->show = false;
			aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->default = false;
			aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->noindex = true;
		} else {
			aioseo()->options->searchAppearance->archives->date->show = true;
		}
	}

	/**
	 * Migrates the post type settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migratePostTypeSettings() {
		$supportedSettings = [
			'title',
			'metadesc',
			'noindex',
			'display-metabox-pt',
			'schema-page-type',
			'schema-article-type'
		];

		foreach ( aioseo()->helpers->getPublicPostTypes( true ) as $postType ) {
			foreach ( $this->options as $name => $value ) {
				if ( ! preg_match( "#(.*)-$postType$#", $name, $match ) || ! in_array( $match[1], $supportedSettings, true ) ) {
					continue;
				}

				switch ( $match[1] ) {
					case 'title':
						if ( 'page' === $postType ) {
							$value = preg_replace( '#%%primary_category%%#', '', $value );
							$value = preg_replace( '#%%excerpt%%#', '', $value );
						}
						aioseo()->options->searchAppearance->dynamic->postTypes->$postType->title =
							aioseo()->helpers->sanitizeOption( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $value ) );
						break;
					case 'metadesc':
						if ( 'page' === $postType ) {
							$value = preg_replace( '#%%primary_category%%#', '', $value );
							$value = preg_replace( '#%%excerpt%%#', '', $value );
						}
						aioseo()->options->searchAppearance->dynamic->postTypes->$postType->metaDescription =
							aioseo()->helpers->sanitizeOption( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $value ) );
						break;
					case 'noindex':
						aioseo()->options->searchAppearance->dynamic->postTypes->$postType->show = empty( $value ) ? true : false;
							aioseo()->options->searchAppearance->dynamic->postTypes->$postType->advanced->robotsMeta->default = empty( $value ) ? true : false;
							aioseo()->options->searchAppearance->dynamic->postTypes->$postType->advanced->robotsMeta->noindex = empty( $value ) ? false : true;
						break;
					case 'display-metabox-pt':
						if ( empty( $value ) ) {
							aioseo()->options->searchAppearance->dynamic->postTypes->$postType->advanced->showMetaBox = false;
						}
						break;
					case 'schema-page-type':
						$value = preg_replace( '#\s#', '', $value );
						if ( in_array( $postType, [ 'post', 'page', 'attachment' ], true ) ) {
							break;
						}
						aioseo()->options->searchAppearance->dynamic->postTypes->$postType->schemaType = 'WebPage';
						if ( in_array( $value, ImportExport\SearchAppearance::$supportedWebPageGraphs, true ) ) {
							aioseo()->options->searchAppearance->dynamic->postTypes->$postType->webPageType = $value;
						}
						break;
					case 'schema-article-type':
						$value = preg_replace( '#\s#', '', $value );
						if ( 'none' === lcfirst( $value ) ) {
							aioseo()->options->searchAppearance->dynamic->postTypes->$postType->articleType = 'none';
							break;
						}

						aioseo()->options->searchAppearance->dynamic->postTypes->$postType->articleType = 'Article';
						if ( in_array( $value, ImportExport\SearchAppearance::$supportedArticleGraphs, true ) ) {
							if ( ! in_array( $postType, [ 'page', 'attachment' ], true ) ) {
								aioseo()->options->searchAppearance->dynamic->postTypes->$postType->articleType = $value;
							}
						} else {
							aioseo()->options->searchAppearance->dynamic->postTypes->$postType->articleType = 'BlogPosting';
						}
						break;
					default:
						break;
				}
			}
		}
	}

	/**
	 * Migrates the Knowledge Graph settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateKnowledgeGraphSettings() {
		if ( ! empty( $this->options['company_or_person'] ) ) {
			aioseo()->options->searchAppearance->global->schema->siteRepresents =
				'company' === $this->options['company_or_person'] ? 'organization' : 'person';
		}

		$settings = [
			'company_or_person_user_id' => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'person' ] ],
			'person_logo'               => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'personLogo' ] ],
			'person_name'               => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'personName' ] ],
			'company_name'              => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'organizationName' ] ],
			'company_logo'              => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'organizationLogo' ] ],
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the RSS content settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateRssContentSettings() {
		if ( isset( $this->options['rssbefore'] ) ) {
			aioseo()->options->rssContent->before = esc_html( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $this->options['rssbefore'] ) );
		}

		if ( isset( $this->options['rssafter'] ) ) {
			aioseo()->options->rssContent->after = esc_html( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $this->options['rssafter'] ) );
		}
	}

	/**
	 * Migrates the Redirect Attachments setting.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateRedirectAttachments() {
		aioseo()->options->searchAppearance->dynamic->postTypes->attachment->redirectAttachmentUrls = empty( $this->options['disable-attachment'] ) ? 'disabled' : 'attachment';
	}
}