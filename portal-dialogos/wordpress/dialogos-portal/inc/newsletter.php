<?php
/**
 * Inscrição na newsletter: vai sempre para o RD Station (ferramenta principal de e-mail marketing)
 * e, se o Brevo for a ferramenta de disparo, também para o Brevo. Uma cópia fica no WordPress.
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
	$nome   = isset( $_POST['nome'] ) ? sanitize_text_field( wp_unslash( $_POST['nome'] ) ) : '';
	$temas  = isset( $_POST['temas'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['temas'] ) ) : array();
	$origem = isset( $_POST['posicao'] ) ? sanitize_text_field( wp_unslash( $_POST['posicao'] ) ) : '';
	$c      = dp_config();

	// 1) RD Station recebe sempre (ferramenta principal de e-mail marketing).
	$rd = dp_rd_enviar( $email, $nome, $temas, $origem );

	// 2) Brevo, só se for a ferramenta de disparo escolhida e estiver configurado.
	$brevo = 'nao-usado';
	if ( $c['disparo_newsletter'] === 'brevo' && $c['brevo_api_key'] && $c['brevo_lista_id'] && $c['brevo_template_id'] ) {
		$brevo = dp_brevo_enviar( $email, $nome, $temas, $origem ) ? 'ok' : 'erro';
	}

	// 3) Cópia de segurança no WordPress, com a situação de cada envio.
	dp_newsletter_guardar( $email, $nome, $temas, $origem, array( 'rd' => $rd, 'brevo' => $brevo ) );

	if ( $brevo === 'ok' ) {
		wp_send_json_success( array( 'msg' => 'Pronto. Confira seu e-mail para confirmar a inscrição.' ) );
	}
	wp_send_json_success( array( 'msg' => 'Pronto! Sua inscrição foi registrada.' ) );
}

/**
 * Envia a inscrição ao RD Station Marketing como conversão (API com chave pública).
 * Retorna 'ok', 'sem-chave' ou 'erro'.
 */
function dp_rd_enviar( $email, $nome, $temas, $origem ) {
	$c = dp_config();
	if ( ! $c['rd_api_key'] ) {
		return 'sem-chave';
	}
	$tags = array( 'newsletter-portal' );
	foreach ( (array) $temas as $t ) {
		$tags[] = 'newsletter-' . sanitize_title( $t );
	}
	$payload = array_filter( array(
		'conversion_identifier' => $c['rd_identificador'] ?: 'newsletter-portal',
		'email'                 => $email,
		'name'                  => $nome,
		'tags'                  => array_values( array_unique( $tags ) ),
		'traffic_source'        => $origem ? 'portal-' . sanitize_title( $origem ) : 'portal',
		'available_for_mailing' => true,
		'legal_bases'           => array( array( 'category' => 'communications', 'type' => 'consent', 'status' => 'granted' ) ),
	) );
	$resp = wp_remote_post( 'https://api.rd.services/platform/conversions?api_key=' . rawurlencode( $c['rd_api_key'] ), array(
		'timeout' => 12,
		'headers' => array( 'content-type' => 'application/json', 'accept' => 'application/json' ),
		'body'    => wp_json_encode( array( 'event_type' => 'CONVERSION', 'event_family' => 'CDP', 'payload' => $payload ) ),
	) );
	$codigo = is_wp_error( $resp ) ? 0 : wp_remote_retrieve_response_code( $resp );
	if ( $codigo >= 200 && $codigo < 300 ) {
		return 'ok';
	}
	$msg = is_wp_error( $resp ) ? $resp->get_error_message() : $codigo . ' ' . wp_strip_all_tags( substr( (string) wp_remote_retrieve_body( $resp ), 0, 300 ) );
	update_option( 'dp_rd_ultimo_erro', array( 'data' => current_time( 'd/m/Y H:i' ), 'msg' => $msg ), false );
	return 'erro';
}

function dp_brevo_enviar( $email, $nome, $temas, $origem ) {
	$c = dp_config();
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
	$codigo = is_wp_error( $resp ) ? 0 : wp_remote_retrieve_response_code( $resp );
	return $codigo >= 200 && $codigo < 300;
}

function dp_newsletter_guardar( $email, $nome, $temas, $origem, $situacao ) {
	$lista = (array) get_option( 'dp_inscritos', array() );
	$lista[ strtolower( $email ) ] = array( 'email' => $email, 'nome' => $nome, 'temas' => $temas, 'origem' => $origem, 'situacao' => $situacao, 'data' => current_time( 'mysql' ) );
	update_option( 'dp_inscritos', $lista, false );
}

function dp_situacao_texto( $s ) {
	if ( ! is_array( $s ) ) {
		return (string) $s; // inscrições antigas
	}
	$rot = array( 'ok' => 'enviado', 'erro' => 'falhou', 'sem-chave' => 'aguardando chave', 'nao-usado' => '—' );
	return 'RD: ' . ( $rot[ $s['rd'] ?? '' ] ?? '?' ) . ' · Brevo: ' . ( $rot[ $s['brevo'] ?? '' ] ?? '?' );
}

// Reenvia ao RD as inscrições que ainda não chegaram lá (sem chave na época ou com falha).
add_action( 'admin_post_dp_reenviar_rd', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'dp_reenviar_rd' ) ) {
		wp_die( 'Sem permissão.' );
	}
	$lista = (array) get_option( 'dp_inscritos', array() );
	$ok = 0; $falha = 0;
	foreach ( $lista as $k => $i ) {
		$s = is_array( $i['situacao'] ) ? $i['situacao'] : array( 'rd' => 'sem-chave', 'brevo' => 'nao-usado' );
		if ( ( $s['rd'] ?? '' ) === 'ok' ) {
			continue;
		}
		$s['rd'] = dp_rd_enviar( $i['email'], $i['nome'], (array) $i['temas'], $i['origem'] );
		$s['rd'] === 'ok' ? $ok++ : $falha++;
		$lista[ $k ]['situacao'] = $s;
	}
	update_option( 'dp_inscritos', $lista, false );
	wp_safe_redirect( admin_url( 'tools.php?page=dp-inscritos&reenviados=' . $ok . '&falhas=' . $falha ) );
	exit;
} );

// Ferramentas › Inscritos da newsletter (lista e exportação em CSV)
add_action( 'admin_menu', function () {
	add_management_page( 'Inscritos da newsletter', 'Inscritos da newsletter', 'manage_options', 'dp-inscritos', function () {
		$lista = (array) get_option( 'dp_inscritos', array() );
		echo '<div class="wrap"><h1>Inscritos da newsletter</h1><p>Cópia de segurança de todas as inscrições feitas no portal, com a situação do envio ao RD Station e ao Brevo.</p>';
		if ( isset( $_GET['reenviados'] ) ) {
			printf( '<div class="notice notice-info"><p>Enviados ao RD agora: %d. Falhas: %d.</p></div>', absint( $_GET['reenviados'] ), absint( $_GET['falhas'] ?? 0 ) );
		}
		printf( '<p><a class="button" href="%s">Baixar CSV</a> <a class="button button-primary" href="%s">Enviar ao RD as inscrições pendentes</a></p>', esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dp_inscritos_csv' ), 'dp_csv' ) ), esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dp_reenviar_rd' ), 'dp_reenviar_rd' ) ) );
		echo '<table class="widefat striped"><thead><tr><th>E-mail</th><th>Nome</th><th>Assuntos</th><th>Origem</th><th>Situação</th><th>Data</th></tr></thead><tbody>';
		foreach ( array_reverse( $lista ) as $i ) {
			printf( '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', esc_html( $i['email'] ), esc_html( $i['nome'] ), esc_html( implode( ', ', (array) $i['temas'] ) ), esc_html( $i['origem'] ), esc_html( dp_situacao_texto( $i['situacao'] ) ), esc_html( $i['data'] ) );
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
		fputcsv( $out, array( $i['email'], $i['nome'], implode( ', ', (array) $i['temas'] ), $i['origem'], dp_situacao_texto( $i['situacao'] ), $i['data'] ) );
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
