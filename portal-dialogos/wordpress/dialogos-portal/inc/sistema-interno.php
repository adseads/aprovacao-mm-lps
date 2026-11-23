<?php
/**
 * Autores vindos do sistema interno (Apps Script).
 * O portal busca, a cada hora, o nome, o mini currículo, o cargo e a foto dos articulistas
 * e cria ou atualiza cada um como Colaborador (sem senha, sem acesso ao painel).
 * Tela: Ferramentas › Sistema interno.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DP_SI_TIPOS_IGNORADOS = array( 'podcast', 'entrevista' ); // convidados e entrevistados não viram articulistas

function dp_si_opcoes() {
	return wp_parse_args( (array) get_option( 'dp_si', array() ), array( 'url' => '', 'ultimo' => array() ) );
}

/** Chave secreta compartilhada com o Apps Script. Criada uma vez; pode ser trocada pela tela. */
function dp_si_chave( $nova = false ) {
	$c = get_option( 'dp_si_chave' );
	if ( $nova || ! $c ) {
		$c = wp_generate_password( 48, false, false );
		update_option( 'dp_si_chave', $c, false );
	}
	return $c;
}

function dp_si_pedir( $args ) {
	$o = dp_si_opcoes();
	if ( ! $o['url'] ) {
		return new WP_Error( 'sem_url', 'Falta o endereço da implantação do portal no sistema interno.' );
	}
	$url  = add_query_arg( array_merge( $args, array( 'chave' => dp_si_chave() ) ), $o['url'] );
	$resp = wp_remote_get( $url, array( 'timeout' => 60, 'redirection' => 5 ) );
	if ( is_wp_error( $resp ) ) {
		return $resp;
	}
	$corpo = json_decode( wp_remote_retrieve_body( $resp ), true );
	if ( ! is_array( $corpo ) ) {
		return new WP_Error( 'resposta', 'O sistema interno não respondeu com dados (código ' . wp_remote_retrieve_response_code( $resp ) . '). Confira se a implantação está com acesso "Qualquer pessoa".' );
	}
	if ( empty( $corpo['ok'] ) ) {
		$e = $corpo['erro'] ?? 'desconhecido';
		return new WP_Error( 'si', $e === 'chave' ? 'O sistema interno recusou a chave. Confira a propriedade PORTAL_CHAVE no Apps Script.' : 'Erro no sistema interno: ' . $e );
	}
	return $corpo;
}

function dp_si_normalizar( $s ) {
	return trim( preg_replace( '/[^a-z0-9]+/', ' ', strtolower( remove_accents( (string) $s ) ) ) );
}

/** Procura o usuário do articulista: primeiro pelo vínculo salvo, depois pelo nome exibido. */
function dp_si_achar_usuario( $chave ) {
	$u = get_users( array( 'meta_key' => 'dp_si_chave', 'meta_value' => $chave, 'number' => 1, 'fields' => 'ID' ) );
	if ( $u ) {
		return (int) $u[0];
	}
	foreach ( get_users( array( 'fields' => array( 'ID', 'display_name' ), 'number' => 2000 ) ) as $x ) {
		if ( dp_si_normalizar( $x->display_name ) === $chave ) {
			return (int) $x->ID;
		}
	}
	return 0;
}

function dp_si_criar_usuario( $nome ) {
	$base  = sanitize_user( sanitize_title( $nome ), true ) ?: 'autor';
	$login = $base;
	for ( $i = 2; username_exists( $login ); $i++ ) {
		$login = $base . '-' . $i;
	}
	$partes = preg_split( '/\s+/', trim( $nome ) );
	$id = wp_insert_user( array(
		'user_login'   => $login,
		'user_pass'    => wp_generate_password( 32, true, true ),
		'display_name' => $nome,
		'nickname'     => $nome,
		'first_name'   => array_shift( $partes ),
		'last_name'    => implode( ' ', $partes ),
		'role'         => 'contributor',
	) );
	return is_wp_error( $id ) ? 0 : (int) $id;
}

function dp_si_baixar_foto( $foto_id, $nome ) {
	$r = dp_si_pedir( array( 'portal' => 'foto', 'id' => $foto_id ) );
	if ( is_wp_error( $r ) ) {
		return $r;
	}
	$bytes = base64_decode( (string) ( $r['base64'] ?? '' ), true );
	$tipos = array( 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif' );
	$ext   = $tipos[ $r['mime'] ?? '' ] ?? '';
	if ( ! $bytes || ! $ext ) {
		return new WP_Error( 'foto', 'Foto inválida para ' . $nome . '.' );
	}
	$up = wp_upload_bits( sanitize_title( 'autor-' . $nome ) . '.' . $ext, null, $bytes );
	if ( ! empty( $up['error'] ) ) {
		return new WP_Error( 'foto', $up['error'] );
	}
	$check = wp_check_filetype_and_ext( $up['file'], basename( $up['file'] ) );
	if ( empty( $check['type'] ) || strpos( $check['type'], 'image/' ) !== 0 ) {
		wp_delete_file( $up['file'] );
		return new WP_Error( 'foto', 'O arquivo recebido para ' . $nome . ' não é uma imagem.' );
	}
	$att = wp_insert_attachment( array( 'post_mime_type' => $check['type'], 'post_title' => 'Foto — ' . $nome, 'post_status' => 'inherit' ), $up['file'] );
	if ( is_wp_error( $att ) ) {
		return $att;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $up['file'] ) );
	update_post_meta( $att, '_wp_attachment_image_alt', $nome );
	return (int) $att;
}

/** Sincroniza todos os articulistas. Devolve um resumo para a tela. */
function dp_si_sincronizar() {
	$r = dp_si_pedir( array( 'portal' => 'autores' ) );
	$res = array( 'quando' => current_time( 'd/m/Y H:i' ), 'criados' => 0, 'atualizados' => 0, 'fotos' => 0, 'ignorados' => 0, 'erros' => array() );
	if ( is_wp_error( $r ) ) {
		$res['erros'][] = $r->get_error_message();
	} else {
		foreach ( (array) ( $r['autores'] ?? array() ) as $a ) {
			$nome = sanitize_text_field( $a['nome'] ?? '' );
			if ( ! $nome || in_array( (string) ( $a['tipo'] ?? '' ), DP_SI_TIPOS_IGNORADOS, true ) ) {
				$res['ignorados']++;
				continue;
			}
			$chave = dp_si_normalizar( $nome );
			$uid   = dp_si_achar_usuario( $chave );
			if ( ! $uid ) {
				$uid = dp_si_criar_usuario( $nome );
				if ( ! $uid ) {
					$res['erros'][] = 'Não consegui criar o usuário de ' . $nome . '.';
					continue;
				}
				$res['criados']++;
			} else {
				$res['atualizados']++;
				// Assinante não aparece na lista de autores: promove a Colaborador. Nunca rebaixa ninguém.
				$user = get_userdata( $uid );
				if ( $user && in_array( 'subscriber', (array) $user->roles, true ) && count( $user->roles ) === 1 ) {
					$user->set_role( 'contributor' );
				}
			}
			update_user_meta( $uid, 'dp_si_chave', $chave );
			// Só sobrescreve quando o sistema tem o dado: o que foi preenchido à mão no portal fica.
			$mini = sanitize_textarea_field( $a['minicurriculo'] ?? '' );
			if ( $mini !== '' ) {
				update_user_meta( $uid, 'description', $mini );
			}
			$cargo = sanitize_text_field( $a['cargo'] ?? '' );
			if ( $cargo !== '' ) {
				update_user_meta( $uid, 'dp_cargo', $cargo );
				update_user_meta( $uid, '_dp_cargo', 'field_dp_cargo' );
			}
			$foto = (array) ( $a['foto'] ?? array() );
			if ( ! empty( $foto['id'] ) ) {
				$versao = (string) ( $foto['id'] . ':' . ( $foto['versao'] ?? '' ) );
				if ( get_user_meta( $uid, 'dp_si_foto_versao', true ) !== $versao ) {
					$att = dp_si_baixar_foto( sanitize_text_field( $foto['id'] ), $nome );
					if ( is_wp_error( $att ) ) {
						$res['erros'][] = $att->get_error_message();
					} else {
						update_user_meta( $uid, 'dp_foto', $att );
						update_user_meta( $uid, '_dp_foto', 'field_dp_foto' );
						update_user_meta( $uid, 'dp_si_foto_versao', $versao );
						$res['fotos']++;
					}
				}
			}
		}
	}
	$o = dp_si_opcoes();
	$o['ultimo'] = $res;
	update_option( 'dp_si', $o, false );
	return $res;
}

// Rotina de hora em hora.
add_action( 'init', function () {
	if ( dp_si_opcoes()['url'] && ! wp_next_scheduled( 'dp_si_hora' ) ) {
		wp_schedule_event( time() + 300, 'hourly', 'dp_si_hora' );
	}
} );
add_action( 'dp_si_hora', 'dp_si_sincronizar' );

// Tela: Ferramentas › Sistema interno.
add_action( 'admin_menu', function () {
	add_management_page( 'Sistema interno', 'Sistema interno', 'manage_options', 'dp-sistema-interno', 'dp_si_tela' );
} );

function dp_si_tela() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o = dp_si_opcoes();
	$u = $o['ultimo'];
	$acao = admin_url( 'admin-post.php' );
	echo '<div class="wrap"><h1>Sistema interno: autores</h1>';
	echo '<p>O portal busca no sistema interno o nome, o mini currículo, o cargo e a foto de cada articulista, a cada hora. Cada um vira um usuário <strong>Colaborador</strong>, sem senha, para aparecer como autor nas matérias. Convidados de podcast e entrevistados não entram.</p>';
	if ( isset( $_GET['ok'] ) ) {
		echo '<div class="notice notice-success"><p>Salvo.</p></div>';
	}
	echo '<h2>1. Ligação</h2><form method="post" action="' . esc_url( $acao ) . '">';
	wp_nonce_field( 'dp_si_salvar' );
	echo '<input type="hidden" name="action" value="dp_si_salvar"><table class="form-table">';
	echo '<tr><th>Endereço da implantação do portal</th><td><input type="url" class="large-text" name="url" value="' . esc_attr( $o['url'] ) . '" placeholder="https://script.google.com/macros/s/…/exec"><p class="description">Apps Script › Implantar › Gerenciar implantações › implantação "Portal" › URL do app da Web.</p></td></tr>';
	echo '<tr><th>Chave secreta</th><td><input type="text" class="large-text code" readonly value="' . esc_attr( dp_si_chave() ) . '" onclick="this.select()"><p class="description">Copie e cole no Apps Script em Configurações do projeto › Propriedades do script, com o nome <code>PORTAL_CHAVE</code>. Não envie esta chave por e-mail ou mensagem.</p>';
	echo '<label><input type="checkbox" name="nova_chave" value="1"> Gerar uma chave nova (a antiga para de funcionar; cole a nova no Apps Script)</label></td></tr>';
	echo '</table>';
	submit_button( 'Salvar' );
	echo '</form>';
	echo '<h2>2. Sincronização</h2>';
	if ( $u ) {
		printf( '<p>Última vez: <strong>%s</strong> · %d criado(s), %d atualizado(s), %d foto(s) nova(s), %d ignorado(s).</p>', esc_html( $u['quando'] ?? '' ), (int) ( $u['criados'] ?? 0 ), (int) ( $u['atualizados'] ?? 0 ), (int) ( $u['fotos'] ?? 0 ), (int) ( $u['ignorados'] ?? 0 ) );
		foreach ( (array) ( $u['erros'] ?? array() ) as $e ) {
			echo '<p style="color:#b32d2e">' . esc_html( $e ) . '</p>';
		}
	} else {
		echo '<p>Ainda não sincronizado.</p>';
	}
	echo '<form method="post" action="' . esc_url( $acao ) . '">';
	wp_nonce_field( 'dp_si_agora' );
	echo '<input type="hidden" name="action" value="dp_si_agora">';
	submit_button( 'Sincronizar agora', 'primary', 'submit', false, $o['url'] ? array() : array( 'disabled' => 'disabled' ) );
	echo '</form></div>';
}

add_action( 'admin_post_dp_si_salvar', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'dp_si_salvar' ) ) {
		wp_die( 'Sem permissão.' );
	}
	$o = dp_si_opcoes();
	$url = esc_url_raw( trim( wp_unslash( $_POST['url'] ?? '' ) ) );
	$o['url'] = preg_match( '~^https://script\.google\.com/(macros|a/macros/[a-z0-9.-]+)/s/[A-Za-z0-9_-]+/exec$~', $url ) ? $url : '';
	update_option( 'dp_si', $o, false );
	if ( ! empty( $_POST['nova_chave'] ) ) {
		dp_si_chave( true );
	}
	wp_safe_redirect( admin_url( 'tools.php?page=dp-sistema-interno&ok=1' ) );
	exit;
} );

add_action( 'admin_post_dp_si_agora', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'dp_si_agora' ) ) {
		wp_die( 'Sem permissão.' );
	}
	dp_si_sincronizar();
	wp_safe_redirect( admin_url( 'tools.php?page=dp-sistema-interno' ) );
	exit;
} );
