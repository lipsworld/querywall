<?php
/**
 * QueryWall Core
 *
 * Core class for QueryWall.
 *
 * @package QueryWall
 * @since   1.0.1
 */

defined( 'ABSPATH' ) or die( 'You shall not pass!' );

if ( ! class_exists( 'QWall_Core' ) ):

class QWall_Core {

	/**
	 * Plugin settings.
	 */
	public static $settings;

	/**
	 * Magic starts here.
	 *
	 * @param  string  $plugin_file File path
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public static function init( $plugin_file ) {

		self::$settings = array(
			'plugin_file' => $plugin_file,

		);

		$dirname = dirname( self::$settings['plugin_file'] );

		require_once( $dirname . '/core/class-qwall-dic.php' );
		require_once( $dirname . '/core/class-qwall-settings.php' );
		require_once( $dirname . '/core/class-qwall-firewall.php' );

		if ( is_admin() ) {
			self::admin_init();
		}
	}

	/**
	 * Admin magic starts here.
	 *
	 * @since 1.0.5
	 * @return void
	 */
	public static function admin_init() {

		$dirname = dirname( self::$settings['plugin_file'] );

		require_once( $dirname . '/core/class-qwall-util.php' );
		require_once( $dirname . '/core/class-qwall-setup.php' );
		require_once( $dirname . '/core/class-qwall-notice.php' );
		require_once( $dirname . '/core/class-qwall-admin.php' );

		register_activation_hook( self::$settings['plugin_file'], array( 'QWall_Setup', 'on_activate' ) );
		register_deactivation_hook( self::$settings['plugin_file'], array( 'QWall_Setup', 'on_deactivate' ) );
		register_uninstall_hook( self::$settings['plugin_file'], array( 'QWall_Setup', 'on_uninstall' ) );
		add_action( 'activated_plugin', array( 'QWall_Setup', 'on_activated_plugin' ) );

		if ( isset( $_POST['qwall_purge_logs_now'] ) ) {
			
			require_once( ABSPATH . 'wp-includes/pluggable.php' );
			
			if ( wp_verify_nonce( $_POST['qwall_purge_logs_nonce'], 'qwall_purge_logs' ) ) {
				
				$affected_rows = QWall_DIC::get( 'admin' )->purge_logs( ( int ) $_POST['qwall_purge_logs_older_than'] );
				
				if ( false === $affected_rows ) {

					new QWall_Notice(
						__( 'Oh noes! An error occurred while attempting to purge the logs. You may open a support ticket here <a href="https://wordpress.org/support/plugin/querywall">QueryWall Support Forum</a> or here <a href="https://github.com/4ley/querywall/issues">Github QueryWall Issues</a>.', 'querywall' ),
						array( 'notice-error', 'is-dismissible' )
					);
				} else {

					new QWall_Notice(
						sprintf( _n( 'Success! %s entry purged.', 'Success! %s entries purged.', $affected_rows, 'querywall' ), $affected_rows ),
						array( 'notice-success', 'is-dismissible' )
					);
				}
			}
		}

		if ( isset( $_POST['qwall_purge_logs_daily'] ) || isset( $_POST['qwall_purge_logs_unschedule'] ) ) {
			
			require_once( ABSPATH . 'wp-includes/pluggable.php' );
			
			if ( wp_verify_nonce( $_POST['qwall_purge_logs_nonce'], 'qwall_purge_logs' ) ) {

				QWall_Util::unschedule_event( 'qwall_purge_logs' );

				if( isset( $_POST['qwall_purge_logs_daily'] ) ) {
					wp_schedule_event( current_time( 'timestamp' ), 'daily', 'qwall_purge_logs', ( int ) $_POST['qwall_purge_logs_older_than'] );
					$message = __( 'Success! You have scheduled to purge logs periodically.', 'querywall' );
				} else {
					$message = __( 'Success! You have unscheduled periodical cleaning.', 'querywall' );
				}

				new QWall_Notice( $message, array( 'notice-success', 'is-dismissible' ) );
			}
		}
	}
}

endif;