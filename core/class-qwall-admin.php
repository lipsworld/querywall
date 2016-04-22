<?php
/**
 * QueryWall Admin
 *
 * Admin class for QueryWall.
 *
 * @package QueryWall
 * @since   1.0.1
 */

defined( 'ABSPATH' ) or die( 'You shall not pass!' );

if ( ! class_exists( 'QWall_Admin' ) ):

class QWall_Admin {

	/**
	 * Enqueue actions to build the admin pages.
	 *
	 * Calls all the needed actions to build any given admin page.
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public static function build_admin() {

		global $plugin_file;

		add_menu_page(
			__( 'Firewall Request Monitor', 'querywall' ),
			__( 'QueryWall', 'querywall' ),
			'manage_options',
			'querywall',
			array( __CLASS__, 'render_page' ),
			'dashicons-shield'
		);
	}

	/**
	 * Displays firewall logs table
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public static function render_page() {

		require( dirname( __FILE__ ) . '/class-qwall-monitor.php' );

		if ( $event_purge_next_run = wp_next_scheduled( 'qwall_purge_logs', 24 ) ) {
			$event_purge_older_than = __( '1 day', 'querywall' );
		} else if ( $event_purge_next_run = wp_next_scheduled( 'qwall_purge_logs', 72 ) ) {
			$event_purge_older_than = __( '3 days', 'querywall' );
		} else if ( $event_purge_next_run = wp_next_scheduled( 'qwall_purge_logs', 120 ) ) {
			$event_purge_older_than = __( '5 days', 'querywall' );
		} else if ( $event_purge_next_run = wp_next_scheduled( 'qwall_purge_logs', 168 ) ) {
			$event_purge_older_than = __( '1 week', 'querywall' );
		} else if ( $event_purge_next_run = wp_next_scheduled( 'qwall_purge_logs', 336 ) ) {
			$event_purge_older_than = __( '2 weeks', 'querywall' );
		} else if ( $event_purge_next_run = wp_next_scheduled( 'qwall_purge_logs', 672 ) ) {
			$event_purge_older_than = __( '4 weeks', 'querywall' );
		} else if ( $event_purge_next_run = wp_next_scheduled( 'qwall_purge_logs', 0 ) ) {
			$event_purge_older_than = '"' . __( 'the big bank', 'querywall' ) . '"';
		} else {
			$event_purge_next_run   = false;
			$event_purge_older_than = false;
		}

		$fw_monitor = new QWall_Monitor();
		$fw_monitor->prepare_items();
		?>
			<style type="text/css">
				.wp-list-table .column-date_time { width: 10%; }
				.wp-list-table .column-date_time span { cursor: help; border-bottom: 1px dotted #aaa; }
				.wp-list-table .column-ipv4 { width: 10%; }
				.wp-list-table .column-filter_group { width: 10%; }
				.wp-list-table .column-filter_input { width: 70%; }
				.wp-list-table .column-filter_input strong {
					padding: 0 2px;
					color: #333;
					border-radius: 2px;
					background-color: #ffff8c;
				}
				#poststuff { margin-top: 10px; padding-top: 0; }
				#poststuff + p { margin: 5px 0 -20px; color: #666; }
				#poststuff form > p { margin-bottom: 0; }
				#poststuff form > p > span { cursor: help; border-bottom: 1px dotted #aaa; }
				#poststuff input,
				#poststuff select { vertical-align: baseline; }
			</style>
			<div class="wrap">
				<h2><?php echo get_admin_page_title(); ?></h2>
				<div id="poststuff" class="postbox">
					<h3 class="hndle"><?php _e( 'Options', 'querywall' ); ?></h3>
					<div class="inside">
						<form method="post" action="">
							<?php wp_nonce_field( 'qwall_purge_logs', 'qwall_purge_logs_nonce' ); ?>
							<?php _e( 'Clear logs older than', 'querywall' ); ?>
							<select name="qwall_purge_logs_older_than">
								<option value="24"><?php _e( '1 day', 'querywall' ); ?></option>
								<option value="72"><?php _e( '3 days', 'querywall' ); ?></option>
								<option value="120"><?php _e( '5 days', 'querywall' ); ?></option>
								<option value="168"><?php _e( '1 week', 'querywall' ); ?></option>
								<option value="336"><?php _e( '2 weeks', 'querywall' ); ?></option>
								<option value="672"><?php _e( '4 weeks', 'querywall' ); ?></option>
								<option value="0"><?php _e( 'the big bang', 'querywall' ); ?></option>
							</select> |
							<input class="button-primary" type="submit" name="qwall_purge_logs_now" value="<?php _e( 'Clear now', 'querywall' ); ?>">
							<?php if ( $event_purge_next_run ) { ?>
								<input class="button-primary" type="submit" name="qwall_purge_logs_unschedule" value="<?php _e( 'Unschedule', 'querywall' ); ?>">
							<?php } else { ?>
								<input class="button-primary" type="submit" name="qwall_purge_logs_daily" value="<?php _e( 'Clear daily', 'querywall' ); ?>">
							<?php } ?>
							<?php if ( $event_purge_next_run ) { ?>
								<p><?php printf( __( 'Logs older than %s are scheduled to be purged in <span title="%s">%s</span>.', 'querywall' ), $event_purge_older_than, get_date_from_gmt( date( 'Y-m-d H:i:s', $event_purge_next_run ) ), human_time_diff( $event_purge_next_run, current_time( 'timestamp', 1 ) ) ); ?></p>
							<?php } ?>
						</form>
					</div>
				</div>
				<p><?php _e( 'Blocked requests are shown in the list below.', 'querywall' ); ?></p>
				<?php $fw_monitor->display(); ?>
			</div>
		<?php
	}

	/**
	 * Displays admin notice on success, error, warning, etc.
	 *
	 * @since 1.0.5
	 * @return void
	 */
	public static function render_admin_notice( $message, $css_classes = 'notice-success is-dismissible' ) {
		?>
		<div class="notice <?php echo $css_classes; ?>">
			<p><?php echo $message; ?></p>
		</div>
		<?php
	}

	/**
	 * Purge blocked request logs.
	 *
	 * @since 1.0.5
	 * @return int|boolen
	 */
	public static function purge_logs( $older_than_hours = 0 ) {

		global $wpdb;

		if ( $older_than_hours == 0 ) {
			return $wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "qwall_monitor`;" );
		} else if( in_array( $older_than_hours, array( 24, 72, 120, 168, 336, 672 ) ) ) {
			return $wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "qwall_monitor` WHERE `date_time_gmt` < '" . current_time( 'mysql', 1 ) . "' - INTERVAL " . esc_sql( ( int ) $older_than_hours ) . " HOUR;" );
		}

		return false;
	}

	/**
	 * Add rating link to plugin page.
	 *
	 * @since 1.0.1
	 * @return array
	 */
	public static function rate( $links, $file ) {
		if ( strpos( $file, 'querywall.php' ) !== false ) {
			$wp_url = 'https://wordpress.org/support/view/plugin-reviews/querywall?rate=5#postform';
			$fb_url = 'https://www.facebook.com/querywall';
			$links[] = '<a target="_blank" href="' . $wp_url . '" title="Rate and review QueryWall on WordPress.org">Rate this plugin</a>';
			$links[] = '<a target="_blank" href="' . $fb_url . '" title="Visit QueryWall on Facebook" style="padding:0 5px;color:#fff;vertical-align:middle;border-radius:2px;background:#f5c140;">Visit on Facebook</a>';
		}
		return $links;
	}
}

endif;