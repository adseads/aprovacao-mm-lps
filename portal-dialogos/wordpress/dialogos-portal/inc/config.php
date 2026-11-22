<?php
/**
 * Configurações do portal: Configurações › Portal.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dp_config_padrao() {
	return array(
		'versao_home'      => 'v2',
		'anuncios_modo'    => 'previa',
		'nivel_padrao'     => 'equilibrada',
		'nivel_home'       => 'equilibrada',
		'trava_densidade'  => '1',
		'gtm_id'           => '',
		'nome_newsletter'  => 'A Minuta',
		'brevo_api_key'    => '',
		'brevo_lista_id'   => '',
		'brevo_template_id'=> '',
	);
}

function dp_config( $chave = null ) {
	$c = wp_parse_args( (array) get_option( 'dialogos_portal', array() ), dp_config_padrao() );
	return $chave ? ( $c[ $chave ] ?? null ) : $c;
}

add_action( 'admin_menu', function () {
	add_options_page( 'Portal Diálogos', 'Portal', 'manage_options', 'dialogos-portal', 'dp_tela_config' );
} );

add_action( 'admin_init', function () {
	register_setting( 'dialogos_portal', 'dialogos_portal', array(
		'sanitize_callback' => function ( $v ) {
			$p = dp_config_padrao();
			$out = array();
			foreach ( $p as $k => $padrao ) {
				$out[ $k ] = isset( $v[ $k ] ) ? sanitize_text_field( wp_unslash( $v[ $k ] ) ) : ( $k === 'trava_densidade' ? '0' : $padrao );
			}
			if ( $out['gtm_id'] && ! preg_match( '/^GTM-[A-Z0-9]+$/', $out['gtm_id'] ) ) {
				add_settings_error( 'dialogos_portal', 'gtm', 'O ID do GTM deve ter o formato GTM-XXXXXXX.' );
				$out['gtm_id'] = '';
			}
			return $out;
		},
	) );
} );

function dp_tela_config() {
	$c = dp_config();
	$sel = function ( $nome, $opcoes ) use ( $c ) {
		echo '<select name="dialogos_portal[' . esc_attr( $nome ) . ']">';
		foreach ( $opcoes as $k => $rotulo ) {
			printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $c[ $nome ], $k, false ), esc_html( $rotulo ) );
		}
		echo '</select>';
	};
	$niveis = array( 'leve' => 'Leve', 'equilibrada' => 'Equilibrada', 'intensiva' => 'Intensiva', 'hard' => 'Hard' );
	?>
	<div class="wrap">
		<h1>Portal Diálogos Estruturantes</h1>
		<?php settings_errors( 'dialogos_portal' ); ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'dialogos_portal' ); ?>
			<h2>Página inicial</h2>
			<table class="form-table">
				<tr><th>Versão da home</th><td><?php $sel( 'versao_home', array( 'v1' => 'V1: setores em faixas', 'v2' => 'V2: setores agrupados no topo' ) ); ?></td></tr>
				<tr><th>Nível de anúncios da home</th><td><?php $sel( 'nivel_home', $niveis ); ?></td></tr>
			</table>
			<h2>Anúncios</h2>
			<table class="form-table">
				<tr><th>Modo</th><td><?php $sel( 'anuncios_modo', array( 'desligado' => 'Desligado (nenhum espaço aparece)', 'previa' => 'Prévia (caixas cinza nos espaços)' ) ); ?>
					<p class="description">A ligação com o Google Ad Manager entra na próxima versão do plugin.</p></td></tr>
				<tr><th>Nível padrão das matérias</th><td><?php $sel( 'nivel_padrao', $niveis ); ?><p class="description">Usado quando a matéria não escolhe um nível.</p></td></tr>
				<tr><th>Trava de densidade</th><td><label><input type="checkbox" name="dialogos_portal[trava_densidade]" value="1" <?php checked( $c['trava_densidade'], '1' ); ?>> Retirar anúncios do fim da página quando a densidade passar de 45% no computador ou 28% no celular</label>
					<p class="description">Protege o site do bloqueio de anúncios do Chrome (Better Ads Standards). Recomendado deixar ligada.</p></td></tr>
			</table>
			<h2>Rastreamento</h2>
			<table class="form-table">
				<tr><th>ID do Google Tag Manager</th><td><input type="text" class="regular-text" name="dialogos_portal[gtm_id]" value="<?php echo esc_attr( $c['gtm_id'] ); ?>" placeholder="GTM-XXXXXXX"></td></tr>
			</table>
			<h2>Newsletter</h2>
			<table class="form-table">
				<tr><th>Nome da newsletter</th><td><input type="text" class="regular-text" name="dialogos_portal[nome_newsletter]" value="<?php echo esc_attr( $c['nome_newsletter'] ); ?>"></td></tr>
				<tr><th>Chave de API do Brevo</th><td><input type="password" class="regular-text" name="dialogos_portal[brevo_api_key]" value="<?php echo esc_attr( $c['brevo_api_key'] ); ?>" autocomplete="off">
					<p class="description">Brevo › Configurações › SMTP e API › Chaves de API. Sem a chave, as inscrições ficam guardadas em Ferramentas › Inscritos da newsletter.</p></td></tr>
				<tr><th>ID da lista no Brevo</th><td><input type="number" name="dialogos_portal[brevo_lista_id]" value="<?php echo esc_attr( $c['brevo_lista_id'] ); ?>"></td></tr>
				<tr><th>ID do modelo de confirmação (opt-in duplo)</th><td><input type="number" name="dialogos_portal[brevo_template_id]" value="<?php echo esc_attr( $c['brevo_template_id'] ); ?>"></td></tr>
			</table>
			<?php submit_button( 'Salvar' ); ?>
		</form>
		<hr>
		<h2>Configuração inicial</h2>
		<p>Cria as editorias, as páginas fixas (newsletter, confirmação, quem somos, privacidade e termos) e o menu, e ajusta os links permanentes. Pode ser usado mais de uma vez: o que já existe não é duplicado.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'dp_instalar' ); ?>
			<input type="hidden" name="action" value="dp_instalar">
			<?php submit_button( 'Criar estrutura inicial', 'secondary' ); ?>
		</form>
	</div>
	<?php
}
