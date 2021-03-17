<?php
namespace AIOSEO\Plugin\Common\Models;

/**
 * The Notification DB Model.
 *
 * @since 4.0.0
 */
class Notification extends Model {
	/**
	 * The name of the table in the database, without the prefix.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $table = 'aioseo_notifications';

	/**
	 * An array of fields to set to null if already empty when saving to the database.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $nullFields = [
		'start',
		'end',
		'notification_id',
		'notification_name',
		'button1_label',
		'button1_action',
		'button2_label',
		'button2_action'
	];

	/**
	 * Fields that should be json encoded on save and decoded on get.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $jsonFields = [ 'level' ];

	/**
	 * Fields that should be boolean values.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $booleanFields = [ 'dismissed' ];

	/**
	 * Fields that should be hidden when serialized.
	 *
	 * @var array
	 */
	protected $hidden = [ 'id' ];

	/**
	 * An array of fields attached to this resource.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $columns = [
		'id',
		'slug',
		'title',
		'content',
		'type',
		'level',
		'notification_id',
		'notification_name',
		'start',
		'end',
		'button1_label',
		'button1_action',
		'button2_label',
		'button2_action',
		'dismissed',
		'created',
		'updated'
	];

	/**
	 * Get an array of active notifications.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of active notifications.
	 */
	public static function getAllActiveNotifications() {
		return array_values( json_decode( wp_json_encode( self::getActiveNotifications() ), true ) );
	}

	/**
	 * Retrieve active notifications.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of active notifications or empty.
	 */
	public static function getActiveNotifications() {
		return self::filterNotifications(
			aioseo()->db
				->start( 'aioseo_notifications' )
				->where( 'dismissed', 0 )
				->whereRaw( "(start <= '" . gmdate( 'Y-m-d H:i:s' ) . "' OR start IS NULL)" )
				->whereRaw( "(end >= '" . gmdate( 'Y-m-d H:i:s' ) . "' OR end IS NULL)" )
				->orderBy( 'start DESC' )
				->run()
				->models( 'AIOSEO\\Plugin\\Common\\Models\\Notification' )
		);
	}

	/**
	 * Get an array of dismissed notifications.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of dismissed notifications.
	 */
	public static function getAllDismissedNotifications() {
		return array_values( json_decode( wp_json_encode( self::getDismissedNotifications() ), true ) );
	}

	/**
	 * Retrieve dismissed notifications.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of dismissed notifications or empty.
	 */
	public static function getDismissedNotifications() {
		return self::filterNotifications(
			aioseo()->db
				->start( 'aioseo_notifications' )
				->where( 'dismissed', 1 )
				->orderBy( 'updated DESC' )
				->run()
				->models( 'AIOSEO\\Plugin\\Common\\Models\\Notification' )
		);
	}

	/**
	 * Returns a notification by its name.
	 *
	 * @since 4.0.0
	 *
	 * @param  string       $name The notification name.
	 * @return Notification       The notification.
	 */
	public static function getNotificationByName( $name ) {
		return aioseo()->db
			->start( 'aioseo_notifications' )
			->where( 'notification_name', $name )
			->run()
			->model( 'AIOSEO\\Plugin\\Common\\Models\\Notification' );
	}

	/**
	 * Stores a new notification in the DB.
	 *
	 * @since 4.0.0
	 *
	 * @param  array        $fields       The fields.
	 * @return Notification $notification The notification.
	 */
	public static function addNotification( $fields ) {
		// Set the dismissed status to false.
		$fields['dismissed'] = 0;

		$notification = new self;
		$notification->set( $fields );
		$notification->save();
		return $notification;
	}

	/**
	 * Deletes a notification by its name.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name The notification name.
	 * @return void
	 */
	public static function deleteNotificationByName( $name ) {
		aioseo()->db
			->delete( 'aioseo_notifications' )
			->where( 'notification_name', $name )
			->run();
	}

	/**
	 * Filters the notifications based on the targeted plan levels.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $notifications          The notifications
	 * @return array $remainingNotifications The remaining notifications.
	 */
	public static function filterNotifications( $notifications ) {
		$remainingNotifications = [];
		foreach ( $notifications as $notification ) {
			$levels = $notification->level;
			if ( ! is_array( $levels ) ) {
				$levels = empty( $notification->level ) ? [ 'all' ] : [ $notification->level ];
			}

			foreach ( $levels as $level ) {
				if ( ! aioseo()->notices->validateType( $level ) ) {
					continue 2;
				}
			}

			$remainingNotifications[] = $notification;
		}
		return $remainingNotifications;
	}
}