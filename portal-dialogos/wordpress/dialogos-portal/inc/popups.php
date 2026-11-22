<?php
/**
 * Popups: lê os popups ativos e entrega ao script do portal.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function () {
	if ( is_admin() || dp_tipo_pagina() === 'parceiro' ) {
		return;
	}
	$hoje = wp_date( 'Y-m-d' );
	$lista = array();
	foreach ( get_posts( array( 'post_type' => 'popup', 'posts_per_page' => 20, 'post_status' => 'publish' ) ) as $p ) {
		if ( ! dp_campo( 'dp_ativo', $p->ID ) ) {
			continue;
		}
		$fim = dp_campo( 'dp_fim', $p->ID );
		if ( $fim && $fim < $hoje ) {
			continue;
		}
		$paginas = (array) dp_campo( 'dp_paginas', $p->ID );
		$lista[] = array(
			'id'           => 'popup-' . $p->ID,
			'modelo'       => dp_campo( 'dp_pp_modelo', $p->ID ) ?: 'modal',
			'cor'          => dp_campo( 'dp_cor', $p->ID ) ?: 'marinho',
			'prioridade'   => (int) dp_campo( 'dp_prioridade', $p->ID ),
			'chapeu'       => (string) dp_campo( 'dp_chapeu', $p->ID ),
			'titulo'       => get_the_title( $p ),
			'texto'        => (string) dp_campo( 'dp_texto', $p->ID ),
			'imagem'       => (string) dp_campo( 'dp_imagem', $p->ID ),
			'botao_texto'  => (string) dp_campo( 'dp_botao_texto', $p->ID ),
			'botao_link'   => (string) dp_campo( 'dp_botao_link', $p->ID ),
			'nova_aba'     => (bool) dp_campo( 'dp_nova_aba', $p->ID ),
			'posicao'      => dp_campo( 'dp_posicao', $p->ID ) ?: 'topo',
			'gatilho'      => dp_campo( 'dp_gatilho', $p->ID ) ?: 'tempo',
			'valor'        => (int) ( dp_campo( 'dp_valor', $p->ID ) ?: 8 ),
			'repetir_dias' => (int) ( dp_campo( 'dp_repetir_dias', $p->ID ) ?: 7 ),
			'paginas'      => $paginas ?: array( 'todas' ),
			'dispositivo'  => dp_campo( 'dp_dispositivo', $p->ID ) ?: 'todos',
			'inicio'       => (string) dp_campo( 'dp_inicio', $p->ID ),
			'fim'          => (string) $fim,
		);
	}
	if ( ! $lista ) {
		return;
	}
	wp_enqueue_script( 'dp-popups', DP_URL . 'assets/popups.js', array(), DP_VERSAO, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_add_inline_script( 'dp-popups', 'window.POPUPS=' . wp_json_encode( $lista ) . ';', 'before' );
} );
