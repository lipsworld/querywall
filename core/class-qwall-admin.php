<?php
/**
 * Firewall Admin
 *
 * Classe QueryWall.
 *
 * @package QueryWall
 * @since   1.0.1
 */

defined( 'ABSPATH' ) or die( 'You shall not pass!' );

if ( ! class_exists( 'QWall_Admin' ) ):

class QWall_Admin {

	/**
	 * @since 1.0.7
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 30 );
	}

	/**
	 * Hook condicional no WordPress.
	 *
	 * @since 1.0.7
	 * @return void
	 */
	public function init() {

		add_action( 'admin_menu', array( $this, 'cb_admin_menu' ) );
		add_filter( 'plugin_row_meta', array( $this, 'cb_plugin_meta' ), 10, 2 );
	}

	/**
	 * Registra actions para inserção dos comandos e opções.
	 *
	 * Aciona todas as rotinas.
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public function cb_admin_menu() {

		// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		add_menu_page(
			__( 'Requisições bloqueadas', 'querywall' ),
			__( 'MeuPPT Firewall', 'querywall' ),
			'manage_options',
			'querywall',
			'',
			'dashicons-shield'
		);
	}

	
}

QWall_DIC::set( 'admin', new QWall_Admin() );

endif;
