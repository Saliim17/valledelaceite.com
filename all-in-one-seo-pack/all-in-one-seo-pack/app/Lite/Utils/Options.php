<?php
namespace AIOSEO\Plugin\Lite\Utils;

use AIOSEO\Plugin\Common\Utils as CommonUtils;

/**
 * Class that holds all options for AIOSEO.
 *
 * @since 4.0.0
 */
class Options extends CommonUtils\Options {
	/**
	 * Defaults options for Lite.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $liteDefaults = [
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		'advanced' => [
			'usageTracking' => [ 'type' => 'boolean', 'default' => false ]
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];

	/**
	 * Class constructor
	 *
	 * @since 4.0.0
	 */
	public function __construct( $optionsName = 'aioseo_options' ) {
		parent::__construct( $optionsName );

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

		parent::init();

		$dbOptions = json_decode( get_option( $this->optionsName . '_lite' ), true );
		if ( empty( $dbOptions ) ) {
			$dbOptions = [];
		}

		// Refactor options.
		$this->defaultsMerged = array_replace_recursive( $this->defaults, $this->liteDefaults );

		$options = array_replace_recursive(
			$this->options,
			$this->addValueToValuesArray( $this->options, $dbOptions )
		);

		$this->options = apply_filters( 'aioseo_get_options_lite', $options );

		if ( $resetKeys ) {
			$this->groupKey  = $originalGroupKey;
			$this->subGroups = $originalSubGroups;
		}
	}

	/**
	 * Updates the options in the database.
	 *
	 * @since 4.0.0
	 *
	 * @param  array|null $options An optional options array.
	 * @return void
	 */
	public function update( $options = null ) {
		$optionsBefore = $this->options;
		parent::update( $options );
		$this->options = $optionsBefore;

		// First, we need to filter our options.
		$options = $this->filterOptions( $this->liteDefaults );

		// Refactor options.
		$refactored = $this->convertOptionsToValues( $options );

		$this->resetGroups();

		update_option( $this->optionsName . '_lite', wp_json_encode( $refactored ) );

		$this->init();
	}
}