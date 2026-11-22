<?php
/**
 * Inscrição na newsletter: envia ao Brevo com opt-in duplo.
 * Sem a chave do Brevo, guarda as inscrições no WordPress para não perder nenhuma.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_dp_newsletter', 'dp_newsletter_inscrever' );
add_action( 'wp_ajax_nopriv_dp_newsletter', 'dp_newsletter_inscrever' );

function dp_newsletter_inscrever() {
	check_ajax_referer( 'dp_newsletter', 'nonce' );
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'msg' => 'Confira o e-mail digitado.' ), 400 );
	}
	if ( ! empty( $_POST['site'] ) ) { // campo invisível contra robôs
		wp_send_json_success( array( 'msg' => 'Pronto.' ) );
	}
	$nome    = isset( $_POST['nome'] ) ? sanitize_text_field( wp_unslash( $_POST['nome'] ) ) : '';
	$temas   = isset( $_POST['temas'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['temas'] ) ) : array();
	$origem  = isset( $_POST['posicao'] ) ? sanitize_text_field( wp_unslash( $_POST['posicao'] ) ) : '';
	$c       = dp_config();

	if ( $c['brevo_api_key'] && $c['brevo_lista_id'] && $c['brevo_template_id'] ) {
		$pagina_ok = dp_url_pagina( 'page-newsletter-ok.php' ) ?: home_url( '/' );
		$resp = wp_remote_post( 'https://api.brevo.com/v3/contacts/doubleOptinConfirmation', array(
			'timeout' => 12,
			'headers' => array( 'api-key' => $c['brevo_api_key'], 'content-type' => 'application/json', 'accept' => 'application/json' ),
			'body'    => wp_json_encode( array(
				'email'          => $email,
				'includeListIds' => array( (int) $c['brevo_lista_id'] ),
				'templateId'     => (int) $c['brevo_template_id'],
				'redirectionUrl' => $pagina_ok,
				'attributes'     => array_filter( array( 'NOME' => $nome, 'TEMAS' => implode( ', ', $temas ), 'ORIGEM' => $origem ) ),
			) ),
		) );
		$codigo = wp_remote_retrieve_response_code( $resp );
		if ( is_wp_error( $resp ) || $codigo >= 300 ) {
			dp_newsletter_guardar( $email, $nome, $temas, $origem, 'erro-brevo' );
			wp_send_json_success( array( 'msg' => 'Recebemos sua inscrição. Em breve você recebe o e-mail de confirmação.' ) );
		}
	} else {
		dp_newsletter_guardar( $email, $nome, $temas, $origem, 'pendente' );
	}
	wp_send_json_success( array( 'msg' => 'Pronto. Confira seu e-mail para confirmar a inscrição.' ) );
}

function dp_newsletter_guardar( $email, $nome, $temas, $origem, $situacao ) {
	$lista = (array) get_option( 'dp_inscritos', array() );
	$lista[ strtolower( $email ) ] = array( 'email' => $email, 'nome' => $nome, 'temas' => $temas, 'origem' => $origem, 'situacao' => $situacao, 'data' => current_time( 'mysql' ) );
	update_option( 'dp_inscritos', $lista, false );
}

// Ferramentas › Inscritos da newsletter (lista e exportação em CSV)
add_action( 'admin_menu', function () {
	add_management_page( 'Inscritos da newsletter', 'Inscritos da newsletter', 'manage_options', 'dp-inscritos', function () {
		$lista = (array) get_option( 'dp_inscritos', array() );
		echo '<div class="wrap"><h1>Inscritos da newsletter</h1><p>Inscrições guardadas no WordPress (sem Brevo configurado ou com falha no envio). Exporte e importe no Brevo.</p>';
		printf( '<p><a class="button" href="%s">Baixar CSV</a></p>', esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dp_inscritos_csv' ), 'dp_csv' ) ) );
		echo '<table class="widefat striped"><thead><tr><th>E-mail</th><th>Nome</th><th>Assuntos</th><th>Origem</th><th>Situação</th><th>Data</th></tr></thead><tbody>';
		foreach ( array_reverse( $lista ) as $i ) {
			printf( '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', esc_html( $i['email'] ), esc_html( $i['nome'] ), esc_html( implode( ', ', (array) $i['temas'] ) ), esc_html( $i['origem'] ), esc_html( $i['situacao'] ), esc_html( $i['data'] ) );
		}
		echo $lista ? '' : '<tr><td colspan="6">Nenhuma inscrição guardada.</td></tr>';
		echo '</tbody></table></div>';
	} );
} );

add_action( 'admin_post_dp_inscritos_csv', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'dp_csv' ) ) {
		wp_die( 'Sem permissão.' );
	}
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=inscritos-newsletter.csv' );
	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'EMAIL', 'NOME', 'TEMAS', 'ORIGEM', 'SITUACAO', 'DATA' ) );
	foreach ( (array) get_option( 'dp_inscritos', array() ) as $i ) {
		fputcsv( $out, array( $i['email'], $i['nome'], implode( ', ', (array) $i['temas'] ), $i['origem'], $i['situacao'], $i['data'] ) );
	}
	exit;
} );

/**
 * URL da primeira página publicada com o modelo indicado.
 */
function dp_url_pagina( $modelo ) {
	$p = get_posts( array( 'post_type' => 'page', 'meta_key' => '_wp_page_template', 'meta_value' => $modelo, 'posts_per_page' => 1, 'post_status' => 'publish' ) );
	return $p ? get_permalink( $p[0] ) : '';
}
