<?php
namespace AIOSEO\Plugin\Common\Utils;

class Access {
	/**
	 * Capabilities for our users.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $capabilities = [
		'aioseo_general_settings',
		'aioseo_search_appearance_settings',
		'aioseo_social_networks_settings',
		'aioseo_sitemap_settings',
		'aioseo_internal_links_settings',
		'aioseo_redirects_settings',
		'aioseo_seo_analysis_settings',
		'aioseo_tools_settings',
		'aioseo_feature_manager_settings',
		'aioseo_page_analysis',
		'aioseo_page_general_settings',
		'aioseo_page_advanced_settings',
		'aioseo_page_schema_settings',
		'aioseo_page_social_settings',
		'aioseo_local_seo_settings',
		'aioseo_about_us_page'
	];

	/**
	 * Roles we check capabilities against.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $roles = [
		'superadmin'    => 'superadmin',
		'administrator' => 'administrator'
	];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$adminRoles = [];
		$wpRoles    = wp_roles();
		$allRoles   = $wpRoles->roles;
		foreach ( $allRoles as $key => $wpRole ) {
			$role = get_role( $key );
			if ( $role->has_cap( 'install_plugins' ) || $role->has_cap( 'publish_posts' ) ) {
				$adminRoles[ $key ] = $key;
			}
		}

		$this->roles = array_merge( $this->roles, $adminRoles );
	}

	/**
	 * Adds capabilities into WordPress for the current user.
	 * Only on activation or settings saved.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addCapabilities() {
		foreach ( $this->roles as $wpRole => $role ) {
			$roleObject = get_role( $wpRole );
			if ( ! is_object( $roleObject ) ) {
				continue;
			}

			if ( $this->canManage( $role ) ) {
				$roleObject->add_cap( 'aioseo_manage_seo' );
			} else {
				$roleObject->remove_cap( 'aioseo_manage_seo' );
			}

			if ( $this->isAdmin( $role ) ) {
				$roleObject->add_cap( 'aioseo_setup_wizard' );
			} else {
				$roleObject->remove_cap( 'aioseo_setup_wizard' );
			}

			foreach ( $this->getAllCapabilities( $role ) as $capability => $enabled ) {
				if ( $enabled ) {
					$roleObject->add_cap( $capability );
				} else {
					$roleObject->remove_cap( $capability );
				}
			}
		}

		$this->removeCapabilities();
	}

	/**
	 * Removes capabilities for any unknown role.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function removeCapabilities() {
		// Clear out capabilities for unknown roles.
		$wpRoles  = wp_roles();
		$allRoles = $wpRoles->roles;
		foreach ( $allRoles as $key => $wpRole ) {
			$checkRole = is_multisite() ? 'superadmin' : 'administrator';
			if ( $checkRole === $key ) {
				continue;
			}

			if ( in_array( $key, $this->roles, true ) ) {
				continue;
			}

			$role = get_role( $key );

			// Anyone with install plugins can remain.
			if ( $role->has_cap( 'install_plugins' ) ) {
				continue;
			}

			foreach ( $this->capabilities as $capability ) {
				if ( $role->has_cap( $capability ) ) {
					$role->remove_cap( $capability );
				}
			}

			$role->remove_cap( 'aioseo_manage_seo' );
		}
	}

	/**
	 * Checks if the current user has the capability.
	 *
	 * @since 4.0.0
	 *
	 * @param  string      $capability The capability to check against.
	 * @param  string|null $checkRole  A role to check against.
	 * @return bool                    Whether or not the user has this capability.
	 */
	public function hasCapability( $capability, $checkRole = null ) {
		// Only admins have access.
		if ( $this->isAdmin( $checkRole ) ) {
			return true;
		}

		if ( $this->canPublish( $checkRole ) && false !== strpos( $capability, 'aioseo_page_' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Gets all the capabilities for the current user.
	 *
	 * @since 4.0.0
	 *
	 * @param  string|null $role A role to check against.
	 * @return array             An array of capabilities.
	 */
	public function getAllCapabilities( $role = null ) {
		$capabilities = [];
		foreach ( $this->capabilities as $capability ) {
			$capabilities[ $capability ] = $this->hasCapability( $capability, $role );
		}

		$capabilities['aioseo_setup_wizard'] = $this->isAdmin( $role );
		$capabilities['aioseo_admin']        = $this->isAdmin( $role );
		$capabilities['aioseo_manage_seo']   = $this->canManage( $role );

		return $capabilities;
	}

	/**
	 * If the current user is an admin, or superadmin, they have access to all caps regardless.
	 *
	 * @since 4.0.0
	 *
	 * @param  string|null $role The role to check admin privileges if we have one.
	 * @return bool              Whether not the user/role is an admin.
	 */
	public function isAdmin( $role = null ) {
		if ( $role ) {
			if ( is_multisite() && 'superadmin' === $role ) {
				return true;
			}

			if ( ! is_multisite() && 'administrator' === $role ) {
				return true;
			}

			$wpRoles  = wp_roles();
			$allRoles = $wpRoles->roles;
			foreach ( $allRoles as $key => $wpRole ) {
				if ( $key === $role ) {
					$r = get_role( $key );
					if ( $r->has_cap( 'install_plugins' ) ) {
						return true;
					}
				}
			}

			return false;
		}

		if ( ( is_multisite() && current_user_can( 'superadmin' ) ) || current_user_can( 'administrator' ) || current_user_can( 'install_plugins' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the passed in role can publish posts.
	 *
	 * @since 4.0.9
	 *
	 * @param  string  $role The role to check.
	 * @return boolean       True if the role can publish.
	 */
	protected function canPublish( $role ) {
		if ( empty( $role ) ) {
			return current_user_can( 'publish_posts' );
		}

		$wpRoles  = wp_roles();
		$allRoles = $wpRoles->roles;
		foreach ( $allRoles as $key => $wpRole ) {
			if ( $key === $role ) {
				$r = get_role( $key );
				if ( $r->has_cap( 'publish_posts' ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Checks if the passed in role can manage AIOSEO.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $role The role to check against.
	 * @return bool         Whether or not the user can manage AIOSEO.
	 */
	protected function canManage( $role ) {
		return $this->isAdmin( $role );
	}
}