<?php
namespace AIOSEO\Plugin\Lite\Utils;

use AIOSEO\Plugin\Common\Utils as CommonUtils;

/**
 * Class that holds all internal options for AIOSEO.
 *
 * @since 4.0.0
 */
class InternalOptions extends CommonUtils\InternalOptions {
	/**
	 * Defaults options for Lite.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $liteDefaults = [
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		'internal' => [
			'activated'      => [ 'type' => 'number', 'default' => 0 ],
			'firstActivated' => [ 'type' => 'number', 'default' => 0 ],
			'installed'      => [ 'type' => 'number', 'default' => 0 ],
			'connect'        => [
				'key'     => [ 'type' => 'string' ],
				'time'    => [ 'type' => 'number', 'default' => 0 ],
				'network' => [ 'type' => 'boolean', 'default' => false ],
				'token'   => [ 'type' => 'string' ]
			]
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];

	/**
	 * Class constructor
	 *
	 * @since 4.0.0
	 */
	public function __construct( $optionsName = 'aioseo_options_internal' ) {
		parent::__construct( $optionsName );

		$this->init();
	}

	/**
	 * Initializes the options.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function init() {
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

		$this->options = apply_filters( 'aioseo_get_options_internal_lite', $options );
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