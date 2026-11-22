<?php
/**
 * Plugin Name: Diálogos Estruturantes – Portal
 * Description: Tipos de conteúdo (episódios, edições, eventos, popups), campos do painel, níveis de anúncio, dataLayer, newsletter e popups do portal.
 * Version: 0.1.1
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Diálogos Estruturantes
 * Text Domain: dialogos-portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DP_VERSAO', '0.1.1' );
define( 'DP_DIR', plugin_dir_path( __FILE__ ) );
define( 'DP_URL', plugin_dir_url( __FILE__ ) );

require_once DP_DIR . 'inc/config.php';
require_once DP_DIR . 'inc/conteudos.php';
require_once DP_DIR . 'inc/campos.php';
require_once DP_DIR . 'inc/rastreamento.php';
require_once DP_DIR . 'inc/newsletter.php';
require_once DP_DIR . 'inc/popups.php';
require_once DP_DIR . 'inc/instalacao.php';

register_activation_hook( __FILE__, function () {
	dp_registrar_conteudos();
	dp_rotas();
	flush_rewrite_rules();
} );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Lê um campo do painel (ACF quando ativo; senão, o metadado salvo).
 */
function dp_campo( $nome, $id = null ) {
	$id = $id ?: get_the_ID();
	if ( function_exists( 'get_field' ) ) {
		return get_field( $nome, $id );
	}
	if ( is_string( $id ) && str_starts_with( $id, 'user_' ) ) {
		return get_user_meta( (int) substr( $id, 5 ), $nome, true );
	}
	return get_post_meta( $id, $nome, true );
}
